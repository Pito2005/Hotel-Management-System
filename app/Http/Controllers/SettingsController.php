<?php

namespace App\Http\Controllers;

use App\Models\HotelSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    // ==========================================
    // SHOW SETTINGS PAGE
    // ==========================================

    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'admin', 403);

        // One shared record for the hotel
        $hotelSettings = HotelSetting::firstOrCreate(
            ['id' => 1],
            ['hotel_name' => 'PITO HOTEL']
        );

        $hotelSettings->load('updatedBy');

        return view('settings.index', compact('hotelSettings'));
    }

    // ==========================================
    // UPDATE CURRENT ADMIN PROFILE
    // ==========================================

    public function updateProfile(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'admin', 403);

        $user = $request->user();

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
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'current_password' => [
                'nullable',
                'required_with:password',
                'current_password',
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $oldPhoto = $user->photo;
        $newPhoto = null;

        try {
            // Upload new profile picture
            if ($request->hasFile('photo')) {
                $newPhoto = $request->file('photo')
                    ->store('profiles', 'public');
            }

            // Update profile information
            $user->name = $data['name'];
            $user->email = $data['email'];

            if ($newPhoto) {
                $user->photo = $newPhoto;
            }

            // Change password only when requested
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

        } catch (\Throwable $e) {
            // Clean up uploaded file if saving fails
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $e;
        }

        // Delete previous profile picture
        if ($newPhoto && $oldPhoto && $oldPhoto !== $newPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return redirect()
            ->route('settings.index')
            ->with('success', 'Admin profile updated successfully!');
    }

    // ==========================================
    // UPDATE HOTEL BRANDING
    // ==========================================

    public function updateHotel(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'admin', 403);

        $data = $request->validate([
            'hotel_name' => [
                'required',
                'string',
                'max:120',
            ],

            // Hotel Logo: max 2 MB
            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            // Login Background: max 5 MB
            'login_background' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            // Remove current Login Background
            'remove_login_background' => [
                'nullable',
                'boolean',
            ],
        ]);

        // All Admins update the same hotel record
        $hotel = HotelSetting::firstOrCreate(
            ['id' => 1],
            ['hotel_name' => 'PITO HOTEL']
        );

        $oldLogo = $hotel->logo;
        $oldBackground = $hotel->login_background;

        $newLogo = null;
        $newBackground = null;

        try {
            // Upload new hotel logo
            if ($request->hasFile('logo')) {
                $newLogo = $request->file('logo')
                    ->store('hotel-logos', 'public');
            }

            // Upload new Login Background
            if ($request->hasFile('login_background')) {
                $newBackground = $request->file('login_background')
                    ->store('login-backgrounds', 'public');
            }

            // Update hotel name
            $hotel->hotel_name = trim($data['hotel_name']);

            // Update hotel logo
            if ($newLogo) {
                $hotel->logo = $newLogo;
            }

            // New image takes priority over removal checkbox
            if ($newBackground) {
                $hotel->login_background = $newBackground;
            } elseif ($request->boolean('remove_login_background')) {
                $hotel->login_background = null;
            }

            // Remember the Admin who updated branding
            $hotel->updated_by = $request->user()->id;

            $hotel->save();

        } catch (\Throwable $e) {
            // Delete newly uploaded files if database save fails
            if ($newLogo) {
                Storage::disk('public')->delete($newLogo);
            }

            if ($newBackground) {
                Storage::disk('public')->delete($newBackground);
            }

            throw $e;
        }

        // Remove old logo after successful update
        if ($newLogo && $oldLogo && $oldLogo !== $newLogo) {
            Storage::disk('public')->delete($oldLogo);
        }

        // Remove old background after replacement or removal
        if (
            $oldBackground &&
            $oldBackground !== $hotel->login_background
        ) {
            Storage::disk('public')->delete($oldBackground);
        }

        return redirect()
            ->route('settings.index')
            ->with('success', 'Hotel branding updated successfully!');
    }
}
