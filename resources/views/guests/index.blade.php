
@extends('layouts.app')

@section('title', 'Guests - Pito Hotel')
@section('heading', 'Guest Management')

@section('content')

{{-- ========================================= --}}
{{-- ADD NEW GUEST --}}
{{-- ========================================= --}}

<div class="card p-4 mb-4">

    {{-- HEADER --}}
    <div class="d-flex align-items-center gap-3 mb-4">

        <div class="d-flex align-items-center justify-content-center"
             style="width:55px;height:55px;
                    background:#e0f3e7;border-radius:14px;">

            <i class="bi bi-person-plus-fill text-success"
               style="font-size:27px;"></i>

        </div>

        <div>
            <h4 class="text-success fw-bold mb-1">
                Add New Guest
            </h4>

            <small class="text-muted">
                Register a new guest at Pito Hotel.
            </small>
        </div>

    </div>

    {{-- ADD GUEST FORM --}}
    <form method="POST" action="{{ route('guests.store') }}">

        @csrf

        <div class="row g-3">

            {{-- FULL NAME --}}
            <div class="col-md-6">

                <label class="form-label fw-semibold">
                    <i class="bi bi-person text-success me-1"></i>
                    Full Name *
                </label>

                <div class="input-group">

                    <span class="input-group-text">
                        <i class="bi bi-person-fill text-success"></i>
                    </span>

                    <input type="text"
                           name="full_name"
                           value="{{ old('full_name') }}"
                           class="form-control"
                           placeholder="Enter guest full name"
                           required>
                </div>

            </div>

            {{-- PHONE NUMBER --}}
            <div class="col-md-6">

                <label class="form-label fw-semibold">
                    <i class="bi bi-telephone text-success me-1"></i>
                    Phone Number *
                </label>

                <div class="input-group">

                    <span class="input-group-text">
                        <i class="bi bi-telephone-fill text-success"></i>
                    </span>

                    <input type="tel"
                           name="phone"
                           value="{{ old('phone') }}"
                           class="form-control"
                           placeholder="Example: 012345678"
                           required>
                </div>

            </div>

            {{-- EMAIL --}}
            <div class="col-md-6">

                <label class="form-label fw-semibold">
                    <i class="bi bi-envelope text-success me-1"></i>
                    Email
                </label>

                <div class="input-group">

                    <span class="input-group-text">
                        <i class="bi bi-envelope-fill text-success"></i>
                    </span>

                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           class="form-control"
                           placeholder="guest@example.com">
                </div>

            </div>

            {{-- NATIONALITY --}}
            <div class="col-md-6">

                <label class="form-label fw-semibold">
                    <i class="bi bi-globe text-success me-1"></i>
                    Nationality
                </label>

                <div class="input-group">

                    <span class="input-group-text">
                        <i class="bi bi-globe-asia-australia text-success"></i>
                    </span>

                    <input type="text"
                           name="nationality"
                           value="{{ old('nationality') }}"
                           class="form-control"
                           placeholder="Example: Cambodian">
                </div>

            </div>

            {{-- ADDRESS --}}
            <div class="col-12">

                <label class="form-label fw-semibold">
                    <i class="bi bi-geo-alt text-success me-1"></i>
                    Address
                </label>

                <textarea name="address"
                          class="form-control"
                          rows="3"
                          placeholder="Enter guest address...">{{ old('address') }}</textarea>

            </div>

            {{-- SAVE BUTTON --}}
            <div class="col-12">

                <button type="submit" class="btn btn-success">

                    <i class="bi bi-person-check me-1"></i>
                    Add Guest

                </button>

            </div>

        </div>
    </form>

</div>


{{-- ========================================= --}}
{{-- HOTEL GUEST LIST --}}
{{-- ========================================= --}}

