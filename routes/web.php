
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\RevenueReportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\FrontDeskController;

/*
|--------------------------------------------------------------------------
| PITO HOTEL - WEB ROUTES
|--------------------------------------------------------------------------
| Laravel 13
| Roles: Admin / Staff
|--------------------------------------------------------------------------
*/

// HOME
Route::get('/', function () {
    return redirect()->route('dashboard');
});

/*
|--------------------------------------------------------------------------
| GUEST ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [
        AuthController::class,
        'showLogin'
    ])->name('login');

    Route::post('/login', [
        AuthController::class,
        'login'
    ])
        ->middleware('throttle:6,1')
        ->name('login.post');

    Route::get('/register', [
        AuthController::class,
        'showRegister'
    ])->name('register');

    Route::post('/register', [
        AuthController::class,
        'register'
    ])->name('register.post');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES - ADMIN & STAFF
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // DASHBOARD
    Route::get('/dashboard', [
        DashboardController::class,
        'index'
    ])->name('dashboard');

    // LOGOUT
    Route::post('/logout', [
        AuthController::class,
        'logout'
    ])->name('logout');

    /*
    |--------------------------------------------------------------------------
    | ROOM MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::get('/rooms', [
        RoomController::class,
        'index'
    ])->name('rooms.index');

    Route::post('/rooms', [
        RoomController::class,
        'store'
    ])->name('rooms.store');

    Route::put('/rooms/{room}', [
        RoomController::class,
        'update'
    ])->name('rooms.update');

    /*
    |--------------------------------------------------------------------------
    | RESERVATION MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::get('/bookings', [
        BookingController::class,
        'index'
    ])->name('bookings.index');

    Route::get('/bookings/available-rooms', [
        BookingController::class,
        'availableRooms'
    ])->name('bookings.available-rooms');

    /*
    |--------------------------------------------------------------------------
    | FRONT DESK - ADMIN & STAFF
    |--------------------------------------------------------------------------
    */

    Route::get('/front-desk', [
        FrontDeskController::class,
        'index'
    ])->name('front-desk.index');

    Route::post('/front-desk/{booking}/check-in', [
        FrontDeskController::class,
        'checkIn'
    ])->name('front-desk.check-in');

    Route::post('/front-desk/{booking}/check-out', [
        FrontDeskController::class,
        'checkOut'
    ])->name('front-desk.check-out');

    /*
    |--------------------------------------------------------------------------
    | GUEST MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::post('/guests', [
        GuestController::class,
        'store'
    ])->name('guests.store');

    Route::put('/guests/{guest}', [
        GuestController::class,
        'update'
    ])->name('guests.update');

    /*
    |--------------------------------------------------------------------------
    | BOOKING MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::post('/bookings', [
        BookingController::class,
        'store'
    ])->name('bookings.store');

    Route::put('/bookings/{booking}', [
        BookingController::class,
        'update'
    ])->name('bookings.update');

    /*
    |--------------------------------------------------------------------------
    | BOOKING RECEIPTS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/bookings/{booking}/receipt/preview',
        [
            BookingController::class,
            'receiptPreview'
        ]
    )->name('bookings.receipt.preview');

    Route::get(
        '/bookings/{booking}/receipt',
        [
            BookingController::class,
            'receipt'
        ]
    )->name('bookings.receipt');

    /*
    |--------------------------------------------------------------------------
    | ADMIN ONLY ROUTES
    |--------------------------------------------------------------------------
    */

    Route::middleware('can:admin')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | DELETE ROOM
        |--------------------------------------------------------------------------
        */

        Route::delete('/rooms/{room}', [
            RoomController::class,
            'destroy'
        ])->name('rooms.destroy');

        /*
        |--------------------------------------------------------------------------
        | ROOM TYPE MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::post('/room-types', [
            RoomTypeController::class,
            'store'
        ])->name('room-types.store');

        Route::put('/room-types/{roomType}', [
            RoomTypeController::class,
            'update'
        ])->name('room-types.update');

        Route::delete('/room-types/{roomType}', [
            RoomTypeController::class,
            'destroy'
        ])->name('room-types.destroy');

        /*
        |--------------------------------------------------------------------------
        | DELETE GUEST
        |--------------------------------------------------------------------------
        */

        Route::delete('/guests/{guest}', [
            GuestController::class,
            'destroy'
        ])->name('guests.destroy');

        /*
        |--------------------------------------------------------------------------
        | DELETE BOOKING
        |--------------------------------------------------------------------------
        */

        Route::delete('/bookings/{booking}', [
            BookingController::class,
            'destroy'
        ])->name('bookings.destroy');

        /*
        |--------------------------------------------------------------------------
        | STAFF MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'staff',
            StaffController::class
        )->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | REPORTS
        |--------------------------------------------------------------------------
        */

        Route::get('/reports', [
            ReportController::class,
            'index'
        ])->name('reports.index');

        Route::get('/reports/revenue/pdf', [
            RevenueReportController::class,
            'exportPdf'
        ])->name('reports.revenue.pdf');

        /*
        |--------------------------------------------------------------------------
        | SETTINGS
        |--------------------------------------------------------------------------
        */

        Route::get('/settings', [
            SettingsController::class,
            'index'
        ])->name('settings.index');

        Route::put('/settings/profile', [
            SettingsController::class,
            'updateProfile'
        ])->name('settings.profile.update');

        Route::put('/settings/hotel', [
            SettingsController::class,
            'updateHotel'
        ])->name('settings.hotel.update');

    });

});
