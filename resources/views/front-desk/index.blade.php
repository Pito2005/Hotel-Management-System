
@extends('layouts.app')

@section('title', 'Front Desk - PITO HOTEL')
@section('heading', 'Front Desk')

@section('content')

<style>
    .fd-card {
        background: #fff;
        border: 1px solid #e1ece5;
        border-radius: 16px;
        padding: 22px;
        box-shadow: 0 5px 24px rgba(20, 80, 45, .045);
    }

    .fd-stat {
        background: #fff;
        border: 1px solid #e1ece5;
        border-radius: 14px;
        padding: 18px;
        height: 100%;
    }

    .fd-number {
        font-size: 28px;
        color: #12623b;
        font-weight: 800;
    }

    .fd-label {
        color: #637568;
        font-size: 13px;
    }

    .fd-tab {
        text-decoration: none;
        color: #34694d;
        padding: 10px 14px;
        border-radius: 10px;
        display: inline-block;
        font-weight: 600;
    }

    .fd-tab.active {
        background: #198754;
        color: white;
    }

    .fd-tab:hover:not(.active) {
        background: #e5f5eb;
        color: #14683c;
    }

    .fd-table th {
        background: #eaf5ee;
        color: #245b3c;
        white-space: nowrap;
        font-size: 12px;
        padding: 12px;
    }

    .fd-table td {
        vertical-align: middle;
        padding: 12px;
        font-size: 13px;
    }

    .fd-note {
        font-size: 12px;
        color: #697d70;
    }

    @media (max-width: 575px) {
        .fd-card {
            padding: 15px;
        }
    }
</style>