<div class="card p-4">

    <div class="d-flex justify-content-between
                align-items-center flex-wrap gap-3 mb-4">

        {{-- LIST TITLE --}}
        <h4 class="text-success fw-bold mb-0">

            <i class="bi bi-people-fill me-2"></i>
            All Hotel Guests

        </h4>

        {{-- SEARCH --}}
        <form method="GET"
              action="{{ route('guests.index') }}"
              class="d-flex gap-2 flex-wrap">

            <div class="input-group" style="width:250px;">

                <span class="input-group-text">
                    <i class="bi bi-search text-success"></i>
                </span>

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="form-control"
                       placeholder="Search guest name...">

            </div>

            <button type="submit" class="btn btn-success">

                <i class="bi bi-search me-1"></i>
                Search

            </button>

            <a href="{{ route('guests.index') }}"
               class="btn btn-outline-secondary">

                <i class="bi bi-arrow-clockwise me-1"></i>
                Clear

            </a>

        </form>
    </div>


    {{-- GUEST TABLE --}}
    <div class="table-responsive">

        <table class="table table-hover align-middle">

            <thead>
                <tr>

                    <th>
                        <i class="bi bi-hash me-1"></i>
                        ID
                    </th>

                    <th>
                        <i class="bi bi-person me-1"></i>
                        Full Name
                    </th>

                    <th>
                        <i class="bi bi-telephone me-1"></i>
                        Phone
                    </th>

                    <th>
                        <i class="bi bi-envelope me-1"></i>
                        Email
                    </th>

                    <th>
                        <i class="bi bi-globe me-1"></i>
                        Nationality
                    </th>

                    <th>
                        <i class="bi bi-gear me-1"></i>
                        Actions
                    </th>

                </tr>
            </thead>

            <tbody>

                @forelse($guests as $guest)

                <tr>

                    {{-- ID --}}
                    <td>
                        <strong class="text-success">
                            #{{ $guest->id }}
                        </strong>
                    </td>

                    {{-- GUEST NAME --}}
                    <td>

                        <div class="d-flex align-items-center gap-2">

                            <i class="bi bi-person-circle text-success"
                               style="font-size:23px;"></i>

                            <strong>
                                {{ $guest->full_name }}
                            </strong>

                        </div>

                    </td>

                    {{-- PHONE --}}
                    <td>
                        <i class="bi bi-telephone text-success me-1"></i>

                        {{ $guest->phone }}
                    </td>

                    {{-- EMAIL --}}
                    <td>
                        <i class="bi bi-envelope text-success me-1"></i>

                        {{ $guest->email ?? '-' }}
                    </td>

                    {{-- NATIONALITY --}}
                    <td>
                        <i class="bi bi-globe text-success me-1"></i>

                        {{ $guest->nationality ?? '-' }}
                    </td>

                    {{-- ACTIONS --}}
                    <td>

                        {{-- EDIT GUEST --}}
                        <details class="mb-2">

                            <summary class="text-success fw-semibold"
                                     style="cursor:pointer;">

                                <i class="bi bi-pencil-square me-1"></i>
                                Edit Guest

                            </summary>

                            <form method="POST"
                                  action="{{ route('guests.update', $guest) }}"
                                  class="mt-3"
                                  style="min-width:220px;">

                                @csrf
                                @method('PUT')

                                {{-- NAME --}}
                                <label class="form-label small">
                                    <i class="bi bi-person text-success"></i>
                                    Full Name
                                </label>

                                <input type="text"
                                       name="full_name"
                                       value="{{ $guest->full_name }}"
                                       class="form-control mb-2"
                                       required>

                                {{-- PHONE --}}
                                <label class="form-label small">
                                    <i class="bi bi-telephone text-success"></i>
                                    Phone Number
                                </label>

                                <input type="tel"
                                       name="phone"
                                       value="{{ $guest->phone }}"
                                       class="form-control mb-2"
                                       required>

                                {{-- EMAIL --}}
                                <label class="form-label small">
                                    <i class="bi bi-envelope text-success"></i>
                                    Email
                                </label>

                                <input type="email"
                                       name="email"
                                       value="{{ $guest->email }}"
                                       class="form-control mb-2">

                                {{-- NATIONALITY --}}
                                <label class="form-label small">
                                    <i class="bi bi-globe text-success"></i>
                                    Nationality
                                </label>

                                <input type="text"
                                       name="nationality"
                                       value="{{ $guest->nationality }}"
                                       class="form-control mb-2">

                                {{-- ADDRESS --}}
                                <label class="form-label small">
                                    <i class="bi bi-geo-alt text-success"></i>
                                    Address
                                </label>

                                <textarea name="address"
                                          class="form-control mb-2"
                                          rows="2">{{ $guest->address }}</textarea>

                                {{-- SAVE CHANGES --}}
                                <button type="submit"
                                        class="btn btn-success btn-sm">

                                    <i class="bi bi-floppy me-1"></i>
                                    Save Changes

                                </button>

                            </form>

                        </details>


                        {{-- DELETE GUEST --}}
                        @can('admin')

                        <form method="POST"
                              action="{{ route('guests.destroy', $guest) }}"
                              onsubmit="return confirm('Delete this guest?')">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="btn btn-outline-danger btn-sm">

                                <i class="bi bi-trash3 me-1"></i>
                                Delete

                            </button>

                        </form>

                        @endcan

                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="6" class="text-center py-5">

                        <i class="bi bi-people text-secondary"
                           style="font-size:45px;"></i>

                        <h5 class="text-muted mt-3">
                            No Guests Found
                        </h5>

                        <p class="text-muted">
                            There are no guests to display.
                        </p>

                    </td>
                </tr>

                @endforelse

            </tbody>
        </table>

    </div>

    {{-- PAGINATION --}}
    <div class="mt-3">
        {{ $guests->links('pagination::bootstrap-5') }}
    </div>

</div>

@endsection
