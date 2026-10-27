<?php

namespace App\Providers;

use App\Models\HotelSetting;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /*
    |--------------------------------------------------------------------------
    | Register Application Services
    |--------------------------------------------------------------------------
    */

    public function register(): void
    {
        //
    }

    /*
    |--------------------------------------------------------------------------
    | Bootstrap Application Services
    |--------------------------------------------------------------------------
    */

    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ADMIN PERMISSIONS
        |--------------------------------------------------------------------------
        |
        | Admin can manage:
        | - Staff
        | - Room Types
        | - Room Deletion
        | - Reports
        | - Settings
        |
        | Staff can add and edit Rooms through
        | authenticated routes.
        |--------------------------------------------------------------------------
        */

        Gate::define('admin', function (User $user): bool {
            return $user->role === 'admin';
        });

        /*
        |--------------------------------------------------------------------------
        | LOGIN / LOGOUT ACTIVITY LOGS
        |--------------------------------------------------------------------------
        |
        | AuthController records login and logout.
        |
        | Do not subscribe StaffAuthenticationSubscriber
        | here because it would record duplicate events.
        |
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | SHARED HOTEL BRANDING
        |--------------------------------------------------------------------------
        |
        | Share Hotel Name and Logo with:
        |
        | - Main Layout
        | - Authentication Pages
        | - Booking Receipts
        | - Revenue PDF Reports
        |
        |--------------------------------------------------------------------------
        */

        View::composer(
            [
                'layouts.app',
                'layouts.auth',
                'auth.login',
                'auth.register',
                'bookings.receipt',
                'reports.revenue-pdf',
            ],
            function ($view) {

                $hotelSettings = HotelSetting::find(1);

                $view->with(
                    'hotelSettings',
                    $hotelSettings
                );

            }
        );
    }
}
