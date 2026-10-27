@extends('layouts.app')

@section('title', 'Room Management - PITO HOTEL')
@section('heading', 'Room Management')

@section('content')

    <link rel="stylesheet" href="{{ asset('css/views/rooms/index.css') }}?v=6">

    <div class="rm-page">


        {{-- TOP ACTION BAR --}}
        <div class="rm-toolbar">
            <div>
                <p class="rm-eyebrow">HOTEL OPERATIONS</p>

                <p class="text-muted mb-0">
                    Manage rooms, room categories and prices in one place.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">

                {{-- ADMIN ONLY: ADD ROOM TYPE --}}
                @can('admin')
                    <button type="button" class="btn rm-btn-secondary" data-open-type>

                        <i class="bi bi-grid-3x3-gap"></i>
                        Add Room Type
                    </button>
                @endcan

                {{-- ADMIN & STAFF: ADD ROOM --}}
                <button type="button" class="btn btn-success rm-btn-primary" data-open-room @disabled($roomTypes->isEmpty())>

                    <i class="bi bi-plus-lg"></i>
                    Add Room
                </button>

            </div>
        </div>


        {{-- MESSAGES --}}
        @if (session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Please correct these errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>

            <div id="rmOldState" hidden data-kind="{{ old('_form_kind') }}" data-id="{{ old('_record_id') }}"
                data-number="{{ old('room_number') }}" data-room-type="{{ old('room_type_id') }}"
                data-floor="{{ old('floor') }}" data-status="{{ old('status') }}"
                data-description="{{ old('description') }}" data-name="{{ old('name') }}"
                data-base-price="{{ old('base_price') }}">
            </div>
        @endif

        {{-- ======================================
         ROOMS
    ====================================== --}}
        <section class="rm-card" id="rooms">

            <div class="rm-section-header">
                <div>
                    <h5>
                        <i class="bi bi-door-open text-success me-2"></i>
                        All Rooms
                    </h5>
                    <p>View, search and manage hotel rooms.</p>
                </div>
                <span class="rm-count">{{ $rooms->total() }} Rooms</span>
            </div>

            {{-- FILTERS --}}
            <form action="{{ route('rooms.index') }}" method="GET" class="row g-3 mb-4">

                <div class="col-lg-4">
                    <label class="form-label">Search Room</label>
                    <input name="search" type="search" class="form-control" value="{{ request('search') }}"
                        placeholder="Number, type or description">
                </div>

                <div class="col-lg-3">
                    <label class="form-label">Room Type</label>
                    <select name="room_type_id" class="form-select">
                        <option value="">All Room Types</option>
                        @foreach ($roomTypes as $type)
                            <option value="{{ $type->id }}" @selected(request('room_type_id') == $type->id)>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        {{-- Display status (including automatically calculated Booked) --}}
                        @foreach (['available', 'booked', 'occupied', 'maintenance'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 d-flex align-items-end gap-2">
                    <button class="btn btn-success flex-fill" type="submit">
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                    <a class="btn btn-outline-secondary" href="{{ route('rooms.index') }}" title="Reset filters"
                        aria-label="Reset filters">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>

            {{-- ROOMS TABLE --}}
            <div class="table-responsive">
                <table class="table rm-table align-middle">
                    <thead>
                        <tr>
                            <th>Picture</th>
                            <th>Room</th>
                            <th>Room Type</th>
                            <th>Floor</th>
                            <th>Nightly Rate</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($rooms as $room)
                            <tr>
                                <td>
                                    @if ($room->image)
                                        <img class="rm-thumbnail" src="{{ asset('storage/' . $room->image) }}"
                                            alt="Room {{ $room->room_number }}">
                                    @else
                                        <div class="rm-no-image">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <strong>#{{ $room->room_number }}</strong>
                                </td>

                                <td>
                                    {{ $room->roomType?->name ?? 'Unknown' }}
                                </td>

                                <td>{{ $room->floor ?? '-' }}</td>

                                <td class="fw-bold text-success">
                                    ${{ number_format($room->price_per_night, 2) }}
                                </td>

                                {{-- Auto display status based on today's reservations.
                                 Keep the original rooms.status for editing. --}}
                                <td>
                                    @php
                                        $displayStatus = $room->display_status ?? $room->status;
                                    @endphp

                                    <span class="rm-status rm-status-{{ $displayStatus }}">
                                        {{ ucfirst($displayStatus) }}
                                    </span>

                                    @if ((bool) ($room->has_upcoming_booking ?? false))
                                        <small class="d-block text-muted mt-1">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            Upcoming reservation
                                        </small>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <button type="button" class="rm-manage-button" data-room-manage
                                            data-id="{{ $room->id }}" data-update-url="{{ route('rooms.update', $room) }}"
                                            data-delete-url="{{ route('rooms.destroy', $room) }}"
                                            data-number="{{ $room->room_number }}" data-type="{{ $room->room_type_id }}"
                                            data-floor="{{ $room->floor }}" data-status="{{ $room->status }}"
                                            data-description="{{ $room->description }}"
                                            data-image="{{ $room->image ? asset('storage/' . $room->image) : '' }}">
                                            <i class="bi bi-sliders2"></i>
                                            Manage
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7"
                                    class="text-center py-5 text-muted">
                                    <i class="bi bi-door-closed fs-2 d-block mb-2"></i>
                                    No rooms found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <span class="text-muted small">
                    Showing {{ $rooms->firstItem() ?? 0 }} to {{ $rooms->lastItem() ?? 0 }}
                    of {{ $rooms->total() }} Rooms
                    · Page {{ $rooms->currentPage() }} of {{ $rooms->lastPage() }}
                </span>
                {{ $rooms->fragment('rooms')->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </section>

        {{-- ======================================
         ROOM TYPES ON SAME PAGE
    ====================================== --}}
        <section class="rm-card" id="room-types">

            <div class="rm-section-header">
                <div>
                    <h5>
                        <i class="bi bi-grid-3x3-gap text-success me-2"></i>
                        Room Types & Pricing
                    </h5>
                    <p>Set the base nightly rate for each room category.</p>
                </div>
                <span class="rm-count">
                    {{ $roomTypeRows->total() }} Types
                </span>
            </div>

            <form action="{{ route('rooms.index') }}#room-types" method="GET" class="d-flex gap-2 mb-4">
                <input name="type_search" class="form-control" value="{{ request('type_search') }}"
                    placeholder="Search room types...">

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('rooms.index') }}#room-types" class="btn btn-outline-secondary">
                    Clear
                </a>
            </form>

            <div class="table-responsive">
                <table class="table rm-table align-middle">
                    <thead>
                        <tr>
                            <th>Room Type</th>
                            <th>Base Price</th>
                            <th>Rooms Using</th>
                            <th>Description</th>
                            @can('admin')
                                <th class="text-end">Action</th>
                            @endcan
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($roomTypeRows as $type)
                            <tr>
                                <td>
                                    <strong>{{ $type->name }}</strong>
                                </td>

                                <td class="fw-bold text-success">
                                    ${{ number_format($type->base_price, 2) }}
                                </td>

                                <td>
                                    <span class="rm-count">
                                        {{ $type->rooms_count }}
                                    </span>
                                </td>

                                <td>{{ $type->description ?? '-' }}</td>

                                @can('admin')
                                    <td class="text-end">
                                        <button type="button" class="rm-manage-button" data-type-manage
                                            data-id="{{ $type->id }}"
                                            data-update-url="{{ route('room-types.update', $type) }}"
                                            data-delete-url="{{ route('room-types.destroy', $type) }}"
                                            data-name="{{ $type->name }}" data-price="{{ $type->base_price }}"
                                            data-description="{{ $type->description }}">
                                            <i class="bi bi-sliders2"></i>
                                            Manage
                                        </button>
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->can('admin') ? 5 : 4 }}" class="text-center py-4 text-muted">
                                    No room types found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <span class="text-muted small">
                    Showing {{ $roomTypeRows->firstItem() ?? 0 }} to {{ $roomTypeRows->lastItem() ?? 0 }}
                    of {{ $roomTypeRows->total() }} Room Types
                    · Page {{ $roomTypeRows->currentPage() }} of {{ $roomTypeRows->lastPage() }}
                </span>
                {{ $roomTypeRows->fragment('room-types')->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </section>
    </div>


    @auth

        {{-- ======================================
     ROOM ADD / MANAGE POPUP
====================================== --}}
        <dialog id="roomDialog" class="rm-dialog" aria-labelledby="roomDialogTitle">

            <div class="rm-dialog-header">
                <div>
                    <p class="rm-eyebrow mb-1">ROOM DETAILS</p>
                    <h5 id="roomDialogTitle" class="mb-0">Add Room</h5>
                </div>

                <button type="button" class="btn-close" data-close-dialog="roomDialog" aria-label="Close"></button>
            </div>

            <form id="roomForm" method="POST" enctype="multipart/form-data" action="{{ route('rooms.store') }}"
                data-store-url="{{ route('rooms.store') }}"
                data-update-template="{{ route('rooms.update', ['room' => '__ROOM_ID__']) }}"
                data-delete-template="{{ route('rooms.destroy', ['room' => '__ROOM_ID__']) }}">
                @csrf

                <input type="hidden" name="_method" id="roomMethod" value="PUT" disabled>
                <input type="hidden" name="_form_kind" id="roomKind" value="room_create">
                <input type="hidden" name="_record_id" id="roomRecordId">

                <div class="rm-dialog-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Room Number *</label>
                            <input id="roomNumber" name="room_number" class="form-control" maxlength="30" placeholder="101"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Room Type *</label>
                            <select id="roomTypeId" name="room_type_id" class="form-select" required>
                                <option value="">Select Room Type</option>
                                @foreach ($roomTypes as $type)
                                    <option value="{{ $type->id }}" data-price="{{ $type->base_price }}">
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Floor</label>
                            <input id="roomFloor" name="floor" type="number" min="0" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Price Per Night ($)
                                <i class="bi bi-lock-fill text-success"></i>
                            </label>
                            <input id="roomPrice" type="text" class="form-control rm-auto-price"
                                placeholder="Choose Room Type" readonly>
                            <small class="text-muted">
                                Automatically taken from Room Type.
                            </small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status *</label>
                            <select id="roomStatus" name="status" class="form-select" required>
                                <option value="available">Available</option>
                                <option value="occupied">Occupied</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea id="roomDescription" name="description" class="form-control" rows="3" maxlength="2000"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Room Picture</label>
                            <input id="roomPhoto" name="image" type="file" class="form-control"
                                accept="image/jpeg,image/png,image/webp">

                            <small class="text-muted">
                                JPG, PNG or WEBP · Maximum 4 MB
                            </small>

                            <div class="mt-3">
                                <img id="roomPhotoPreview" class="rm-photo-preview" alt="Room photo preview" hidden>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rm-dialog-footer">
                    <button id="roomDeleteButton" type="button" class="btn rm-danger-button me-auto"
                        @cannot('admin') disabled style="display: none !important;" @endcannot hidden>
                        <i class="bi bi-trash3 me-1"></i> Delete Room
                    </button>

                    <button type="button" class="btn btn-outline-secondary" data-close-dialog="roomDialog">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2-circle me-1"></i>
                        Save Room
                    </button>
                </div>
            </form>
        </dialog>


        {{-- ======================================
     ROOM TYPE ADD / MANAGE POPUP
====================================== --}}
        <dialog id="typeDialog" class="rm-dialog rm-type-dialog" aria-labelledby="typeDialogTitle">

            <div class="rm-dialog-header">
                <div>
                    <p class="rm-eyebrow mb-1">CATEGORY & PRICING</p>
                    <h5 id="typeDialogTitle" class="mb-0">Add Room Type</h5>
                </div>

                <button type="button" class="btn-close" data-close-dialog="typeDialog" aria-label="Close"></button>
            </div>

            <form id="typeForm" method="POST" action="{{ route('room-types.store') }}"
                data-store-url="{{ route('room-types.store') }}"
                data-update-template="{{ route('room-types.update', ['roomType' => '__TYPE_ID__']) }}"
                data-delete-template="{{ route('room-types.destroy', ['roomType' => '__TYPE_ID__']) }}">
                @csrf

                <input type="hidden" name="_method" id="typeMethod" value="PUT" disabled>
                <input type="hidden" name="_form_kind" id="typeKind" value="type_create">
                <input type="hidden" name="_record_id" id="typeRecordId">

                <div class="rm-dialog-body">
                    <div class="mb-3">
                        <label class="form-label">Room Type Name *</label>
                        <input id="typeName" name="name" class="form-control" maxlength="255"
                            placeholder="Example: Deluxe" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Base Price ($) *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input id="typeBasePrice" name="base_price" type="number" min="0" max="99999999"
                                step="0.01" class="form-control" required>
                        </div>
                        <small class="text-muted">
                            This is the nightly rate used by rooms in this category.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea id="typeDescription" name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="rm-dialog-footer">
                    <button id="typeDeleteButton" type="button" class="btn rm-danger-button me-auto" hidden>
                        <i class="bi bi-trash3 me-1"></i> Delete Type
                    </button>

                    <button type="button" class="btn btn-outline-secondary" data-close-dialog="typeDialog">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2-circle me-1"></i>
                        Save Type
                    </button>
                </div>
            </form>
        </dialog>


        {{-- ======================================
     DELETE CONFIRMATION POPUP
====================================== --}}
        <dialog id="deleteDialog" class="rm-dialog rm-delete-dialog" aria-labelledby="deleteDialogTitle">

            <div class="rm-dialog-header">
                <h5 id="deleteDialogTitle" class="mb-0">
                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                    Confirm Delete
                </h5>

                <button type="button" class="btn-close" data-close-dialog="deleteDialog" aria-label="Close"></button>
            </div>

            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')

                <div class="rm-dialog-body">
                    <p>Are you sure you want to delete:</p>
                    <h6 id="deleteItemName" class="fw-bold text-danger"></h6>
                    <p class="text-muted small mb-0">
                        Deletion cannot be undone. Records that are in use
                        are protected by the system.
                    </p>
                </div>

                <div class="rm-dialog-footer">
                    <button type="button" class="btn btn-outline-secondary" data-close-dialog="deleteDialog">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash3 me-1"></i>
                        Yes, Delete
                    </button>
                </div>
            </form>
        </dialog>

    @endauth

    <script src="{{ asset('js/views/rooms/index.js') }}" defer></script>

@endsection
