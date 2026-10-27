<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class StaffAuthenticationSubscriber
{
    // =====================================
    // RECORD STAFF LOGIN
    // =====================================

    public function recordLogin(Login $event): void
    {
        $user = $event->user;

        // Log staff accounts only
        if (!$user instanceof User || $user->role !== 'staff') {
            return;
        }

        ActivityLog::record(
            'staff.login',
            $user,
            'Staff logged in to PITO HOTEL',
            null,
            [
                'role' => $user->role,
            ]
        );
    }

    // =====================================
    // RECORD STAFF LOGOUT
    // =====================================

    public function recordLogout(Logout $event): void
    {
        $user = $event->user;

        // Log staff accounts only
        if (!$user instanceof User || $user->role !== 'staff') {
            return;
        }

        ActivityLog::record(
            'staff.logout',
            $user,
            'Staff logged out of PITO HOTEL',
            null,
            [
                'role' => $user->role,
            ]
        );
    }

    // =====================================
    // REGISTER AUTHENTICATION EVENTS
    // =====================================

    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'recordLogin',
            Logout::class => 'recordLogout',
        ];
    }
}
