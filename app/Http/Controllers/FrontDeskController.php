<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FrontDeskController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | AUTHORIZATION
    |--------------------------------------------------------------------------
    */

    private function authorizeFrontDesk(Request $request): void
    {
        abort_unless(
            in_array(
                $request->user()?->role,
                ['admin', 'staff'],
                true
            ),
            403
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FRONT DESK PAGE
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $this->authorizeFrontDesk($request);

        $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:100'
            ],
        ]);

        $today = now()->toDateString();

        $tabs = [
            'arrivals',
            'in_house',
            'departures',
            'upcoming',
            'completed',
            'all'
        ];

        $tab = in_array(
            $request->query('tab'),
            $tabs,
            true
        )
            ? $request->query('tab')
            : 'arrivals';

        /*
        |--------------------------------------------------------------------------
        | FRONT DESK SUMMARY
        |--------------------------------------------------------------------------
        */

        $counts = [
            'arrivals' => Booking::where('status', 'confirmed')
                ->whereNull('checked_in_at')
                ->whereDate('check_in', '<=', $today)
                ->whereDate('check_out', '>', $today)
                ->count(),

            'in_house' => Booking::where('status', 'confirmed')
                ->whereNotNull('checked_in_at')
                ->whereNull('checked_out_at')
                ->count(),

            'departures' => Booking::where('status', 'confirmed')
                ->whereNotNull('checked_in_at')
                ->whereNull('checked_out_at')
                ->whereDate('check_out', '<=', $today)
                ->count(),

            'upcoming' => Booking::where('status', 'confirmed')
                ->whereNull('checked_in_at')
                ->whereDate('check_in', '>', $today)
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | BOOKING SEARCH AND FILTER
        |--------------------------------------------------------------------------
        */

        $bookings = Booking::query()
            ->with([
                'guest',
                'room.roomType'
            ])

            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $term = '%' .
                        trim($request->input('search')) .
                        '%';

                    $query->where(
                        function ($q) use ($term) {
                            $q->whereHas(
                                'guest',
                                function ($guest) use ($term) {
                                    $guest
                                        ->where(
                                            'full_name',
                                            'like',
                                            $term
                                        )
                                        ->orWhere(
                                            'phone',
                                            'like',
                                            $term
                                        );
                                }
                            )
                            ->orWhereHas(
                                'room',
                                function ($room) use ($term) {
                                    $room->where(
                                        'room_number',
                                        'like',
                                        $term
                                    );
                                }
                            );
                        }
                    );
                }
            )

            ->when(
                $tab === 'arrivals',
                function ($query) use ($today) {
                    $query
                        ->where('status', 'confirmed')
                        ->whereNull('checked_in_at')
                        ->whereDate(
                            'check_in',
                            '<=',
                            $today
                        )
                        ->whereDate(
                            'check_out',
                            '>',
                            $today
                        );
                }
            )

            ->when(
                $tab === 'in_house',
                function ($query) {
                    $query
                        ->where('status', 'confirmed')
                        ->whereNotNull('checked_in_at')
                        ->whereNull('checked_out_at');
                }
            )

            ->when(
                $tab === 'departures',
                function ($query) use ($today) {
                    $query
                        ->where('status', 'confirmed')
                        ->whereNotNull('checked_in_at')
                        ->whereNull('checked_out_at')
                        ->whereDate(
                            'check_out',
                            '<=',
                            $today
                        );
                }
            )

            ->when(
                $tab === 'upcoming',
                function ($query) use ($today) {
                    $query
                        ->where('status', 'confirmed')
                        ->whereNull('checked_in_at')
                        ->whereDate(
                            'check_in',
                            '>',
                            $today
                        );
                }
            )

            ->when(
                $tab === 'completed',
                function ($query) {
                    $query->where(
                        'status',
                        'checked_out'
                    );
                }
            )

            ->orderByDesc('id')
            ->paginate(5)
            ->withQueryString();

        return view(
            'front-desk.index',
            compact(
                'bookings',
                'counts',
                'tab',
                'today'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK IN GUEST
    |--------------------------------------------------------------------------
    */

    public function checkIn(
        Request $request,
        Booking $booking
    ) {
        $this->authorizeFrontDesk($request);

        $today = now()->toDateString();

        DB::transaction(function () use (
            $booking,
            $today
        ) {
            $item = Booking::whereKey($booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            $room = Room::whereKey($item->room_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Only confirmed and not yet checked-in.
            if (
                $item->status !== 'confirmed' ||
                $item->checked_in_at !== null
            ) {
                throw ValidationException::withMessages([
                    'front_desk' =>
                        'This booking is not waiting for check-in.',
                ]);
            }

            // Only during reserved stay dates.
            if (
                $item->check_in->toDateString() > $today ||
                $item->check_out->toDateString() <= $today
            ) {
                throw ValidationException::withMessages([
                    'front_desk' =>
                        'Check-in is allowed during the reserved stay dates only.',
                ]);
            }

            // Prevent use of unavailable rooms.
            if (
                in_array(
                    $room->status,
                    ['maintenance', 'occupied'],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'front_desk' =>
                        'The room is marked unavailable. Check Room Management first.',
                ]);
            }

            // Prevent two in-house guests in one room.
            $alreadyInHouse = Booking::where(
                'room_id',
                $item->room_id
            )
                ->where('id', '!=', $item->id)
                ->where('status', 'confirmed')
                ->whereNotNull('checked_in_at')
                ->whereNull('checked_out_at')
                ->exists();

            if ($alreadyInHouse) {
                throw ValidationException::withMessages([
                    'front_desk' =>
                        'Another guest is already checked into this room.',
                ]);
            }

            // Record actual arrival time.
            $item->checked_in_at = now();

            $item->save();
        });

        $this->recordActivity(
            'booking_checked_in',
            $request,
            $booking
        );

        return redirect()
            ->route(
                'front-desk.index',
                ['tab' => 'in_house']
            )
            ->with(
                'success',
                'Guest checked in successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK OUT GUEST
    |--------------------------------------------------------------------------
    */

    public function checkOut(
        Request $request,
        Booking $booking
    ) {
        $this->authorizeFrontDesk($request);

        DB::transaction(function () use ($booking) {
            $item = Booking::whereKey($booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $item->status !== 'confirmed' ||
                $item->checked_in_at === null ||
                $item->checked_out_at !== null
            ) {
                throw ValidationException::withMessages([
                    'front_desk' =>
                        'This booking has not been checked in, or was already checked out.',
                ]);
            }

            // Record actual departure time.
            $item->checked_out_at = now();

            // Close booking stay.
            $item->status = 'checked_out';

            // Do not change payment status.
            $item->save();
        });

        $this->recordActivity(
            'booking_checked_out',
            $request,
            $booking
        );

        return redirect()
            ->route(
                'front-desk.index',
                ['tab' => 'completed']
            )
            ->with(
                'success',
                'Guest checked out successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG
    |--------------------------------------------------------------------------
    */

    private function recordActivity(
        string $action,
        Request $request,
        Booking $booking
    ): void {
        try {
            ActivityLog::record(
                $action,
                $request->user(),
                'Booking #' . $booking->id . ': ' .
                    str_replace('_', ' ', $action),
                $booking,
                [
                    'guest_id' => $booking->guest_id,
                    'room_id' => $booking->room_id,
                ]
            );

        } catch (\Throwable $exception) {
            Log::warning(
                'Front Desk activity log failed',
                [
                    'booking_id' => $booking->id,
                    'error' => $exception->getMessage(),
                ]
            );
        }
    }
}
