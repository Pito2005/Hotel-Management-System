
@extends('layouts.app')

@section('title', 'Room Types - Pito Hotel')
@section('heading', 'Room Type Management')

@section('content')

{{-- ========================================= --}}
{{-- ADD NEW ROOM TYPE --}}
{{-- ========================================= --}}

@can('admin')
<div class="card p-4 mb-4">

    {{-- Form heading --}}
    <div class="d-flex align-items-center gap-3 mb-4">

        <div class="d-flex align-items-center justify-content-center"
             style="width:52px;height:52px;background:#e0f3e7;
                    border-radius:12px;">

            <i class="bi bi-grid-3x3-gap-fill text-success"
               style="font-size:25px;"></i>
        </div>

        <div>
            <h4 class="text-success fw-bold mb-1">
                Add New Room Type
            </h4>

            <small class="text-muted">
                Create a new hotel room category.
            </small>
        </div>

    </div>

    {{-- Add form --}}
    <form method="POST"
          action="{{ route('room-types.store') }}">

        @csrf

        <div class="row g-3">

            {{-- ROOM TYPE NAME --}}
            <div class="col-md-6">

                <label class="form-label fw-semibold">
                    <i class="bi bi-grid text-success me-1"></i>
                    Room Type Name *
                </label>

                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-door-open text-success"></i>
                    </span>

                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           class="form-control"
                           placeholder="Example: Deluxe Room"
                           required>
                </div>

            </div>

            {{-- BASE PRICE --}}
            <div class="col-md-6">

                <label class="form-label fw-semibold">
                    <i class="bi bi-currency-dollar text-success me-1"></i>
                    Base Price ($) *
                </label>

                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-cash-stack text-success"></i>
                    </span>

                    <input type="number"
                           name="base_price"
                           value="{{ old('base_price') }}"
                           class="form-control"
                           min="0"
                           step="0.01"
                           placeholder="Example: 45.00"
                           required>
                </div>

            </div>

            {{-- DESCRIPTION --}}
            <div class="col-12">

                <label class="form-label fw-semibold">
                    <i class="bi bi-card-text text-success me-1"></i>
                    Description
                </label>

                <textarea name="description"
                          class="form-control"
                          rows="3"
                          placeholder="Enter room type description...">{{ old('description') }}</textarea>

            </div>

            {{-- SAVE BUTTON --}}
            <div class="col-12">

                <button type="submit" class="btn btn-success">

                    <i class="bi bi-plus-circle me-1"></i>
                    Add Room Type

                </button>

            </div>

        </div>
    </form>

</div>
@endcan


{{-- ========================================= --}}
{{-- ROOM TYPE LIST --}}
{{-- ========================================= --}}

