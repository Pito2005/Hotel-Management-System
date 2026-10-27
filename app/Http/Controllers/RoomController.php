<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ROOM MANAGEMENT + AUTO BOOKING STATUS
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $today = now()->toDateString();

        // Find bookings active today.
        $activeBooking = function ($query) use ($today) {
            $query->where('status', 'confirmed')
                ->whereDate('check_in', '<=', $today)
                ->whereDate('check_out', '>', $today);
        };

        $rooms = Room::with('roomType')

            ->withExists([
                'bookings as has_current_booking' =>
                    $activeBooking,

                'bookings as has_upcoming_booking' =>
                    function ($query) use ($today) {
                        $query->where('status', 'confirmed')
                            ->whereDate('check_in', '>', $today);
                    },
            ])

            // SEARCH
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request->input('search');

                    $query->where(function ($q) use ($search) {
                        $q->where(
                            'room_number',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'description',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'roomType',
                            fn ($t) => $t->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                        );
                    });
                }
            )

            // ROOM TYPE FILTER
            ->when(
                $request->filled('room_type_id'),
                fn ($q) => $q->where(
                    'room_type_id',
                    $request->room_type_id
                )
            )

            // SMART ROOM STATUS FILTER
            ->when(
                $request->filled('status'),
                function ($query) use (
                    $request,
                    $activeBooking
                ) {
                    $status = $request->input('status');

                    if ($status === 'booked') {
                        $query->where('status', 'available')
                            ->whereHas(
                                'bookings',
                                $activeBooking
                            );

                    } elseif ($status === 'available') {
                        $query->where('status', 'available')
                            ->whereDoesntHave(
                                'bookings',
                                $activeBooking
                            );

                    } elseif (
                        in_array(
                            $status,
                            ['occupied', 'maintenance'],
                            true
                        )
                    ) {
                        $query->where('status', $status);
                    }
                }
            )

            ->orderBy('room_number')
            ->paginate(5, ['*'], 'page')
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | AUTOMATIC DISPLAY STATUS
        |--------------------------------------------------------------------------
        | Keep physical rooms.status unchanged.
        */

        $rooms->getCollection()->each(function ($room) {
            if ($room->status === 'maintenance') {
                $displayStatus = 'maintenance';

            } elseif ($room->status === 'occupied') {
                $displayStatus = 'occupied';

            } elseif ((bool) $room->has_current_booking) {
                $displayStatus = 'booked';

            } else {
                $displayStatus = 'available';
            }

            $room->setAttribute(
                'display_status',
                $displayStatus
            );
        });

        /*
        |--------------------------------------------------------------------------
        | ROOM TYPES
        |--------------------------------------------------------------------------
        */

        $roomTypes = RoomType::orderBy('name')->get();

        $roomTypeRows = RoomType::withCount('rooms')
            ->when(
                $request->filled('type_search'),
                fn ($q) => $q->where(
                    'name',
                    'like',
                    '%' . $request->type_search . '%'
                )
            )
            ->orderBy('name')
            ->paginate(5, ['*'], 'types_page')
            ->withQueryString();

        return view('rooms.index', compact(
            'rooms',
            'roomTypes',
            'roomTypeRows'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | OLD URL REDIRECTS
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return redirect()->route('rooms.index');
    }

    public function edit(Room $room)
    {
        return redirect()->route('rooms.index');
    }

    /*
    |--------------------------------------------------------------------------
    | ADD ROOM + ACTIVITY LOG
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $data = $this->validateRoom($request);

        unset($data['image']);

        // Room price comes from selected Room Type.
        $type = RoomType::findOrFail(
            $data['room_type_id']
        );

        $data['price_per_night'] = $type->base_price;

        if ($request->hasFile('image')) {
            $data['image'] = $request
                ->file('image')
                ->store('rooms', 'public');
        }

        // Save room.
        $room = Room::create($data);

        // Record after successful creation.
        $this->recordRoomActivity(
            'room_created',
            $room,
            'Created Room #' . $room->room_number,
            [
                'room_number' => $room->room_number,
                'room_type_id' => $room->room_type_id,
                'floor' => $room->floor,
                'status' => $room->status,
                'price_per_night' =>
                    $room->price_per_night,
            ]
        );

        return redirect()
            ->route('rooms.index')
            ->with(
                'success',
                'Room added successfully!'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE ROOM + ACTIVITY LOG
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, Room $room)
    {
        $data = $this->validateRoom(
            $request,
            $room
        );

        unset($data['image']);

        $type = RoomType::findOrFail(
            $data['room_type_id']
        );

        $data['price_per_night'] = $type->base_price;

        // Save original information for audit.
        $fieldsToTrack = [
            'room_number',
            'room_type_id',
            'floor',
            'status',
            'description',
            'price_per_night',
            'image',
        ];

        $before = $room->only($fieldsToTrack);

        $oldImage = $room->image;

        if ($request->hasFile('image')) {
            $data['image'] = $request
                ->file('image')
                ->store('rooms', 'public');
        }

        // Save updated room.
        $room->update($data);

        // Delete old photo only after update.
        if (isset($data['image']) && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        // Find which fields were changed.
        $changedFields = [];

        foreach ($fieldsToTrack as $field) {
            if (
                $before[$field] !=
                $room->getAttribute($field)
            ) {
                $changedFields[] = $field;
            }
        }

        // Only record an update when data changed.
        if (!empty($changedFields)) {
            $this->recordRoomActivity(
                'room_updated',
                $room,
                'Updated Room #' . $room->room_number,
                [
                    'room_number' =>
                        $room->room_number,

                    'changed_fields' =>
                        $changedFields,

                    'old_room_number' =>
                        $before['room_number'],

                    'new_room_number' =>
                        $room->room_number,

                    'old_status' =>
                        $before['status'],

                    'new_status' =>
                        $room->status,
                ]
            );
        }

        return redirect()
            ->route('rooms.index')
            ->with(
                'success',
                'Room updated successfully!'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE ROOM + ACTIVITY LOG
    |--------------------------------------------------------------------------
    */

    public function destroy(Room $room)
    {
        // Preserve booking and receipt history.
        if ($room->bookings()->exists()) {
            return redirect()
                ->route('rooms.index')
                ->with(
                    'error',
                    'Cannot delete a room with booking history.'
                );
        }

        // Store details before deleting.
        $details = [
            'room_number' => $room->room_number,
            'room_type_id' => $room->room_type_id,
            'floor' => $room->floor,
            'status' => $room->status,
            'price_per_night' =>
                $room->price_per_night,
        ];

        $roomNumber = $room->room_number;
        $image = $room->image;

        // Delete room.
        $room->delete();

        if ($image) {
            Storage::disk('public')->delete($image);
        }

        // Keep the activity log after deletion.
        $this->recordRoomActivity(
            'room_deleted',
            $room,
            'Deleted Room #' . $roomNumber,
            $details
        );

        return redirect()
            ->route('rooms.index')
            ->with(
                'success',
                'Room deleted successfully!'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | ROOM VALIDATION
    |--------------------------------------------------------------------------
    */

    private function validateRoom(
        Request $request,
        ?Room $room = null
    ): array {
        return $request->validate([
            'room_number' => [
                'required',
                'string',
                'max:30',
                Rule::unique(
                    'rooms',
                    'room_number'
                )->ignore($room?->id),
            ],

            'room_type_id' =>
                'required|exists:room_types,id',

            'floor' =>
                'nullable|integer|min:0',

            'status' => [
                'required',
                Rule::in([
                    'available',
                    'occupied',
                    'maintenance',
                ]),
            ],

            'description' =>
                'nullable|string|max:2000',

            'image' =>
                'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PITO HOTEL - ROOM ACTIVITY LOGGER
    |--------------------------------------------------------------------------
    */

    private function recordRoomActivity(
        string $action,
        Room $room,
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
                $room,
                $details
            );

        } catch (\Throwable $exception) {
            // Room operations remain available if
            // optional activity logging fails.
            Log::warning(
                'PITO HOTEL room activity could not be saved.',
                [
                    'room_id' => $room->id,
                    'action' => $action,
                    'error' => $exception->getMessage(),
                ]
            );
        }
    }
}