<div class="container-fluid px-0">

    {{-- PAGE HEADER --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">

        <div>
            <div class="text-success fw-bold small">
                PITO HOTEL OPERATIONS
            </div>

            <p class="text-muted mb-0">
                Check guest arrivals, stays and departures.
                Today: {{ now()->format('d M Y') }}
            </p>
        </div>

        <a href="{{ route('bookings.index') }}"
           class="btn btn-outline-success">
            <i class="bi bi-calendar-plus me-1"></i>
            Reservation Management
        </a>
    </div>

    {{-- SUCCESS / ERROR --}}
    @if (session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-1"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    {{-- SUMMARY CARDS --}}
    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-sm-6">
            <div class="fd-stat">

                <i class="bi bi-person-plus text-success"></i>

                <div class="fd-label mt-2">
                    Waiting for Check-in
                </div>

                <div class="fd-number">
                    {{ $counts['arrivals'] }}
                </div>

            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="fd-stat">

                <i class="bi bi-house-check text-success"></i>

                <div class="fd-label mt-2">
                    Currently In House
                </div>

                <div class="fd-number">
                    {{ $counts['in_house'] }}
                </div>

            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="fd-stat">

                <i class="bi bi-box-arrow-right text-success"></i>

                <div class="fd-label mt-2">
                    Check-out Due
                </div>

                <div class="fd-number">
                    {{ $counts['departures'] }}
                </div>

            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="fd-stat">

                <i class="bi bi-calendar-event text-success"></i>

                <div class="fd-label mt-2">
                    Upcoming Reservations
                </div>

                <div class="fd-number">
                    {{ $counts['upcoming'] }}
                </div>

            </div>
        </div>

    </div>

    {{-- BOOKINGS MANAGEMENT --}}
    <div class="fd-card">

        {{-- FILTER TABS --}}
        <div class="d-flex gap-1 flex-wrap mb-3">

            @foreach ([
                'arrivals' => 'Arrivals',
                'in_house' => 'In House',
                'departures' => 'Due Check-out',
                'upcoming' => 'Upcoming',
                'completed' => 'Completed',
                'all' => 'All Bookings'
            ] as $key => $label)

                <a class="fd-tab {{ $tab === $key ? 'active' : '' }}"
                   href="{{ route('front-desk.index', ['tab' => $key]) }}">

                    {{ $label }}

                    @if (isset($counts[$key]))
                        ({{ $counts[$key] }})
                    @endif

                </a>

            @endforeach

        </div>

        {{-- SEARCH --}}
        <form method="GET"
              action="{{ route('front-desk.index') }}"
              class="row g-2 mb-3">

            <input type="hidden"
                   name="tab"
                   value="{{ $tab }}">

            <div class="col-md-9">
                <input
                    type="search"
                    class="form-control"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search guest name, phone or room number..."
                >
            </div>

            <div class="col-md-3 d-flex gap-2">

                <button class="btn btn-success flex-grow-1"
                        type="submit">
                    <i class="bi bi-search me-1"></i>
                    Search
                </button>

                <a href="{{ route('front-desk.index', ['tab' => $tab]) }}"
                   class="btn btn-outline-secondary"
                   title="Reset">

                    <i class="bi bi-arrow-clockwise"></i>

                </a>

            </div>

        </form>

        {{-- BOOKINGS TABLE --}}
        <div class="table-responsive">

            <table class="table fd-table align-middle">

                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Guest / Contact</th>
                        <th>Room</th>
                        <th>Arrival</th>
                        <th>Departure</th>
                        <th>Stay Status</th>
                        <th>Payment</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($bookings as $booking)

                        @php
                            $waiting =
                                $booking->status === 'confirmed' &&
                                !$booking->checked_in_at;

                            $inHouse =
                                $booking->status === 'confirmed' &&
                                $booking->checked_in_at &&
                                !$booking->checked_out_at;

                            $canCheckIn =
                                $waiting &&
                                $booking->check_in->toDateString() <= $today &&
                                $booking->check_out->toDateString() > $today;
                        @endphp

                        <tr>

                            {{-- BOOKING ID --}}
                            <td class="fw-bold text-success">
                                #{{ $booking->id }}
                            </td>

                            {{-- GUEST --}}
                            <td>

                                <strong>
                                    {{ $booking->guest?->full_name ?? 'N/A' }}
                                </strong>

                                <div class="fd-note">
                                    {{ $booking->guest?->phone ?? 'N/A' }}
                                </div>

                            </td>

                            {{-- ROOM --}}
                            <td>

                                <strong>
                                    {{ $booking->room?->room_number ?? '-' }}
                                </strong>

                                <div class="fd-note">
                                    {{ $booking->room?->roomType?->name ?? '' }}
                                </div>

                            </td>

                            {{-- DATES --}}
                            <td class="text-nowrap">
                                {{ $booking->check_in->format('d M Y') }}
                            </td>

                            <td class="text-nowrap">
                                {{ $booking->check_out->format('d M Y') }}
                            </td>

                            {{-- CHECK-IN STATUS --}}
                            <td>

                                @if ($booking->status === 'cancelled')

                                    <span class="badge bg-secondary">
                                        Cancelled
                                    </span>

                                @elseif ($booking->status === 'checked_out')

                                    <span class="badge bg-secondary">
                                        Checked Out
                                    </span>

                                @elseif ($inHouse)

                                    <span class="badge bg-success">
                                        Checked In
                                    </span>

                                    <div class="fd-note mt-1">
                                        {{ \Carbon\Carbon::parse($booking->checked_in_at)->format('d M, h:i A') }}
                                    </div>

                                @elseif ($canCheckIn)

                                    <span class="badge bg-warning text-dark">
                                        Waiting Arrival
                                    </span>

                                @elseif (
                                    $waiting &&
                                    $booking->check_in->toDateString() > $today
                                )

                                    <span class="badge bg-info text-dark">
                                        Upcoming
                                    </span>

                                @else

                                    <span class="badge bg-secondary">
                                        Not Checked In
                                    </span>

                                @endif

                            </td>

                            {{-- PAYMENT --}}
                            <td>

                                <span class="badge {{ $booking->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">

                                    {{ ucfirst($booking->payment_status) }}

                                </span>

                            </td>

                            {{-- ACTION --}}
                            <td class="text-end text-nowrap">

                                @if ($canCheckIn)

                                    <form method="POST"
                                          action="{{ route('front-desk.check-in', $booking) }}"
                                          onsubmit="return confirm('Check in this guest?')">

                                        @csrf

                                        <button type="submit"
                                                class="btn btn-success btn-sm">

                                            <i class="bi bi-box-arrow-in-right me-1"></i>
                                            Check In

                                        </button>

                                    </form>

                                @elseif ($inHouse)

                                    <form method="POST"
                                          action="{{ route('front-desk.check-out', $booking) }}"
                                          onsubmit="return confirm('Check out this guest? Payment status will not change.')">

                                        @csrf

                                        <button type="submit"
                                                class="btn btn-outline-success btn-sm">

                                            <i class="bi bi-box-arrow-right me-1"></i>
                                            Check Out

                                        </button>

                                    </form>

                                @else

                                    <span class="fd-note">—</span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8"
                                class="text-center text-muted py-5">

                                No bookings found for this view.

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINATION --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">

            <small class="text-muted">
                Showing {{ $bookings->firstItem() ?? 0 }}
                –
                {{ $bookings->lastItem() ?? 0 }}
                of {{ $bookings->total() }}
            </small>

            {{ $bookings->links('pagination::bootstrap-5') }}

        </div>

    </div>

    <p class="fd-note mt-3">

        <i class="bi bi-info-circle me-1"></i>

        Check-in records the real arrival time.
        Check-out closes the stay but does not mark
        an unpaid booking as paid.

        Use Reservation Management to update payment details.

    </p>

</div>

@endsection
