<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | RESERVATION MANAGEMENT
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $bookings = Booking::with([
            'room.roomType',
            'guest',
            'user',
        ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));

                $query->where(function ($q) use ($search) {
                    $q->whereHas('guest', function ($guest) use ($search) {
                        $guest->where('full_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('room', function ($room) use ($search) {
                        $room->where('room_number', 'like', "%{$search}%");
                    });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(5, ['*'], 'page')
            ->withQueryString();

        $guests = Guest::query()
            ->when($request->filled('guest_search'), function ($query) use ($request) {
                $search = trim($request->input('guest_search'));

                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(5, ['*'], 'guests_page')
            ->withQueryString();

        $guestOptions = Guest::orderBy('full_name')->get();

        $roomOptions = Room::with('roomType')
            ->where('status', 'available')
            ->orderBy('room_number')
            ->get();

        $hasPaymentMethod = Schema::hasColumn(
            'bookings',
            'payment_method'
        );

        return view('bookings.index', compact(
            'bookings',
            'guests',
            'guestOptions',
            'roomOptions',
            'hasPaymentMethod'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | OLD CREATE URL REDIRECT
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return redirect()->route('bookings.index', [
            'new_booking' => 1,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE BOOKING + PAYMENT + ACTIVITY LOG
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $hasPaymentMethod = Schema::hasColumn(
            'bookings',
            'payment_method'
        );

        $rules = [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:30',
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1|max:20',
            'notes' => 'nullable|string|max:2000',

            'payment_status' => [
                'required',
                Rule::in(['paid', 'unpaid']),
            ],
        ];

        if ($hasPaymentMethod) {
            $rules['payment_method'] = [
                'nullable',
                Rule::in([
                    'cash',
                    'khqr',
                    'visa',
                    'mastercard',
                    'unspecified',
                ]),
            ];
        }

        $data = $request->validate($rules);

        $customer = [
            'full_name' => trim($data['customer_name']),
            'phone' => trim($data['customer_phone']),
        ];

        unset(
            $data['customer_name'],
            $data['customer_phone']
        );

        $paymentStatus = $data['payment_status'];

        unset($data['payment_status']);

        $paymentMethod = $hasPaymentMethod
            ? ($data['payment_method'] ?? 'unspecified')
            : null;

        unset($data['payment_method']);

        // Paid reservations must have a payment method.
        if (
            $hasPaymentMethod &&
            $paymentStatus === 'paid' &&
            !in_array($paymentMethod, [
                'cash',
                'khqr',
                'visa',
                'mastercard',
            ], true)
        ) {
            throw ValidationException::withMessages([
                'payment_method' =>
                    'Please select a payment method for a paid booking.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | DATABASE TRANSACTION
        |--------------------------------------------------------------------------
        */

        $booking = DB::transaction(function () use (
            $data,
            $customer,
            $request,
            $hasPaymentMethod,
            $paymentMethod,
            $paymentStatus
        ) {
            $room = Room::whereKey($data['room_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($room->status !== 'available') {
                throw ValidationException::withMessages([
                    'room_id' => 'This room is currently unavailable.',
                ]);
            }

            // Prevent overlapping bookings.
            $overlap = Booking::where('room_id', $room->id)
                ->where('status', '!=', 'cancelled')
                ->where('check_in', '<', $data['check_out'])
                ->where('check_out', '>', $data['check_in'])
                ->exists();

            if ($overlap) {
                throw ValidationException::withMessages([
                    'room_id' =>
                        'This room is already booked for the selected dates.',
                ]);
            }

            // Automatically find or create guest.
            $guest = Guest::firstOrCreate([
                'full_name' => $customer['full_name'],
                'phone' => $customer['phone'],
            ]);

            // Calculate number of nights.
            $nights = (int) Carbon::parse($data['check_in'])
                ->diffInDays(Carbon::parse($data['check_out']));

            $price = (float) $room->price_per_night;
            $total = round($price * $nights, 2);

            $booking = new Booking([
                ...$data,
                'guest_id' => $guest->id,
                'user_id' => $request->user()->id,
                'nights' => $nights,
                'price_per_night' => $price,
                'total_amount' => $total,
                'status' => 'confirmed',
            ]);

            // Save actual selected payment status.
            $booking->payment_status = $paymentStatus;

            $booking->paid_at = $paymentStatus === 'paid'
                ? now()
                : null;

            if ($hasPaymentMethod) {
                $booking->payment_method = $paymentMethod;
            }

            $booking->save();

            return $booking;
        }, 3);

        /*
        |--------------------------------------------------------------------------
        | RECORD BOOKING ACTIVITY
        |--------------------------------------------------------------------------
        | IMPORTANT: This must be BEFORE return redirect.
        */

        $this->recordBookingActivity(
            'booking_created',
            $booking,
            'Created Booking #' . $booking->id,
            [
                'room_id' => $booking->room_id,
                'guest_id' => $booking->guest_id,
                'check_in' => (string) $booking->check_in,
                'check_out' => (string) $booking->check_out,
                'total_amount' => $booking->total_amount,
                'payment_status' => $booking->payment_status,
                'payment_method' => $booking->payment_method ?? null,
            ]
        );

        return redirect()
            ->route('bookings.index')
            ->with(
                'success',
                'Booking #' . $booking->id . ' created successfully!'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE BOOKING + PAYMENT ACTIVITY
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, Booking $booking)
    {
        $hasPaymentMethod = Schema::hasColumn(
            'bookings',
            'payment_method'
        );

        $rules = [
            'status' => [
                'required',
                Rule::in([
                    'confirmed',
                    'checked_out',
                    'cancelled',
                ]),
            ],

            'payment_status' => [
                'required',
                Rule::in(['paid', 'unpaid']),
            ],
        ];

        if ($hasPaymentMethod) {
            $rules['payment_method'] = [
                'nullable',
                Rule::in([
                    'cash',
                    'khqr',
                    'visa',
                    'mastercard',
                    'unspecified',
                ]),
            ];
        }

        $data = $request->validate($rules);

        // Remember old values for activity logging.
        $before = [
            'status' => $booking->status,
            'payment_status' => $booking->payment_status,
            'payment_method' => $hasPaymentMethod
                ? $booking->payment_method
                : null,
        ];

        // Do not reactivate cancelled bookings directly.
        if (
            $booking->status === 'cancelled' &&
            $data['status'] !== 'cancelled'
        ) {
            return redirect()
                ->route('bookings.index')
                ->with(
                    'error',
                    'Cancelled bookings cannot be reactivated. Create a new booking.'
                );
        }

        // Do not mark cancelled bookings as paid.
        if (
            $data['status'] === 'cancelled' &&
            $data['payment_status'] === 'paid'
        ) {
            return redirect()
                ->route('bookings.index')
                ->with(
                    'error',
                    'Cancelled bookings cannot be marked as paid.'
                );
        }

        // Paid bookings cannot be cancelled without refund handling.
        if (
            $data['status'] === 'cancelled' &&
            $booking->payment_status === 'paid'
        ) {
            return redirect()
                ->route('bookings.index')
                ->with(
                    'error',
                    'Paid bookings require refund handling before cancellation.'
                );
        }

        $paymentMethod = $hasPaymentMethod
            ? ($data['payment_method'] ?? 'unspecified')
            : null;

        // Update booking fields.
        $booking->status = $data['status'];
        $booking->payment_status = $data['payment_status'];

        $booking->paid_at = $data['payment_status'] === 'paid'
            ? ($booking->paid_at ?? now())
            : null;

        if ($hasPaymentMethod) {
            $booking->payment_method = $paymentMethod;
        }

        $booking->save();
        $booking->refresh();

        /*
        |--------------------------------------------------------------------------
        | BOOKING STATUS LOG
        |--------------------------------------------------------------------------
        */

        if ($before['status'] !== $booking->status) {
            $this->recordBookingActivity(
                'booking_status_updated',
                $booking,
                'Changed Booking #' . $booking->id .
                    ' status to ' . $booking->status,
                [
                    'old_status' => $before['status'],
                    'new_status' => $booking->status,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PAYMENT CHANGE LOG
        |--------------------------------------------------------------------------
        */

        $currentPaymentMethod = $hasPaymentMethod
            ? $booking->payment_method
            : null;

        if (
            $before['payment_status'] !== $booking->payment_status ||
            $before['payment_method'] !== $currentPaymentMethod
        ) {
            $this->recordBookingActivity(
                'payment_updated',
                $booking,
                'Updated payment for Booking #' . $booking->id,
                [
                    'old_payment_status' => $before['payment_status'],
                    'new_payment_status' => $booking->payment_status,
                    'old_payment_method' => $before['payment_method'],
                    'new_payment_method' => $currentPaymentMethod,
                    'total_amount' => $booking->total_amount,
                ]
            );
        }

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Booking updated successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | RECEIPT PREVIEW
    |--------------------------------------------------------------------------
    */

    public function receiptPreview(Booking $booking)
    {
        $booking->load([
            'guest',
            'room.roomType',
            'user',
        ]);

        return view('bookings.receipt', compact('booking'));
    }

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD PDF RECEIPT
    |--------------------------------------------------------------------------
    */

    public function receipt(Booking $booking)
    {
        $booking->load([
            'guest',
            'room.roomType',
            'user',
        ]);

        $pdf = Pdf::loadView(
            'bookings.receipt',
            compact('booking')
        )->setPaper('a4');

        return $pdf->download(
            'Pito-Hotel-Receipt-' . $booking->id . '.pdf'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE BOOKING + ACTIVITY LOG
    |--------------------------------------------------------------------------
    */

    public function destroy(Booking $booking)
    {
        // Protect paid booking history.
        if (
            $booking->payment_status === 'paid' ||
            $booking->paid_at !== null
        ) {
            return redirect()
                ->route('bookings.index')
                ->with(
                    'error',
                    'Paid bookings cannot be deleted. Refund handling is required first.'
                );
        }

        // Save details for the activity log.
        $details = [
            'room_id' => $booking->room_id,
            'guest_id' => $booking->guest_id,
            'payment_status' => $booking->payment_status,
            'total_amount' => $booking->total_amount,
        ];

        $booking->delete();

        // Record deletion after successful delete.
        $this->recordBookingActivity(
            'booking_deleted',
            $booking,
            'Deleted Booking #' . $booking->id,
            $details
        );

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Booking deleted successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | SMART ROOM AVAILABILITY
    |--------------------------------------------------------------------------
    */

    public function availableRooms(Request $request)
    {
        $data = $request->validate([
            'check_in' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'check_out' => [
                'required',
                'date_format:Y-m-d',
                'after:check_in',
            ],
        ]);

        $checkIn = $data['check_in'];
        $checkOut = $data['check_out'];

        $rooms = Room::with('roomType')
            ->where('status', 'available')
            ->whereDoesntHave(
                'bookings',
                function ($query) use ($checkIn, $checkOut) {
                    $query
                        ->where('status', '!=', 'cancelled')
                        ->where('check_in', '<', $checkOut)
                        ->where('check_out', '>', $checkIn);
                }
            )
            ->orderBy('room_number')
            ->get();

        $results = $rooms->map(function ($room) {
            return [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'room_type' => $room->roomType?->name ?? 'Room',
                'price_per_night' => $room->price_per_night,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'count' => $results->count(),
            'rooms' => $results,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG HELPER
    |--------------------------------------------------------------------------
    */

    private function recordBookingActivity(
        string $action,
        Booking $booking,
        string $description,
        array $details = []
    ): void {
        try {
            if (!Schema::hasTable('activity_logs')) {
                Log::warning(
                    'PITO HOTEL: activity_logs table is missing.'
                );

                return;
            }

            ActivityLog::record(
                $action,
                request()->user(),
                $description,
                $booking,
                $details
            );

        } catch (\Throwable $exception) {
            Log::warning(
                'PITO HOTEL booking activity could not be saved.',
                [
                    'booking_id' => $booking->id,
                    'action' => $action,
                    'error' => $exception->getMessage(),
                ]
            );
        }
    }
}
