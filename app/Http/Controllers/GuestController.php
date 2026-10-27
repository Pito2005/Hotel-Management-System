<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    // Old Guests URL redirects to Reservation Management
    public function index()
    {
        return redirect(route('bookings.index') . '#guests');
    }

    // CREATE GUEST
    public function store(Request $request)
    {
        $data = $this->validateGuest($request);

        Guest::create($data);

        return redirect(route('bookings.index') . '#guests')
            ->with('success', 'Guest added successfully!');
    }

    // UPDATE GUEST
    public function update(Request $request, Guest $guest)
    {
        $data = $this->validateGuest($request);

        $guest->update($data);

        return redirect(route('bookings.index') . '#guests')
            ->with('success', 'Guest updated successfully!');
    }

    // DELETE GUEST (ADMIN ONLY VIA ROUTE)
    public function destroy(Guest $guest)
    {
        if ($guest->bookings()->exists()) {
            return redirect(route('bookings.index') . '#guests')
                ->with(
                    'error',
                    'Cannot delete a guest with booking history.'
                );
        }

        $guest->delete();

        return redirect(route('bookings.index') . '#guests')
            ->with('success', 'Guest deleted successfully!');
    }

    // SHARED VALIDATION
    private function validateGuest(Request $request): array
    {
        return $request->validate([
            'full_name'   => 'required|string|max:255',
            'phone'       => 'required|string|max:30',
            'email'       => 'nullable|email|max:255',
            'nationality' => 'nullable|string|max:255',
            'address'     => 'nullable|string|max:1000',
        ]);
    }
}
