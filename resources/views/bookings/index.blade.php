@extends('layouts.app')

@section('title', 'Reservation Management - PITO HOTEL')
@section('heading', 'Reservation Management')

@section('content')

    <link rel="stylesheet" href="{{ asset('css/views/bookings/index.css') }}?v=5">
    <link rel="stylesheet" href="{{ asset('css/views/bookings/country-dropdown.css') }}?v=6">

    <div class="rv-page">

        {{-- ======================================
         PAGE HEADER
    ====================================== --}}
        <div class="rv-toolbar">
            <div>
                <p class="rv-eyebrow">PITO HOTEL OPERATIONS</p>
                <p class="text-muted mb-0">
                    Manage reservations, guests, payments and receipts.
                </p>
            </div>

            <div class="rv-header-actions">
                <button type="button" class="btn rv-btn-soft" data-add-guest>
                    <i class="bi bi-person-plus me-1"></i>
                    Add Guest
                </button>

                <button type="button" class="btn btn-success" data-add-booking>
                    <i class="bi bi-calendar-plus me-1"></i>
                    New Booking
                </button>
            </div>
        </div>

        {{-- ======================================
         ALERTS
    ====================================== --}}

        @if (session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            {{-- Reopen the correct popup after validation --}}
            <div id="rvOldState" hidden data-kind="{{ old('_form_kind') }}" data-id="{{ old('_record_id') }}"
                data-name="{{ old('full_name') }}" data-phone="{{ old('phone') }}" data-email="{{ old('email') }}"
                data-nationality="{{ old('nationality') }}" data-address="{{ old('address') }}"
                data-customer-name="{{ old('customer_name') }}" data-customer-phone="{{ old('customer_phone') }}"
                data-room-id="{{ old('room_id') }}" data-check-in="{{ old('check_in') }}"
                data-check-out="{{ old('check_out') }}" data-adults="{{ old('adults') }}"
                data-notes="{{ old('notes') }}" data-status="{{ old('status') }}"
                data-payment-status="{{ old('payment_status') }}" data-payment-method="{{ old('payment_method') }}">
            </div>
        @endif

        {{-- ======================================
         BOOKINGS TABLE
    ====================================== --}}
        <section class="rv-card" id="reservations">

            <div class="rv-card-heading">
                <div>
                    <h5>
                        <i class="bi bi-calendar-check me-2 text-success"></i>
                        All Reservations
                    </h5>
                    <p>View bookings and manage payments.</p>
                </div>
                <span class="rv-count">
                    {{ $bookings->total() }} Bookings
                </span>
            </div>

            {{-- BOOKING SEARCH --}}
            <form method="GET" action="{{ route('bookings.index') }}" class="row g-3 mb-4">

                <div class="col-md-6">
                    <label class="form-label">Search Booking</label>
                    <input type="search" name="search" class="form-control" value="{{ request('search') }}"
                        placeholder="Guest, phone or room number">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Booking Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>

                        @foreach (['confirmed', 'checked_out', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>
                                {{ ucwords(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-success flex-fill">
                        <i class="bi bi-search me-1"></i>
                        Search
                    </button>

                    <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary" title="Clear filters">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table rv-table align-middle">

                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Booking ID</th>
                            <th>Guest</th>
                            <th>Room</th>
                            <th>Stay</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>

                                {{-- 1. ROW NUMBER --}}
                                <td>
                                    <strong class="text-success">
                                        {{ $bookings->firstItem() + $loop->index }}
                                    </strong>
                                </td>

                                {{-- 2. BOOKING ID --}}
                                <td>
                                    <strong>
                                        #{{ $booking->id }}
                                    </strong>
                                </td>

                                {{-- 3. GUEST --}}
                                <td>
                                    <strong>
                                        {{ $booking->guest?->full_name ?? 'Unknown Guest' }}
                                    </strong>

                                    <small class="d-block text-muted">
                                        {{ $booking->guest?->phone ?? '' }}
                                    </small>
                                </td>

                                {{-- 4. ROOM --}}
                                <td>
                                    Room {{ $booking->room?->room_number ?? '-' }}
                                </td>

                                {{-- 5. STAY --}}
                                <td class="rv-stay">
                                    {{ $booking->check_in->format('d M Y') }}

                                    <span class="text-muted">to</span>

                                    {{ $booking->check_out->format('d M Y') }}

                                    <small class="d-block text-muted">
                                        {{ $booking->nights }} night(s)
                                    </small>
                                </td>

                                {{-- 6. TOTAL --}}
                                <td class="fw-bold text-success">
                                    ${{ number_format($booking->total_amount, 2) }}
                                </td>

                                {{-- 7. BOOKING STATUS --}}
                                <td>
                                    <span class="rv-status rv-status-{{ $booking->status }}">
                                        {{ ucwords(str_replace('_', ' ', $booking->status)) }}
                                    </span>
                                </td>

                                {{-- 8. PAYMENT --}}
                                <td>
                                    <span class="rv-status rv-status-{{ $booking->payment_status }}">
                                        {{ ucfirst($booking->payment_status) }}
                                    </span>

                                    @if ($hasPaymentMethod && $booking->payment_method)
                                        <small class="d-block text-muted mt-1">
                                            {{ strtoupper($booking->payment_method) }}
                                        </small>
                                    @endif
                                </td>

                                {{-- 9. ACTIONS --}}
                                <td class="text-end">
                                    <div class="rv-action-group">

                                        {{-- PRINT RECEIPT --}}
                                        <button type="button" class="rv-print-button" data-print-receipt
                                            data-preview-url="{{ route('bookings.receipt.preview', $booking) }}"
                                            data-download-url="{{ route('bookings.receipt', $booking) }}"
                                            title="Preview and Print">
                                            <i class="bi bi-printer"></i>
                                            Print
                                        </button>

                                        {{-- MANAGE BOOKING --}}
                                        <button type="button" class="rv-manage-button" data-booking-manage
                                            data-id="{{ $booking->id }}"
                                            data-update-url="{{ route('bookings.update', $booking) }}"
                                            data-preview-url="{{ route('bookings.receipt.preview', $booking) }}"
                                            data-download-url="{{ route('bookings.receipt', $booking) }}"
                                            data-guest="{{ $booking->guest?->full_name ?? 'Unknown' }}"
                                            data-room="{{ $booking->room?->room_number ?? '-' }}"
                                            data-total="{{ $booking->total_amount }}" data-status="{{ $booking->status }}"
                                            data-payment-status="{{ $booking->payment_status }}"
                                            data-payment-method="{{ $hasPaymentMethod ? $booking->payment_method ?? 'unspecified' : '' }}"
                                            @can('admin')
                            data-delete-url="{{ route('bookings.destroy', $booking) }}"
                            data-was-paid="{{ $booking->paid_at ? '1' : '0' }}"
                        @endcan>
                                            <i class="bi bi-sliders2"></i>
                                            Manage
                                        </button>

                                    </div>
                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                                    No bookings found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <div class="mt-3">
                {{-- PITO_PAGINATION_BOOKINGS_V1 --}}
<div class="rv-pagination">
    <div class="rv-pagination-info">
        <span>
            Showing {{ $bookings->firstItem() ?? 0 }}
            to {{ $bookings->lastItem() ?? 0 }}
            of {{ $bookings->total() }} bookings
        </span>
        <span>
            Page {{ $bookings->currentPage() }}
            of {{ $bookings->lastPage() }}
        </span>
    </div>
    <div class="rv-pagination-links">
        {{ $bookings->fragment('reservations')->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
</div>
            </div>
        </section>

        {{-- ======================================
         GUESTS TABLE - SAME PAGE
    ====================================== --}}
        <section class="rv-card" id="guests">

            <div class="rv-card-heading">
                <div>
                    <h5>
                        <i class="bi bi-people me-2 text-success"></i>
                        Hotel Guests
                    </h5>
                    <p>Manage guest profiles and contact details.</p>
                </div>

                <span class="rv-count">
                    {{ $guests->total() }} Guests
                </span>
            </div>

            {{-- GUEST SEARCH --}}
            <form method="GET" action="{{ route('bookings.index') }}#guests" class="d-flex flex-wrap gap-2 mb-4">

                <input type="search" name="guest_search" class="form-control rv-guest-search"
                    value="{{ request('guest_search') }}" placeholder="Search name, phone or email">

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-search me-1"></i>
                    Search
                </button>

                <a href="{{ route('bookings.index') }}#guests" class="btn btn-outline-secondary">
                    Clear
                </a>
            </form>

            <div class="table-responsive">
                <table class="table rv-table align-middle">
                    <thead>
                        <tr>
                            <th>Guest</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Nationality</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($guests as $guest)
                            <tr>
                                <td>
                                    <strong>{{ $guest->full_name }}</strong>
                                    <small class="d-block text-muted">
                                        Guest #{{ $guest->id }}
                                    </small>
                                </td>

                                <td>{{ $guest->phone }}</td>
                                <td>{{ $guest->email ?: '-' }}</td>
                                <td>{{ $guest->nationality ?: '-' }}</td>

                                <td class="text-end">
                                    <button type="button" class="rv-manage-button" data-guest-manage
                                        data-id="{{ $guest->id }}"
                                        data-update-url="{{ route('guests.update', $guest) }}"
                                        @can('admin')
                                        data-delete-url="{{ route('guests.destroy', $guest) }}"
                                        @endcan
                                        data-name="{{ $guest->full_name }}" data-phone="{{ $guest->phone }}"
                                        data-email="{{ $guest->email }}" data-nationality="{{ $guest->nationality }}"
                                        data-address="{{ $guest->address }}">
                                        <i class="bi bi-sliders2"></i>
                                        Manage
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-people fs-2 d-block mb-2"></i>
                                    No guests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{-- PITO_PAGINATION_GUESTS_V1 --}}
<div class="rv-pagination">
    <div class="rv-pagination-info">
        <span>
            Showing {{ $guests->firstItem() ?? 0 }}
            to {{ $guests->lastItem() ?? 0 }}
            of {{ $guests->total() }} guests
        </span>
        <span>
            Page {{ $guests->currentPage() }}
            of {{ $guests->lastPage() }}
        </span>
    </div>
    <div class="rv-pagination-links">
        {{ $guests->fragment('guests')->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
</div>
            </div>
        </section>

    </div>

    {{-- ======================================
     ADD / EDIT GUEST POPUP
====================================== --}}
    <dialog id="guestDialog" class="rv-dialog" aria-labelledby="guestDialogTitle">

        <div class="rv-dialog-header">
            <div>
                <p class="rv-eyebrow mb-1">GUEST PROFILE</p>
                <h5 id="guestDialogTitle" class="mb-0">Add Guest</h5>
            </div>

            <button type="button" class="btn-close" data-close-dialog="guestDialog" aria-label="Close"></button>
        </div>

        <form id="guestForm" method="POST" action="{{ route('guests.store') }}"
            data-store-url="{{ route('guests.store') }}"
            data-update-template="{{ route('guests.update', ['guest' => '__GUEST_ID__']) }}"
            @can('admin')
          data-delete-template="{{ route('guests.destroy', ['guest' => '__GUEST_ID__']) }}"
          @endcan>
            @csrf

            <input type="hidden" id="guestMethod" name="_method" value="PUT" disabled>

            <input type="hidden" id="guestKind" name="_form_kind" value="guest_create">

            <input type="hidden" id="guestRecordId" name="_record_id">

            <div class="rv-dialog-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Full Name *</label>
                        <input id="guestName" name="full_name" class="form-control" maxlength="255"
                            placeholder="Guest full name" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Phone Number *</label>
                        <input id="guestPhone" name="phone" type="tel" class="form-control" maxlength="30"
                            placeholder="012345678" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input id="guestEmail" name="email" type="email" class="form-control" maxlength="255">
                    </div>



                    {{-- SEARCHABLE COUNTRY DROPDOWN --}}
                    <div class="col-md-6">
                        <label for="guestNationality" class="form-label">
                            <i class="bi bi-globe-asia-australia text-success me-1"></i>
                            Nationality / Country
                        </label>

                        <div class="rv-country-picker" id="guestCountryPicker">

                            <input type="text" id="guestNationality" name="nationality" class="form-control"
                                placeholder="Select or search country..." maxlength="255" autocomplete="off"
                                spellcheck="false" role="combobox" aria-autocomplete="list"
                                aria-controls="guestCountryMenu" aria-expanded="false">

                            <button type="button" id="guestCountryToggle" class="rv-country-toggle"
                                aria-label="Choose country">
                                <i class="bi bi-chevron-down"></i>
                            </button>

                        </div>

                        <small class="text-muted d-block mt-1">
                            Click to choose or type to search.
                        </small>
                    </div>



                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea id="guestAddress" name="address" rows="3" class="form-control" maxlength="1000"></textarea>
                    </div>

                </div>
            </div>

            <div class="rv-dialog-footer">

                @can('admin')
                    <button id="guestDeleteButton" type="button" class="btn rv-danger-button me-auto" hidden>
                        <i class="bi bi-trash3 me-1"></i>
                        Delete Guest
                    </button>
                @endcan

                <button type="button" class="btn btn-outline-secondary" data-close-dialog="guestDialog">
                    Cancel
                </button>

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check2-circle me-1"></i>
                    Save Guest
                </button>
            </div>



        </form>

        {{-- Searchable country options belong to the Guest popup. --}}
        <div id="guestCountryMenu" class="rv-country-menu" role="listbox" aria-label="Choose a country"
            popover="manual"></div>
    </dialog>

    {{-- ======================================
     NEW BOOKING POPUP
====================================== --}}
    <dialog id="newBookingDialog" class="rv-dialog" aria-labelledby="newBookingTitle">

        <div class="rv-dialog-header">
            <div>
                <p class="rv-eyebrow mb-1">NEW RESERVATION</p>
                <h5 id="newBookingTitle" class="mb-0">
                    Create New Booking
                </h5>
            </div>

            <button type="button" class="btn-close" data-close-dialog="newBookingDialog" aria-label="Close"></button>
        </div>

        <form id="newBookingForm" method="POST" action="{{ route('bookings.store') }}">
            @csrf

            <input type="hidden" name="_form_kind" value="booking_create">

            <div class="rv-dialog-body">
                <div class="row g-3">


                    {{-- CUSTOMER NAME --}}
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-person text-success me-1"></i>
                            Customer Name *
                        </label>

                        <input type="text" id="bookingCustomerName" name="customer_name" class="form-control"
                            maxlength="255" placeholder="Enter customer full name" required>
                    </div>

                    {{-- CUSTOMER PHONE --}}
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-telephone text-success me-1"></i>
                            Phone Number *
                        </label>

                        <input type="tel" id="bookingCustomerPhone" name="customer_phone" class="form-control"
                            maxlength="30" placeholder="012345678" required>

                        <small class="text-muted">
                            Customer is automatically registered when booking.
                        </small>
                    </div>



                    {{-- SMART ROOM AVAILABILITY --}}
                    <div class="col-md-6">

                        <label class="form-label" for="bookingRoom">
                            <i class="bi bi-door-open text-success me-1"></i>
                            Hotel Room *
                        </label>

                        <select id="bookingRoom" name="room_id" class="form-select"
                            data-availability-url="{{ route('bookings.available-rooms') }}" required disabled>
                            <option value="">
                                Select Check-In and Check-Out first
                            </option>
                        </select>

                        <small id="bookingRoomHelp" class="text-muted d-block mt-1" aria-live="polite">
                            Choose your dates to see available rooms.
                        </small>

                    </div>


                    <div class="col-md-6">
                        <label class="form-label">Check-In Date *</label>
                        <input id="bookingCheckIn" name="check_in" type="date" class="form-control"
                            min="{{ now()->toDateString() }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Check-Out Date *</label>
                        <input id="bookingCheckOut" name="check_out" type="date" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Number of Adults *</label>
                        <input id="bookingAdults" name="adults" type="number" class="form-control" min="1"
                            max="20" value="1" required>
                    </div>

                    {{-- PAYMENT STATUS: Record whether the customer has paid --}}
                    <div class="col-md-6">
                        <label class="form-label" for="newPaymentStatus">
                            <i class="bi bi-wallet2 text-success me-1"></i>
                            Payment Status *
                        </label>
                        <select id="newPaymentStatus" name="payment_status" class="form-select" required>
                            <option value="unpaid" @selected(old('payment_status', 'unpaid') === 'unpaid')>
                                Unpaid - Not Yet Paid
                            </option>
                            <option value="paid" @selected(old('payment_status') === 'paid')>
                                Paid - Payment Received
                            </option>
                        </select>
                    </div>

                    {{-- PAYMENT METHOD: Shown when the database column exists --}}
                    @if ($hasPaymentMethod)
                        <div class="col-md-6">
                            <label class="form-label" for="newPaymentMethod">
                                <i class="bi bi-credit-card text-success me-1"></i>
                                Payment Method
                            </label>
                            <select id="newPaymentMethod" name="payment_method" class="form-select">
                                <option value="unspecified" @selected(old('payment_method', 'unspecified') === 'unspecified')>Unspecified</option>
                                <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                                <option value="khqr" @selected(old('payment_method') === 'khqr')>KHQR</option>
                                <option value="visa" @selected(old('payment_method') === 'visa')>Visa</option>
                                <option value="mastercard" @selected(old('payment_method') === 'mastercard')>Mastercard</option>
                            </select>
                            <small class="text-muted">Required when Payment Status is Paid.</small>
                        </div>
                    @endif

                    <div class="col-12">
                        <label class="form-label">Booking Notes</label>
                        <textarea id="bookingNotes" name="notes" class="form-control" rows="3" maxlength="2000"
                            placeholder="Special guest requests..."></textarea>
                    </div>

                    <div class="col-12">
                        <div class="rv-price-preview">
                            <span>Estimated Booking Total</span>
                            <strong id="bookingEstimate">$0.00</strong>
                            <small id="bookingNights" class="d-block">
                                Select room and dates
                            </small>
                        </div>

                        <small class="text-muted d-block mt-2">
                            Final price is calculated by the server.
                            Choose whether payment was received and how the customer paid.
                        </small>
                    </div>

                </div>
            </div>

            <div class="rv-dialog-footer">
                <button type="button" class="btn btn-outline-secondary" data-close-dialog="newBookingDialog">
                    Cancel
                </button>

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check2-circle me-1"></i>
                    Save Booking
                </button>
            </div>


        </form>
    </dialog>

    {{-- ======================================
     MANAGE BOOKING POPUP
====================================== --}}
    <dialog id="bookingDialog" class="rv-dialog rv-small-dialog" aria-labelledby="bookingDialogTitle">

        <div class="rv-dialog-header">
            <div>
                <p class="rv-eyebrow mb-1">BOOKING DETAILS</p>
                <h5 id="bookingDialogTitle" class="mb-0">
                    Manage Booking
                </h5>
            </div>

            <button type="button" class="btn-close" data-close-dialog="bookingDialog" aria-label="Close"></button>
        </div>

        <form id="bookingUpdateForm" method="POST"
            data-update-template="{{ route('bookings.update', ['booking' => '__BOOKING_ID__']) }}">
            @csrf
            @method('PUT')

            <input type="hidden" name="_form_kind" value="booking_update">

            <input type="hidden" name="_record_id" id="bookingRecordId">

            <div class="rv-dialog-body">

                <div class="rv-booking-summary mb-4">
                    <strong id="bookingSummaryGuest"></strong>

                    <div id="bookingSummaryRoom" class="text-muted small"></div>

                    <div id="bookingSummaryTotal" class="rv-summary-total"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Booking Status *</label>
                    <select id="editBookingStatus" name="status" class="form-select" required>
                        <option value="confirmed">Confirmed</option>
                        <option value="checked_out">Checked Out</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Payment Status *</label>
                    <select id="editPaymentStatus" name="payment_status" class="form-select" required>
                        <option value="unpaid">Unpaid</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>

                @if ($hasPaymentMethod)
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select id="editPaymentMethod" name="payment_method" class="form-select">
                            <option value="unspecified">Unspecified</option>
                            <option value="cash">Cash</option>
                            <option value="khqr">KHQR</option>
                            <option value="visa">Visa</option>
                            <option value="mastercard">Mastercard</option>
                        </select>
                    </div>
                @endif

                <div class="rv-info-note">
                    <i class="bi bi-info-circle me-1"></i>
                    Paid bookings require refund handling
                    before cancellation.
                </div>
            </div>

            <div class="rv-dialog-footer">

                @can('admin')
                    <button type="button" id="deleteBookingButton" class="btn rv-danger-button" hidden>
                        <i class="bi bi-trash3 me-1"></i>
                        Delete Booking
                    </button>
                @endcan

                <div class="rv-footer-tools me-auto">
                    <button id="bookingPrintButton" type="button" class="btn rv-btn-soft">
                        <i class="bi bi-printer me-1"></i>
                        Print
                    </button>

                    <a id="bookingReceiptLink" href="#" class="btn btn-outline-success">
                        <i class="bi bi-file-earmark-pdf me-1"></i>
                        PDF
                    </a>
                </div>

                <button type="button" class="btn btn-outline-secondary" data-close-dialog="bookingDialog">
                    Cancel
                </button>

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check2-circle me-1"></i>
                    Save Changes
                </button>

            </div>
        </form>
    </dialog>

    {{-- ======================================
     RECEIPT PREVIEW / PRINT POPUP
====================================== --}}
    <dialog id="receiptDialog" class="rv-dialog rv-receipt-dialog" aria-labelledby="receiptDialogTitle">

        <div class="rv-dialog-header">
            <div>
                <p class="rv-eyebrow mb-1">PITO HOTEL</p>
                <h5 id="receiptDialogTitle" class="mb-0">
                    <i class="bi bi-receipt me-2"></i>
                    Receipt Preview
                </h5>
            </div>

            <button type="button" class="btn-close" data-close-dialog="receiptDialog" aria-label="Close"></button>
        </div>

        <div class="rv-receipt-body">
            <iframe id="receiptFrame" title="PITO HOTEL Receipt Preview"></iframe>
        </div>

        <div class="rv-dialog-footer">

            <button type="button" class="btn btn-outline-secondary me-auto" data-close-dialog="receiptDialog">
                Close
            </button>

            <a id="receiptDownloadLink" href="#" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-pdf me-1"></i>
                Download PDF
            </a>

            <button id="receiptPrintNow" type="button" class="btn btn-success" disabled>
                <i class="bi bi-printer me-1"></i>
                Print Receipt
            </button>

        </div>
    </dialog>

    {{-- ======================================
     DELETE GUEST CONFIRMATION
====================================== --}}
    @can('admin')
        <dialog id="deleteGuestDialog" class="rv-dialog rv-delete-dialog" aria-labelledby="deleteGuestTitle">

            <div class="rv-dialog-header">
                <h5 id="deleteGuestTitle" class="mb-0 text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Confirm Delete
                </h5>

                <button type="button" class="btn-close" data-close-dialog="deleteGuestDialog" aria-label="Close"></button>
            </div>

            <form id="deleteGuestForm" method="POST">
                @csrf
                @method('DELETE')

                <div class="rv-dialog-body">
                    <p>Are you sure you want to delete this guest?</p>

                    <h6 id="deleteGuestName" class="fw-bold text-danger"></h6>

                    <p class="text-muted small mb-0">
                        Guests with booking history cannot be deleted.
                    </p>
                </div>

                <div class="rv-dialog-footer">
                    <button type="button" class="btn btn-outline-secondary" data-close-dialog="deleteGuestDialog">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash3 me-1"></i>
                        Yes, Delete
                    </button>
                </div>
            </form>
        </dialog>
    @endcan


    {{-- =========================================
     DELETE BOOKING CONFIRMATION
========================================= --}}
    @can('admin')
        <dialog id="deleteBookingDialog" class="rv-dialog rv-delete-dialog" aria-labelledby="deleteBookingTitle">

            <div class="rv-dialog-header">
                <h5 id="deleteBookingTitle" class="mb-0 text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Delete Booking?
                </h5>

                <button type="button" class="btn-close" data-close-dialog="deleteBookingDialog"
                    aria-label="Close"></button>
            </div>

            <form id="deleteBookingForm" method="POST">
                @csrf
                @method('DELETE')

                <div class="rv-dialog-body">
                    <p>Are you sure you want to delete:</p>

                    <h6 id="deleteBookingName" class="fw-bold text-danger"></h6>

                    <p class="text-muted small mb-0">
                        Only unpaid bookings with no recorded payment history can be deleted.
                        This action permanently removes the reservation and cannot be undone.
                    </p>
                </div>

                <div class="rv-dialog-footer">
                    <button type="button" class="btn btn-outline-secondary" data-close-dialog="deleteBookingDialog">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash3 me-1"></i>
                        Yes, Delete
                    </button>
                </div>
            </form>
        </dialog>
    @endcan



    <script src="{{ asset('js/views/bookings/index.js') }}?v=5" defer></script>
    <script src="{{ asset('js/views/bookings/delete-booking.js') }}?v=1" defer></script>
    {{-- COUNTRY SEARCH --}}
    <script src="{{ asset('js/views/bookings/countries.js') }}?v=6" defer></script>
    <script src="{{ asset('js/views/bookings/room-availability.js') }}?v=1" defer></script>

@endsection
