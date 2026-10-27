<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | STAFF MANAGEMENT + ACTIVITY LOGS
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | STAFF LIST + SEARCH + PAGINATION
        |--------------------------------------------------------------------------
        */

        $staffMembers = User::query()
            ->where('role', 'staff')
            ->withCount('bookings')

            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = trim(
                        (string) $request->input('search')
                    );

                    $query->where(function ($q) use ($search) {
                        $q->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )->orWhere(
                            'email',
                            'like',
                            "%{$search}%"
                        );
                    });
                }
            )

            ->latest()
            ->paginate(5, ['*'], 'page')
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | RECENT ACTIVITY LOGS + PAGINATION
        |--------------------------------------------------------------------------
        */

        $recentActivities = Schema::hasTable('activity_logs')
            ? ActivityLog::query()
                ->latest()
                ->paginate(5, ['*'], 'activities_page')
                ->withQueryString()
            : collect();

        /*
        |--------------------------------------------------------------------------
        | STAFF MANAGEMENT PAGE
        |--------------------------------------------------------------------------
        */

        return view('staff.index', compact(
            'staffMembers',
            'recentActivities'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | OLD CREATE URL -> ADD STAFF POPUP
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return redirect()->route('staff.index', [
            'new' => 1,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | OLD EDIT URL -> MANAGE STAFF POPUP
    |--------------------------------------------------------------------------
    */

    public function edit(User $staff)
    {
        abort_unless($staff->role === 'staff', 404);

        return redirect()->route('staff.index', [
            'search' => $staff->email,
            'edit' => $staff->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE STAFF ACCOUNT
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $staff = new User();

        $staff->name = $data['name'];
        $staff->email = $data['email'];

        $staff->password = Hash::make(
            $data['password']
        );

        // Never allow role selection from the form.
        $staff->role = 'staff';

        if ($request->hasFile('photo')) {
            $staff->photo = $request
                ->file('photo')
                ->store('staff', 'public');
        }

        $staff->save();

        return redirect()
            ->route('staff.index')
            ->with(
                'success',
                'Staff account created successfully!'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STAFF + PASSWORD + PHOTO
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, User $staff)
    {
        abort_unless($staff->role === 'staff', 404);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($staff->id),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $staff->name = $data['name'];
        $staff->email = $data['email'];

        // Blank password means keep the old password.
        if (!empty($data['password'])) {
            $staff->password = Hash::make(
                $data['password']
            );
        }

        $oldPhoto = $staff->photo;

        if ($request->hasFile('photo')) {
            $staff->photo = $request
                ->file('photo')
                ->store('staff', 'public');
        }

        $staff->save();

        // Delete old photo only after saving.
        if (
            $request->hasFile('photo') &&
            $oldPhoto
        ) {
            Storage::disk('public')->delete(
                $oldPhoto
            );
        }

        return redirect()
            ->route('staff.index')
            ->with(
                'success',
                'Staff information updated successfully!'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE STAFF - PROTECT BOOKING HISTORY
    |--------------------------------------------------------------------------
    */

    public function destroy(User $staff)
    {
        abort_unless($staff->role === 'staff', 404);

        // Do not delete staff who have booking history.
        if ($staff->bookings()->exists()) {
            return redirect()
                ->route('staff.index')
                ->with(
                    'error',
                    'Cannot delete a staff member with booking history.'
                );
        }

        $photo = $staff->photo;

        $staff->delete();

        if ($photo) {
            Storage::disk('public')->delete(
                $photo
            );
        }

        return redirect()
            ->route('staff.index')
            ->with(
                'success',
                'Staff account deleted successfully!'
            );
    }
}
