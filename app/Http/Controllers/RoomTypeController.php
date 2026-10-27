<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoomTypeController extends Controller
{
    public function index()
    {
        return redirect(route('rooms.index') . '#room-types');
    }

    public function store(Request $request)
    {
        $data = $this->validateType($request);

        RoomType::create($data);

        return redirect(route('rooms.index') . '#room-types')
            ->with('success', 'Room type added successfully!');
    }

    public function update(Request $request, RoomType $roomType)
    {
        $data = $this->validateType($request, $roomType);

        $priceChanged = round((float) $roomType->base_price, 2)
            !== round((float) $data['base_price'], 2);

        DB::transaction(function () use ($roomType, $data, $priceChanged) {
            $roomType->update($data);

            // Synchronize future room prices if base price changed
            if ($priceChanged) {
                $roomType->rooms()->update([
                    'price_per_night' => $data['base_price'],
                ]);
            }
        });

        return redirect(route('rooms.index') . '#room-types')
            ->with('success', 'Room type updated successfully!');
    }

    public function destroy(RoomType $roomType)
    {
        if ($roomType->rooms()->exists()) {
            return redirect(route('rooms.index') . '#room-types')
                ->with('error', 'Cannot delete a room type used by rooms.');
        }

        $roomType->delete();

        return redirect(route('rooms.index') . '#room-types')
            ->with('success', 'Room type deleted successfully!');
    }

    private function validateType(
        Request $request,
        ?RoomType $roomType = null
    ): array {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('room_types', 'name')
                    ->ignore($roomType?->id),
            ],
            'base_price' => 'required|numeric|min:0|max:99999999',
            'description' => 'nullable|string',
        ]);
    }
}
