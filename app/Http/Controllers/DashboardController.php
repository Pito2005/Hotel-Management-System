<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
;

class DashboardController extends Controller
{
    // ======================================
    // MAIN DASHBOARD ROUTE
    // ======================================

    public function index(Request $request)
    {
        $user = $request->user();

        // Admin / Owner Dashboard
        if ($user->role === 'admin') {
            return $this->adminDashboard();
        }

        // Staff Dashboard
        if ($user->role === 'staff') {
            return $this->staffDashboard($user);
        }

        abort(403, 'Your account role is not authorized.');
    }

    // ======================================
    // ADMIN / OWNER DASHBOARD
    // ======================================

    private function adminDashboard()
    {
        // ======================================
        // HOTEL STATISTICS
        // ======================================

        // Total hotel rooms
        $totalRooms = Room::count();

        // Available rooms
        $availableRooms = Room::where(
            'status',
            'available'
        )->count();

        // Total registered guests
        $totalGuests = Guest::count();

        // Total hotel bookings
        $totalBookings = Booking::count();

        // Total staff accounts
        $totalStaff = User::where(
            'role',
            'staff'
        )->count();

        // ======================================
        // TOTAL PAID REVENUE
        // ======================================

        $totalRevenue = (float) Booking::where(
            'payment_status',
            'paid'
        )->sum('total_amount');

        // ======================================
        // MONTHLY REVENUE GRAPH
        // LAST 6 MONTHS
        // ======================================

        $months = collect(range(5, 0))
            ->map(function ($offset) {
                return now()
                    ->startOfMonth()
                    ->subMonths($offset);
            });

        // Chart month labels
        $monthLabels = $months
            ->map(function ($date) {
                return $date->format('M Y');
            })
            ->values()
            ->all();

        // Revenue grouped by payment date
        $monthRevenue = $months
            ->map(function ($date) {

                return (float) Booking::query()
                    ->where('payment_status', 'paid')
                    ->whereBetween('paid_at', [
                        $date->copy()->startOfMonth(),
                        $date->copy()->endOfMonth(),
                    ])
                    ->sum('total_amount');

            })
            ->values()
            ->all();

        // ======================================
        // PAYMENT METHODS CHART
        // ======================================

        $paymentLabels = [
            'Cash',
            'KHQR',
            'Visa',
            'Mastercard',
            'Unspecified',
        ];

        // Initial payment amounts
        $methodTotals = [
            'cash' => 0,
            'khqr' => 0,
            'visa' => 0,
            'mastercard' => 0,
            'unspecified' => 0,
        ];

        // Check whether payment_method exists
        if (Schema::hasColumn('bookings', 'payment_method')) {

            // Get all paid bookings
            $payments = Booking::query()
                ->where('payment_status', 'paid')
                ->get([
                    'payment_method',
                    'total_amount',
                ]);

            foreach ($payments as $payment) {

                $method = strtolower(
                    $payment->payment_method ?? 'unspecified'
                );

                // Handle unknown payment methods
                if (!array_key_exists($method, $methodTotals)) {
                    $method = 'unspecified';
                }

                // Add amount to corresponding method
                $methodTotals[$method] +=
                    (float) $payment->total_amount;
            }

        } else {

            // Fallback for older database schema
            $methodTotals['unspecified'] = $totalRevenue;

        }

        // Chart payment amounts
        $paymentAmounts = array_values($methodTotals);

        // ======================================
        // RECENT HOTEL BOOKINGS
        // ======================================

        $recentBookings = Booking::with([
            'guest',
            'room',
            'user',
        ])
            ->latest()
            ->take(5)
            ->get();

        // ======================================
        // RECENT STAFF ACTIVITIES
        // ======================================

        // Show latest recorded staff actions:
        // Login, Logout and staff.* events

        $recentActivities = Schema::hasTable('activity_logs')
            ? ActivityLog::query()
                ->where('action', 'like', 'staff.%')
                ->orderByDesc('created_at')
                ->take(10)
                ->get()
            : collect();

        // ======================================
        // RETURN ADMIN / OWNER DASHBOARD
        // IMPORTANT: DO NOT REMOVE THIS!
        // ======================================

        return view('dashboards.admin', compact(
            'totalRooms',
            'availableRooms',
            'totalGuests',
            'totalBookings',
            'totalStaff',
            'totalRevenue',
            'monthLabels',
            'monthRevenue',
            'paymentLabels',
            'paymentAmounts',
            'recentBookings',
            'recentActivities'
        ));
    }

    // ======================================
    // STAFF DASHBOARD
    // ======================================

    private function staffDashboard(User $user)
    {
        // Today's date
        $today = now()->toDateString();

        // ======================================
        // AVAILABLE ROOMS
        // ======================================

        $availableRooms = Room::where(
            'status',
            'available'
        )->count();

        // ======================================
        // TODAY'S CHECK-INS
        // ======================================

        $todayCheckIns = Booking::with([
            'guest',
            'room',
        ])
            ->whereDate('check_in', $today)
            ->where('status', 'confirmed')
            ->orderBy('check_in')
            ->get();

        // ======================================
        // TODAY'S CHECK-OUTS
        // ======================================

        $todayCheckOuts = Booking::with([
            'guest',
            'room',
        ])
            ->whereDate('check_out', $today)
            ->where('status', 'confirmed')
            ->orderBy('check_out')
            ->get();

        // ======================================
        // BOOKINGS CREATED BY THIS STAFF
        // ======================================

        $myBookingsCount = Booking::where(
            'user_id',
            $user->id
        )->count();

        // ======================================
        // RECENT BOOKINGS CREATED BY THIS STAFF
        // ======================================

        $myRecentBookings = Booking::with([
            'guest',
            'room',
        ])
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // ======================================
        // RETURN STAFF DASHBOARD
        // ======================================

        return view('dashboards.staff', compact(
            'availableRooms',
            'todayCheckIns',
            'todayCheckOuts',
            'myBookingsCount',
            'myRecentBookings'
        ));
    }
}
