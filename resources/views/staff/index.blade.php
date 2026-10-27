@extends('layouts.app')

@section('title', 'Staff Management - PITO HOTEL')
@section('heading', 'Staff Management')

@section('content')

<link rel="stylesheet"
      href="{{ asset('css/views/staff/index.css') }}?v=1">

<div class="sm-page">

    {{-- PAGE HEADER --}}
    <div class="sm-toolbar">
        <div>
            <p class="sm-eyebrow">PITO HOTEL OPERATIONS</p>
            <p class="text-muted mb-0">
                Manage staff accounts, photos and access.
            </p>
        </div>

        <button class="btn btn-success"
                type="button"
                id="smAddStaff">
            <i class="bi bi-person-plus me-1"></i>
            Add Staff
        </button>
    </div>

    {{-- Validation messages are displayed by layouts.app.
         Keep this hidden state so existing staff/index.js can reopen the dialog. --}}
    @if ($errors->any())
        <div id="smOldState" hidden
             data-kind="{{ old('_form_kind') }}"
             data-id="{{ old('_record_id') }}"
             data-name="{{ old('name') }}"
             data-email="{{ old('email') }}">
        </div>
    @endif

    {{-- STAFF TABLE --}}
    <section class="sm-card" id="staff-members">

        <div class="sm-card-heading">
            <div>
                <h5 class="mb-1">
                    <i class="bi bi-people text-success me-2"></i>
                    All Staff Members
                </h5>
                <p class="text-muted mb-0">
                    Search staff and manage their accounts.
                </p>
            </div>

            <span class="sm-count">
                {{ $staffMembers->total() }} Staff
            </span>
        </div>

        {{-- SEARCH --}}
        <form method="GET"
              action="{{ route('staff.index') }}"
              class="sm-search-row">

            <input type="search"
                   name="search"
                   class="form-control"
                   value="{{ request('search') }}"
                   placeholder="Search staff name or email">

            <button type="submit" class="btn btn-success">
                <i class="bi bi-search me-1"></i>
                Search
            </button>

            <a href="{{ route('staff.index') }}"
               class="btn btn-outline-secondary"
               aria-label="Clear search">
                <i class="bi bi-arrow-clockwise"></i>
            </a>
        </form>

        {{-- STAFF LIST --}}
        <div class="table-responsive">

            <table class="table align-middle sm-table">
                <thead>
                    <tr>
                        <th>Staff</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Bookings Handled</th>
                        <th>Joined</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($staffMembers as $member)

                        <tr>
                            <td>
                                <div class="sm-person">

                                    @if ($member->photo)
                                        <img
                                            class="sm-avatar"
                                            src="{{ asset('storage/' . $member->photo) }}"
                                            alt="{{ $member->name }}">
                                    @else
                                        <span class="sm-avatar sm-avatar-empty">
                                            <i class="bi bi-person"></i>
                                        </span>
                                    @endif

                                    <div>
                                        <strong>{{ $member->name }}</strong>
                                        <small class="d-block text-muted">
                                            Staff #{{ $member->id }}
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <td>{{ $member->email }}</td>

                            <td>
                                <span class="sm-role">Staff</span>
                            </td>

                            <td>{{ $member->bookings_count }}</td>

                            <td>
                                {{ $member->created_at?->format('d M Y') ?? '-' }}
                            </td>

                            <td class="text-end">

                                <button
                                    type="button"
                                    class="sm-manage-button"
                                    data-sm-manage
                                    data-id="{{ $member->id }}"
                                    data-name="{{ $member->name }}"
                                    data-email="{{ $member->email }}"
                                    data-photo-url="{{ $member->photo ? asset('storage/' . $member->photo) : '' }}"
                                    data-bookings-count="{{ $member->bookings_count }}"
                                    data-update-url="{{ route('staff.update', $member) }}"
                                    data-delete-url="{{ route('staff.destroy', $member) }}">

                                    <i class="bi bi-sliders2 me-1"></i>
                                    Manage

                                </button>

                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="6"
                                class="text-center text-muted py-5">
                                <i class="bi bi-person-x fs-2 d-block mb-2"></i>
                                No staff found.
                            </td>
                        </tr>

                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- STAFF PAGINATION --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3">
            <div class="text-muted small">
                Showing {{ $staffMembers->firstItem() ?? 0 }}
                to {{ $staffMembers->lastItem() ?? 0 }}
                of {{ $staffMembers->total() }} Staff Members
                · Page {{ $staffMembers->currentPage() }} of {{ $staffMembers->lastPage() }}
            </div>
            <div>
                {{ $staffMembers->fragment('staff-members')
                    ->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </div>

    </section>

    {{-- ACTIVITY LOGS: Admin can review staff and hotel activity. --}}
    <section class="sm-card mt-4" id="staff-activities">
        <div class="sm-card-heading">
            <div>
                <h5 class="mb-1">
                    <i class="bi bi-activity text-success me-2"></i>
                    Recent Staff Activities
                </h5>
                <p class="text-muted mb-0">
                    Staff logins, bookings, room changes and payment updates.
                </p>
            </div>
            @if(isset($recentActivities) && $recentActivities instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                <span class="sm-count">{{ $recentActivities->total() }} Activities</span>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table align-middle sm-table">
                <thead>
                    <tr>
                        <th>Staff / Admin</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Date &amp; Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($recentActivities ?? collect()) as $activity)
                        <tr>
                            <td>
                                <i class="bi bi-person-circle text-success me-1"></i>
                                <strong>{{ $activity->actor_name ?? 'System' }}</strong>
                            </td>
                            <td>
                                @php
                                    $action = $activity->action ?? '';
                                    $badgeClass = match (true) {
                                        in_array($action, ['login', 'staff.login', 'room_created', 'booking_created'], true) => 'bg-success',
                                        in_array($action, ['logout', 'staff.logout'], true) => 'bg-secondary',
                                        in_array($action, ['room_deleted', 'booking_deleted'], true) => 'bg-danger',
                                        in_array($action, ['room_updated', 'booking_status_updated', 'payment_updated'], true) => 'bg-primary',
                                        default => 'bg-info text-dark',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ ucwords(str_replace(['_', '.'], ' ', $action)) }}
                                </span>
                            </td>
                            <td>{{ $activity->description ?? '-' }}</td>
                            <td class="text-nowrap">
                                {{ $activity->created_at?->format('d M Y, h:i A') ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                <i class="bi bi-clock-history fs-2 d-block mb-2"></i>
                                No activities recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($recentActivities) && $recentActivities instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3">
                <div class="text-muted small">
                    Showing {{ $recentActivities->firstItem() ?? 0 }}
                    to {{ $recentActivities->lastItem() ?? 0 }}
                    of {{ $recentActivities->total() }} Activities
                    · Page {{ $recentActivities->currentPage() }} of {{ $recentActivities->lastPage() }}
                </div>
                <div>
                    {{ $recentActivities->fragment('staff-activities')
                        ->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </section>
</div>


{{-- =====================================
     ADD / EDIT STAFF POPUP
===================================== --}}

<dialog id="smStaffDialog"
        class="sm-dialog"
        aria-labelledby="smDialogTitle">

    <div class="sm-dialog-header">

        <div>
            <p class="sm-eyebrow mb-1">STAFF ACCOUNT</p>
            <h5 id="smDialogTitle" class="mb-0">
                Add Staff
            </h5>
        </div>

        <button type="button"
                class="btn-close"
                data-sm-close="smStaffDialog"
                aria-label="Close">
        </button>

    </div>

    <form id="smStaffForm"
          method="POST"
          enctype="multipart/form-data"
          action="{{ route('staff.store') }}"
          data-store-url="{{ route('staff.store') }}">

        @csrf

        <input type="hidden"
               id="smMethod"
               name="_method"
               value="PUT"
               disabled>

        <input type="hidden"
               id="smFormKind"
               name="_form_kind"
               value="staff_create">

        <input type="hidden"
               id="smRecordId"
               name="_record_id">

        <div class="sm-dialog-body">

            <div class="row g-3">

                {{-- NAME --}}
                <div class="col-md-6">
                    <label class="form-label" for="smName">
                        Full Name *
                    </label>

                    <input id="smName"
                           name="name"
                           class="form-control"
                           maxlength="255"
                           required>
                </div>

                {{-- EMAIL --}}
                <div class="col-md-6">
                    <label class="form-label" for="smEmail">
                        Email *
                    </label>

                    <input id="smEmail"
                           name="email"
                           type="email"
                           class="form-control"
                           maxlength="255"
                           required>
                </div>

                {{-- PASSWORD --}}
                <div class="col-md-6">
                    <label class="form-label" for="smPassword">
                        Password
                        <span id="smPasswordRequired">*</span>
                    </label>

                    <input id="smPassword"
                           name="password"
                           type="password"
                           class="form-control"
                           minlength="8"
                           autocomplete="new-password">

                    <small id="smPasswordHint" class="text-muted">
                        At least 8 characters.
                    </small>
                </div>

                {{-- CONFIRM PASSWORD --}}
                <div class="col-md-6">
                    <label class="form-label"
                           for="smPasswordConfirmation">
                        Confirm Password
                        <span id="smConfirmRequired">*</span>
                    </label>

                    <input id="smPasswordConfirmation"
                           name="password_confirmation"
                           type="password"
                           class="form-control"
                           minlength="8"
                           autocomplete="new-password">
                    <small class="text-muted" id="smConfirmHint">
                        Enter the same password in both fields.
                    </small>
                </div>

                {{-- PROFILE PICTURE --}}
                <div class="col-12">

                    <label class="form-label" for="smPhoto">
                        Profile Picture
                    </label>

                    <div class="sm-photo-row">

                        <img id="smPhotoPreview"
                             class="sm-photo-preview"
                             alt="Staff preview"
                             hidden>

                        <div class="flex-grow-1">

                            <input id="smPhoto"
                                   name="photo"
                                   type="file"
                                   class="form-control"
                                   accept="image/jpeg,image/png,image/webp">

                            <small class="text-muted d-block mt-1">
                                JPG, PNG or WEBP, max 2MB.
                            </small>

                        </div>

                    </div>
                </div>

                <div class="col-12">
                    <div class="sm-note">
                        <i class="bi bi-shield-check me-1"></i>
                        New accounts always have the Staff role.
                    </div>
                </div>

            </div>
        </div>

        {{-- FOOTER --}}
        <div class="sm-dialog-footer">

            <button id="smDeleteButton"
                    type="button"
                    class="btn btn-outline-danger me-auto"
                    hidden>
                <i class="bi bi-trash3 me-1"></i>
                Delete Staff
            </button>

            <button type="button"
                    class="btn btn-outline-secondary"
                    data-sm-close="smStaffDialog">
                Cancel
            </button>

            <button type="submit" class="btn btn-success">
                <i class="bi bi-check2-circle me-1"></i>
                Save Staff
            </button>

        </div>

    </form>
</dialog>


{{-- =====================================
     DELETE STAFF CONFIRMATION
===================================== --}}

<dialog id="smDeleteDialog"
        class="sm-dialog sm-delete-dialog"
        aria-labelledby="smDeleteTitle">

    <div class="sm-dialog-header">

        <h5 id="smDeleteTitle" class="text-danger mb-0">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Delete Staff?
        </h5>

        <button type="button"
                class="btn-close"
                data-sm-close="smDeleteDialog"
                aria-label="Close">
        </button>

    </div>

    <form id="smDeleteForm" method="POST">

        @csrf
        @method('DELETE')

        <div class="sm-dialog-body">

            <p>Are you sure you want to permanently delete:</p>

            <strong id="smDeleteName" class="text-danger"></strong>

            <p class="text-muted small mt-3 mb-0">
                Only staff without booking history can be deleted.
                This action cannot be undone.
            </p>

        </div>

        <div class="sm-dialog-footer">

            <button type="button"
                    class="btn btn-outline-secondary"
                    data-sm-close="smDeleteDialog">
                Cancel
            </button>

            <button type="submit" class="btn btn-danger">
                <i class="bi bi-trash3 me-1"></i>
                Yes, Delete
            </button>

        </div>
    </form>
</dialog>

<script src="{{ asset('js/views/staff/index.js') }}?v=1" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const password = document.getElementById('smPassword');
    const confirm = document.getElementById('smPasswordConfirmation');
    if (!password || !confirm) return;

    function checkPasswords() {
        confirm.setCustomValidity(
            confirm.value && confirm.value !== password.value
                ? 'Passwords do not match.'
                : ''
        );
    }
    password.addEventListener('input', checkPasswords);
    confirm.addEventListener('input', checkPasswords);
});
</script>

@endsection
