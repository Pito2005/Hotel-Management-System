@extends('layouts.app')

@section('title', 'Revenue Reports - PITO HOTEL')
@section('heading', 'Revenue Reports')

@section('content')
<style>
    .pr-page { max-width: 1320px; margin: 0 auto; }
    .pr-eyebrow { color: #198754; text-transform: uppercase; font-size: 11px; font-weight: 800; letter-spacing: 1.2px; }
    .pr-card { background: #fff; border: 1px solid #e0e9e2; border-radius: 16px; box-shadow: 0 5px 22px rgba(20, 83, 45, .045); }
    .pr-top { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 15px; }
    .pr-stat { padding: 20px; height: 100%; }
    .pr-stat-icon { display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; border-radius: 12px; background: #eaf7ef; color: #198754; font-size: 21px; }
    .pr-stat-label { color: #64766c; font-size: 13px; margin: 12px 0 3px; }
    .pr-stat-value { color: #174d34; font-weight: 800; font-size: clamp(22px, 2vw, 30px); overflow-wrap: anywhere; }
    .pr-panel { padding: 22px; }
    .pr-panel h5 { color: #165b38; font-size: 17px; font-weight: 750; }
    .pr-chart { position: relative; height: 290px; width: 100%; }
    .pr-table thead th { background: #edf6ef; color: #456252; border-bottom: 0; font-size: 12px; font-weight: 800; padding: 14px 11px; white-space: nowrap; }
    .pr-table tbody td { padding: 12px 11px; vertical-align: middle; border-color: #edf1ee; font-size: 13px; }
    .pr-tag { border-radius: 50px; display: inline-block; padding: 5px 10px; font-size: 11px; font-weight: 750; }
    .pr-tag-paid { color: #146b43; background: #e5f5eb; }
    .pr-tag-unpaid { color: #986500; background: #fff2d5; }
    .pr-muted { color: #6b8072; font-size: 12px; }
    .pr-note { background: #f2f8f4; border-radius: 10px; padding: 12px 15px; color: #547060; font-size: 12px; }
    .pr-page .form-control:focus, .pr-page .form-select:focus { border-color: #65b38a; box-shadow: 0 0 0 .2rem rgba(25, 135, 84, .12); }
    @media (max-width: 575px) { .pr-panel { padding: 16px; } .pr-chart { height: 250px; } }
</style>

<div class="pr-page">
    <div class="pr-top mb-4">
        <div>
            <div class="pr-eyebrow mb-1">PITO HOTEL / FINANCIAL REPORTING</div>
            <p class="text-muted mb-0">Revenue, payments and booking transactions from your database.</p>
        </div>
        <a class="btn btn-success fw-semibold"
           href="{{ route('reports.revenue.pdf', ['from' => $from, 'to' => $to]) }}">
            <i class="bi bi-file-earmark-arrow-down me-2"></i>Export Revenue PDF
        </a>
    </div>

    {{-- DATE RANGE --}}
    <div class="pr-card pr-panel mb-4">
        <h5 class="mb-3"><i class="bi bi-funnel me-2"></i>Report Period</h5>
        <form method="GET" action="{{ route('reports.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="prFrom" class="form-label fw-semibold small">From Date</label>
                <input id="prFrom" name="from" type="date" class="form-control" value="{{ $from }}" required>
            </div>
            <div class="col-md-4">
                <label for="prTo" class="form-label fw-semibold small">To Date</label>
                <input id="prTo" name="to" type="date" class="form-control" value="{{ $to }}" required>
            </div>
            <div class="col-md-4">
                <button class="btn btn-success w-100 fw-semibold" type="submit">
                    <i class="bi bi-search me-2"></i>Generate Report
                </button>
            </div>
        </form>
        @if ($errors->any())
            <div class="text-danger small mt-3">Please check the date range and filter values.</div>
        @endif
    </div>

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="pr-card pr-stat">
                <span class="pr-stat-icon"><i class="bi bi-cash-stack"></i></span>
                <div class="pr-stat-label">Total Paid Revenue</div>
                <div class="pr-stat-value">${{ number_format($totalRevenue, 2) }}</div>
                <div class="pr-muted">Payments in selected period</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="pr-card pr-stat">
                <span class="pr-stat-icon"><i class="bi bi-check-circle"></i></span>
                <div class="pr-stat-label">Paid Bookings</div>
                <div class="pr-stat-value">{{ number_format($paidBookings) }}</div>
                <div class="pr-muted">Recorded payment dates</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="pr-card pr-stat">
                <span class="pr-stat-icon"><i class="bi bi-hourglass-split"></i></span>
                <div class="pr-stat-label">Unpaid Bookings</div>
                <div class="pr-stat-value">{{ number_format($unpaidBookings) }}</div>
                <div class="pr-muted">Created in selected period</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="pr-card pr-stat">
                <span class="pr-stat-icon"><i class="bi bi-receipt"></i></span>
                <div class="pr-stat-label">Report Records</div>
                <div class="pr-stat-value">{{ number_format($totalRecords) }}</div>
                <div class="pr-muted">Paid + unpaid entries</div>
            </div>
        </div>
    </div>

    {{-- CHARTS --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="pr-card pr-panel h-100">
                <h5 class="mb-1"><i class="bi bi-graph-up-arrow me-2"></i>Revenue Trend</h5>
                <p class="pr-muted mb-3">Paid revenue grouped by payment date.</p>
                <div class="pr-chart"><canvas id="prRevenueChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="pr-card pr-panel h-100">
                <h5 class="mb-1"><i class="bi bi-pie-chart me-2"></i>Payment Methods</h5>
                <p class="pr-muted mb-3">Cash, KHQR, Visa and Mastercard.</p>
                <div class="pr-chart"><canvas id="prMethodChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- TRANSACTION HISTORY --}}
    <div class="pr-card pr-panel mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h5 class="mb-1"><i class="bi bi-list-check me-2"></i>Payment History</h5>
                <div class="pr-muted">Financial transactions from reservations · 5 records per page.</div>
            </div>
            <span class="badge text-bg-light border">{{ $transactions->total() }} Matching</span>
        </div>

        <form action="{{ route('reports.index') }}" method="GET" class="row g-2 align-items-end mb-3">
            <input type="hidden" name="from" value="{{ $from }}">
            <input type="hidden" name="to" value="{{ $to }}">
            <div class="col-lg-5">
                <label class="form-label small fw-semibold">Guest / Phone / Room</label>
                <input type="search" name="search" class="form-control"
                       placeholder="Search guest or room..." value="{{ request('search') }}">
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label small fw-semibold">Payment Status</label>
                <select class="form-select" name="status">
                    <option value="">All</option>
                    <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                    <option value="unpaid" @selected(request('status') === 'unpaid')>Unpaid</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-4">
                <label class="form-label small fw-semibold">Payment Method</label>
                <select class="form-select" name="method">
                    <option value="">All Methods</option>
                    @foreach (['cash' => 'Cash', 'khqr' => 'KHQR', 'visa' => 'Visa', 'mastercard' => 'Mastercard', 'unspecified' => 'Unspecified'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('method') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4 d-flex gap-2">
                <button class="btn btn-success flex-grow-1" type="submit" title="Apply filters"><i class="bi bi-search"></i></button>
                <a class="btn btn-outline-secondary" title="Clear filters"
                   href="{{ route('reports.index', ['from' => $from, 'to' => $to]) }}"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table pr-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Guest / Room</th>
                        <th>Recorded Date</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th class="text-end">Amount</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $booking)
                        <tr>
                            <td class="fw-semibold text-success">#{{ $booking->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $booking->guest?->full_name ?? 'N/A' }}</div>
                                <div class="pr-muted">Room {{ $booking->room?->room_number ?? 'N/A' }}</div>
                            </td>
                            <td class="text-nowrap">
                                {{ $booking->payment_status === 'paid'
                                    ? ($booking->paid_at ? \Carbon\Carbon::parse($booking->paid_at)->format('d M Y') : 'N/A')
                                    : ($booking->created_at?->format('d M Y') ?? 'N/A') }}
                            </td>
                            <td>{{ strtoupper($booking->payment_method ?: 'Unspecified') }}</td>
                            <td>
                                <span class="pr-tag {{ $booking->payment_status === 'paid' ? 'pr-tag-paid' : 'pr-tag-unpaid' }}">
                                    {{ ucfirst($booking->payment_status) }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap fw-bold">${{ number_format((float) $booking->total_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No matching transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
            <small class="text-muted">Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }}</small>
            <div>{{ $transactions->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>

    <div class="pr-note mb-3">
        <i class="bi bi-info-circle me-2"></i>
        Revenue and Paid Bookings use <strong>Payment Date</strong>. Unpaid Bookings use <strong>Booking Creation Date</strong>.
        Only paid amounts count as revenue. Search/status/method filters affect the transaction table, not the summary or charts. Individual booking receipts are available in Reservation Management.
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') return;

        const labels = {{ \Illuminate\Support\Js::from($chartLabels) }};
        const amounts = {{ \Illuminate\Support\Js::from($chartValues) }};
        const methodNames = {{ \Illuminate\Support\Js::from(array_keys($methodTotals)) }};
        const methodAmounts = {{ \Illuminate\Support\Js::from(array_values($methodTotals)) }};

        const revenueCanvas = document.getElementById('prRevenueChart');
        const methodCanvas = document.getElementById('prMethodChart');

        if (revenueCanvas) {
            new Chart(revenueCanvas, {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Paid Revenue ($)', data: amounts, backgroundColor: '#198754', borderRadius: 6, maxBarThickness: 35 }] },
                options: { responsive: true, maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, ticks: { callback: value => '$' + value } } },
                    plugins: { legend: { display: false } } }
            });
        }

        if (methodCanvas) {
            new Chart(methodCanvas, {
                type: 'doughnut',
                data: { labels: methodNames, datasets: [{ data: methodAmounts, backgroundColor: ['#198754', '#0d6efd', '#6f42c1', '#f59e0b', '#9ca3af'], borderWidth: 2 }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%',
                    plugins: { legend: { position: 'bottom' } } }
            });
        }
    });
</script>
@endsection
