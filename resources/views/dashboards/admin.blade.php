@extends('layouts.app')

@section('title', 'Admin Dashboard - PITO HOTEL')
@section('heading', 'Admin Dashboard')

@section('content')

    <style>
        .owner-stat {
            border: none;
            border-radius: 16px;
            background: #fff;
            padding: 22px;
            height: 100%;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .owner-icon {
            width: 48px;
            height: 48px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e0f3e7;
            color: #198754;
            font-size: 23px;
        }

        .owner-number {
            font-size: 29px;
            font-weight: 700;
            color: #125c38;
        }

        .owner-chart {
            position: relative;
            height: 300px;
        }

        .owner-chart canvas {
            width: 100% !important;
            height: 100% !important;
        }
    </style>

    {{-- ======================================
     OWNER WELCOME
====================================== --}}

    <div class="card p-4 mb-4">

        <div class="d-flex justify-content-between
                align-items-center flex-wrap gap-3">

            <div>
                <h3 class="text-success fw-bold">
                    <i class="bi bi-shield-check me-2"></i>
                    Welcome, {{ auth()->user()->name }}!
                </h3>

                <p class="text-muted mb-0">
                    Overview of PITO HOTEL performance
                    and daily operations.
                </p>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if (\Illuminate\Support\Facades\Route::has('reports.index'))
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-success">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i>Reports
                    </a>
                @endif
                <span class="badge bg-success px-3 py-2">
                    <i class="bi bi-person-badge me-1"></i>Admin / Owner
                </span>
            </div>

        </div>
    </div>

    {{-- ======================================
     KPI SUMMARY CARDS
====================================== --}}

    <div class="row g-4 mb-4">

        {{-- TOTAL REVENUE --}}
        <div class="col-lg-4 col-md-6">
            <div class="owner-stat">

                <div class="owner-icon mb-3">
                    <i class="bi bi-cash-stack"></i>
                </div>

                <div class="text-muted">Total Paid Revenue</div>

                <div class="owner-number">
                    ${{ number_format($totalRevenue, 2) }}
                </div>

                <small class="text-muted">
                    All recorded paid bookings
                </small>
            </div>
        </div>

        {{-- TOTAL ROOMS --}}
        <div class="col-lg-4 col-md-6">
            <div class="owner-stat">

                <div class="owner-icon mb-3">
                    <i class="bi bi-door-open"></i>
                </div>

                <div class="text-muted">Total Rooms</div>
                <div class="owner-number">{{ $totalRooms }}</div>

                <small class="text-success">
                    {{ $availableRooms }} marked available
                </small>
            </div>
        </div>

        {{-- BOOKINGS --}}
        <div class="col-lg-4 col-md-6">
            <div class="owner-stat">

                <div class="owner-icon mb-3">
                    <i class="bi bi-calendar-check"></i>
                </div>

                <div class="text-muted">Total Bookings</div>
                <div class="owner-number">{{ $totalBookings }}</div>

                <small class="text-muted">
                    All reservations
                </small>
            </div>
        </div>

        {{-- GUESTS --}}
        <div class="col-lg-6 col-md-6">
            <div class="owner-stat">

                <div class="owner-icon mb-3">
                    <i class="bi bi-people"></i>
                </div>

                <div class="text-muted">Total Guests</div>
                <div class="owner-number">{{ $totalGuests }}</div>
            </div>
        </div>

        {{-- STAFF --}}
        <div class="col-lg-6 col-md-6">
            <div class="owner-stat">

                <div class="owner-icon mb-3">
                    <i class="bi bi-person-badge"></i>
                </div>

                <div class="text-muted">Total Staff</div>
                <div class="owner-number">{{ $totalStaff }}</div>
            </div>
        </div>
    </div>

    {{-- ======================================
     REVENUE CHART AND PAYMENT CHART
====================================== --}}

    <div class="row g-4 mb-4">

        {{-- MONTHLY REVENUE GRAPH --}}
        <div class="col-lg-8">

            <div class="card p-4 h-100">

                <h4 class="text-success fw-bold">
                    <i class="bi bi-graph-up-arrow me-2"></i>
                    Monthly Revenue
                </h4>

                <p class="text-muted small">
                    Paid booking amounts for the last 6 months.
                </p>

                <div class="owner-chart">
                    <canvas id="revenueChart"></canvas>
                </div>

            </div>
        </div>

        {{-- PAYMENT METHODS --}}
        <div class="col-lg-4">

            <div class="card p-4 h-100">

                <h4 class="text-success fw-bold">
                    <i class="bi bi-wallet2 me-2"></i>
                    Payment Methods
                </h4>

                <p class="text-muted small">
                    Breakdown of recorded paid amounts.
                </p>

                <div class="owner-chart">
                    <canvas id="paymentChart"></canvas>
                </div>

            </div>
        </div>
    </div>

    {{-- Revenue export has its own Reports menu. No duplicate form here. --}}

    {{-- ======================================
     RECENT BOOKINGS
====================================== --}}

    <div class="card p-4 mb-4">

        <h4 class="text-success fw-bold mb-3">
            <i class="bi bi-clock-history me-2"></i>
            Recent Bookings
        </h4>

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Created By</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($recentBookings as $booking)
                        <tr>

                            <td>#{{ $booking->id }}</td>

                            <td>
                                {{ $booking->guest?->full_name ?? '-' }}
                            </td>

                            <td>
                                {{ $booking->room?->room_number ?? '-' }}
                            </td>

                            <td>
                                {{ $booking->user?->name ?? '-' }}
                            </td>

                            <td>
                                <span class="badge bg-success">
                                    {{ ucwords(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </td>

                            <td class="fw-bold text-success">
                                ${{ number_format($booking->total_amount, 2) }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                No bookings found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>


    {{-- Activity Logs are shown in Staff Management. --}}

    {{-- ======================================
     CHART.JS
====================================== --}}

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            if (typeof Chart === 'undefined') {
                console.error('Chart.js could not be loaded.');
                return;
            }

            // Real data returned from Laravel Controller
            const monthLabels = {{ \Illuminate\Support\Js::from($monthLabels) }};
            const monthRevenue = {{ \Illuminate\Support\Js::from($monthRevenue) }};

            const paymentLabels = {{ \Illuminate\Support\Js::from($paymentLabels) }};
            const paymentAmounts = {{ \Illuminate\Support\Js::from($paymentAmounts) }};

            // MONTHLY REVENUE BAR CHART
            new Chart(document.getElementById('revenueChart'), {
                type: 'bar',

                data: {
                    labels: monthLabels,

                    datasets: [{
                        label: 'Paid Revenue ($)',
                        data: monthRevenue,
                        backgroundColor: '#198754',
                        borderRadius: 8,
                        maxBarThickness: 60
                    }]
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    plugins: {
                        legend: {
                            display: false
                        }
                    },

                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => '$' + value
                            }
                        }
                    }
                }
            });

            // PAYMENT METHOD DOUGHNUT CHART
            new Chart(document.getElementById('paymentChart'), {
                type: 'doughnut',

                data: {
                    labels: paymentLabels,

                    datasets: [{
                        data: paymentAmounts,
                        backgroundColor: [
                            '#198754',
                            '#0d6efd',
                            '#6f42c1',
                            '#fd7e14',
                            '#adb5bd'
                        ],
                        borderWidth: 2
                    }]
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',

                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

        });
    </script>

@endsection