<div class="card p-4">

    {{-- List Header --}}
    <div class="d-flex justify-content-between align-items-center
                flex-wrap gap-3 mb-4">

        <h4 class="text-success fw-bold mb-0">

            <i class="bi bi-list-ul me-2"></i>
            All Room Types

        </h4>

        {{-- SEARCH --}}
        <form method="GET"
              action="{{ route('room-types.index') }}"
              class="d-flex gap-2 flex-wrap">

            <div class="input-group" style="width:250px;">

                <span class="input-group-text">
                    <i class="bi bi-search text-success"></i>
                </span>

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="form-control"
                       placeholder="Search room type...">

            </div>

            <button type="submit" class="btn btn-success">

                <i class="bi bi-search me-1"></i>
                Search

            </button>

            <a href="{{ route('room-types.index') }}"
               class="btn btn-outline-secondary">

                <i class="bi bi-arrow-clockwise me-1"></i>
                Clear

            </a>

        </form>
    </div>


    {{-- ROOM TYPES TABLE --}}
    <div class="table-responsive">

        <table class="table table-hover align-middle">

            <thead>
                <tr>

                    <th>
                        <i class="bi bi-hash me-1"></i>
                        ID
                    </th>

                    <th>
                        <i class="bi bi-grid-3x3-gap me-1"></i>
                        Room Type
                    </th>

                    <th>
                        <i class="bi bi-cash-stack me-1"></i>
                        Base Price
                    </th>

                    <th>
                        <i class="bi bi-door-open me-1"></i>
                        Rooms
                    </th>

                    <th>
                        <i class="bi bi-card-text me-1"></i>
                        Description
                    </th>

                    @can('admin')
                    <th>
                        <i class="bi bi-gear me-1"></i>
                        Actions
                    </th>
                    @endcan

                </tr>
            </thead>

            <tbody>

                @forelse($roomTypes as $type)

                <tr>

                    {{-- ID --}}
                    <td>
                        <span class="text-success fw-bold">
                            #{{ $type->id }}
                        </span>
                    </td>

                    {{-- NAME --}}
                    <td>
                        <i class="bi bi-grid text-success me-2"></i>
                        <strong>{{ $type->name }}</strong>
                    </td>

                    {{-- PRICE --}}
                    <td>
                        <span class="fw-bold text-success">
                            ${{ number_format($type->base_price, 2) }}
                        </span>
                    </td>

                    {{-- ROOM COUNT --}}
                    <td>
                        <span class="badge bg-success">
                            <i class="bi bi-door-closed me-1"></i>
                            {{ $type->rooms_count }}
                        </span>
                    </td>

                    {{-- DESCRIPTION --}}
                    <td>
                        <i class="bi bi-file-text text-success me-1"></i>
                        {{ $type->description ?? '-' }}
                    </td>

                    {{-- ACTIONS --}}
                    @can('admin')
                    <td>

                        {{-- EDIT FORM --}}
                        <details class="mb-2">

                            <summary class="text-success fw-semibold"
                                     style="cursor:pointer;">

                                <i class="bi bi-pencil-square me-1"></i>
                                Edit

                            </summary>

                            <form method="POST"
                                  action="{{ route('room-types.update', $type) }}"
                                  class="mt-3"
                                  style="min-width:200px;">

                                @csrf
                                @method('PUT')

                                <label class="form-label small">
                                    <i class="bi bi-grid text-success"></i>
                                    Room Type Name
                                </label>

                                <input type="text"
                                       name="name"
                                       value="{{ $type->name }}"
                                       class="form-control mb-2"
                                       required>

                                <label class="form-label small">
                                    <i class="bi bi-cash text-success"></i>
                                    Base Price
                                </label>

                                <input type="number"
                                       name="base_price"
                                       min="0"
                                       step="0.01"
                                       value="{{ $type->base_price }}"
                                       class="form-control mb-2"
                                       required>

                                <label class="form-label small">
                                    <i class="bi bi-card-text text-success"></i>
                                    Description
                                </label>

                                <textarea name="description"
                                          class="form-control mb-2"
                                          rows="2">{{ $type->description }}</textarea>

                                <button type="submit"
                                        class="btn btn-success btn-sm">

                                    <i class="bi bi-floppy me-1"></i>
                                    Save Changes

                                </button>

                            </form>
                        </details>

                        {{-- DELETE --}}
                        <form method="POST"
                              action="{{ route('room-types.destroy', $type) }}"
                              onsubmit="return confirm('Delete this room type?')">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="btn btn-outline-danger btn-sm">

                                <i class="bi bi-trash3 me-1"></i>
                                Delete

                            </button>

                        </form>

                    </td>
                    @endcan

                </tr>

                @empty

                <tr>
                    <td colspan="6" class="text-center py-5">

                        <i class="bi bi-grid text-secondary"
                           style="font-size:42px;"></i>

                        <h5 class="text-muted mt-3">
                            No Room Types Found
                        </h5>

                        <p class="text-muted">
                            Add a room type to get started.
                        </p>

                    </td>
                </tr>

                @endforelse

            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-3">
        {{ $roomTypes->links('pagination::bootstrap-5') }}
    </div>

</div>

@endsection
