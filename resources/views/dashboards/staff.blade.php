
@extends('layouts.app')

@push('styles')
    <link rel="stylesheet"
          href="{{ asset('css/views/dashboards/staff.css') }}">
@endpush

@section('title', 'Staff Dashboard - Pito Hotel')
@section('heading', 'Staff Workspace')

@section('content')

    {{-- =====================================
         STAFF WELCOME
    ===================================== --}}

    <div class="card p-4 mb-4">

        <div class="d-flex justify-content-between
                    align-items-center flex-wrap gap-3">

            <div>
                <h3 class="text-success fw-bold">

                    <i class="bi bi-person-check me-2"></i>
                    Hello, {{ auth()->user()->name }}!

                </h3>

                <p class="text-muted mb-0">
                    Welcome to your daily work dashboard
                    at PITO HOTEL.
                </p>
            </div>

            <span class="badge bg-primary px-3 py-2">
                <i class="bi bi-person-badge me-1"></i>
                Staff Account
            </span>

        </div>

    </div>

    {{-- =====================================
         TODAY'S WORK SUMMARY
    ===================================== --}}

    <div class="row g-4 mb-4">

        {{-- TODAY'S CHECK-INS --}}
        <div class="col-xl-3 col-md-6">

            <div class="staff-stat">

                <i class="bi bi-calendar-check"></i>

                <div class="text-muted mt-3">
                    Today's Check-ins
                </div>

                <div class="staff-stat-number">
                    {{ $todayCheckIns->count() }}
                </div>

            </div>
        </div>

        {{-- TODAY'S CHECK-OUTS --}}
        <div class="col-xl-3 col-md-6">

            <div class="staff-stat blue">

                <i class="bi bi-box-arrow-right"></i>

                <div class="text-muted mt-3">
                    Today's Check-outs
                </div>

                <div class="staff-stat-number">
                    {{ $todayCheckOuts->count() }}
                </div>

            </div>
        </div>

        {{-- AVAILABLE ROOMS --}}
        <div class="col-xl-3 col-md-6">

            <div class="staff-stat orange">

                <i class="bi bi-door-open"></i>

                <div class="text-muted mt-3">
                    Rooms Marked Available
                </div>

                <div class="staff-stat-number">
                    {{ $availableRooms }}
                </div>

            </div>
        </div>

        {{-- MY CREATED BOOKINGS --}}
        <div class="col-xl-3 col-md-6">

            <div class="staff-stat purple">

                <i class="bi bi-journal-check"></i>

                <div class="text-muted mt-3">
                    My Created Bookings
                </div>

                <div class="staff-stat-number">
                    {{ $myBookingsCount }}
                </div>

            </div>
        </div>

    </div>

    {{-- =====================================
         QUICK ACTIONS
    ===================================== --}}

    <div class="card p-4 mb-4">

        <h4 class="text-success fw-bold mb-4">

            <i class="bi bi-lightning-charge me-2"></i>
            Quick Actions

        </h4>

        <div class="row g-3">

            {{-- NEW BOOKING --}}
            <div class="col-md-4">

                <a href="{{ route('bookings.index') }}"
                   class="staff-action">

                    <i class="bi bi-calendar-plus"></i>

                    <h5 class="mt-3">
                        New Booking
                    </h5>

                    <small class="text-muted">
                        Reserve a hotel room for a guest.
                    </small>

                </a>

            </div>

            {{-- MANAGE GUESTS --}}
            <div class="col-md-4">

                <a href="{{ route('bookings.index') }}#guests"
                   class="staff-action">

                    <i class="bi bi-person-plus"></i>

                    <h5 class="mt-3">
                        Manage Guests
                    </h5>

                    <small class="text-muted">
                        Register and update guest details.
                    </small>

                </a>

            </div>

            {{-- MANAGE ROOMS --}}
            <div class="col-md-4">

                {{--
                    FIXED:
                    Admin and Staff use rooms.index.

                    Room Management has Add/Edit
                    popups on the same page.

                    Staff cannot delete rooms because
                    the DELETE route is Admin Only.
                --}}

                <a href="{{ route('rooms.index') }}#rooms"
                   class="staff-action">

                    <i class="bi bi-door-open"></i>

                    <h5 class="mt-3">
                        Manage Rooms
                    </h5>

                    <small class="text-muted">
                        Add or edit rooms and upload pictures.
                    </small>

                </a>

            </div>

        </div>

    </div>

    {{-- =====================================
         TODAY'S CHECK-INS & CHECK-OUTS
    ===================================== --}}

    <div class="row g-4 mb-4">

        {{-- TODAY'S CHECK-INS --}}
        <div class="col-lg-6">

            <div class="card p-4 h-100">

                <h4 class="text-success fw-bold mb-3">

                    <i class="bi bi-calendar-check me-2"></i>
                    Today's Check-ins

                </h4>

                @forelse($todayCheckIns as $booking)

                    <div class="border-bottom py-3">

                        <div class="fw-bold">

                            <i class="bi bi-person-circle text-success me-1"></i>

                            {{ $booking->guest?->full_name ?? 'Guest' }}

                        </div>

                        <small class="text-muted">

                            Room
                            {{ $booking->room?->room_number ?? '-' }}

                            | Booking #{{ $booking->id }}

                        </small>

                    </div>

                @empty

                    <p class="text-muted">
                        No check-ins scheduled for today.
                    </p>

                @endforelse

            </div>

        </div>

        {{-- TODAY'S CHECK-OUTS --}}
        <div class="col-lg-6">

            <div class="card p-4 h-100">

                <h4 class="text-success fw-bold mb-3">

                    <i class="bi bi-box-arrow-right me-2"></i>
                    Today's Check-outs

                </h4>

                @forelse($todayCheckOuts as $booking)

                    <div class="border-bottom py-3">

                        <div class="fw-bold">

                            <i class="bi bi-person-circle text-success me-1"></i>

                            {{ $booking->guest?->full_name ?? 'Guest' }}

                        </div>

                        <small class="text-muted">

                            Room
                            {{ $booking->room?->room_number ?? '-' }}

                            | Booking #{{ $booking->id }}

                        </small>

                    </div>

                @empty

                    <p class="text-muted">
                        No check-outs scheduled for today.
                    </p>

                @endforelse

            </div>

        </div>

    </div>

    {{-- =====================================
         MY RECENT BOOKINGS
    ===================================== --}}

    <div class="card p-4">

        <div class="d-flex justify-content-between
                    align-items-center mb-3 flex-wrap gap-2">

            <h4 class="text-success fw-bold mb-0">

                <i class="bi bi-clock-history me-2"></i>
                My Recent Bookings

            </h4>

            <a href="{{ route('bookings.index') }}"
               class="btn btn-outline-success btn-sm">

                View All

            </a>

        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Check-In</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($myRecentBookings as $booking)

                        <tr>

                            <td>
                                #{{ $booking->id }}
                            </td>

                            <td>
                                {{ $booking->guest?->full_name ?? '-' }}
                            </td>

                            <td>
                                {{ $booking->room?->room_number ?? '-' }}
                            </td>

                            <td>
                                {{ $booking->check_in->format('d M Y') }}
                            </td>

                            <td>

                                <span class="badge bg-success">

                                    {{ ucwords(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $booking->status
                                        )
                                    ) }}

                                </span>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5"
                                class="text-center py-4 text-muted">

                                You haven't created
                                any bookings yet.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

@endsection
