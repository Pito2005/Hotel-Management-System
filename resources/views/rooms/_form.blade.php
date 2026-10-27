
{{-- =========================================
     PITO HOTEL - ROOM FORM
     Used for both Create and Edit
========================================= --}}

@php
    $isEdit = isset($room);
@endphp

<div class="row g-3">

    {{-- =====================================
         ROOM NUMBER
    ===================================== --}}
    <div class="col-md-6">

        <label for="room_number" class="form-label fw-semibold">
            <i class="bi bi-door-open text-success me-1"></i>
            Room Number *
        </label>

        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-door-closed text-success"></i>
            </span>

            <input
                type="text"
                name="room_number"
                id="room_number"
                class="form-control"
                value="{{ old('room_number', $room->room_number ?? '') }}"
                placeholder="Example: 101"
                maxlength="50"
                required
            >
        </div>

    </div>

    {{-- =====================================
         ROOM TYPE
    ===================================== --}}
    <div class="col-md-6">

        <label for="room_type_id" class="form-label fw-semibold">
            <i class="bi bi-grid-3x3-gap text-success me-1"></i>
            Room Type *
        </label>

        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-grid-fill text-success"></i>
            </span>

            <select
                name="room_type_id"
                id="room_type_id"
                class="form-select"
                required
            >
                <option value="">Select Room Type</option>

                @foreach($roomTypes as $type)
                    <option
                        value="{{ $type->id }}"
                        @selected(
                            old(
                                'room_type_id',
                                $room->room_type_id ?? ''
                            ) == $type->id
                        )
                    >
                        {{ $type->name }}
                    </option>
                @endforeach

            </select>
        </div>

    </div>

    {{-- =====================================
         FLOOR NUMBER
    ===================================== --}}
    <div class="col-md-6">

        <label for="floor" class="form-label fw-semibold">
            <i class="bi bi-building text-success me-1"></i>
            Floor Number
        </label>

        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-buildings text-success"></i>
            </span>

            <input
                type="number"
                name="floor"
                id="floor"
                class="form-control"
                value="{{ old('floor', $room->floor ?? '') }}"
                placeholder="Example: 1"
                min="0"
                step="1"
            >
        </div>

    </div>

    {{-- =====================================
         PRICE PER NIGHT
    ===================================== --}}
    <div class="col-md-6">

        <label for="price_per_night" class="form-label fw-semibold">
            <i class="bi bi-cash-stack text-success me-1"></i>
            Price Per Night ($) *
        </label>

        <div class="input-group">
            <span class="input-group-text text-success fw-bold">
                $
            </span>

            <input
                type="number"
                name="price_per_night"
                id="price_per_night"
                class="form-control"
                value="{{ old('price_per_night', $room->price_per_night ?? '') }}"
                placeholder="Example: 60.00"
                min="0"
                step="0.01"
                required
            >
        </div>

    </div>

    {{-- =====================================
         ROOM STATUS
    ===================================== --}}
    <div class="col-md-6">

        <label for="status" class="form-label fw-semibold">
            <i class="bi bi-check-circle text-success me-1"></i>
            Room Status *
        </label>

        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-activity text-success"></i>
            </span>

            <select
                name="status"
                id="status"
                class="form-select"
                required
            >

                @foreach([
                    'available' => 'Available',
                    'occupied' => 'Occupied',
                    'maintenance' => 'Maintenance'
                ] as $value => $label)

                    <option
                        value="{{ $value }}"
                        @selected(
                            old(
                                'status',
                                $room->status ?? 'available'
                            ) === $value
                        )
                    >
                        {{ $label }}
                    </option>

                @endforeach

            </select>
        </div>

    </div>

    {{-- =====================================
         DESCRIPTION
    ===================================== --}}
    <div class="col-12">

        <label for="description" class="form-label fw-semibold">
            <i class="bi bi-card-text text-success me-1"></i>
            Room Description
        </label>

        <textarea
            name="description"
            id="description"
            class="form-control"
            rows="4"
            placeholder="Enter room information, facilities and description..."
        >{{ old('description', $room->description ?? '') }}</textarea>

    </div>

    {{-- =====================================
         CURRENT ROOM PICTURE
    ===================================== --}}
    @if($isEdit && $room->image)

        <div class="col-12">

            <label class="form-label fw-semibold">
                <i class="bi bi-image text-success me-1"></i>
                Current Room Picture
            </label>

            <div>
                <img
                    src="{{ asset('storage/' . $room->image) }}"
                    alt="Room {{ $room->room_number }}"
                    style="
                        width:220px;
                        max-width:100%;
                        height:150px;
                        object-fit:cover;
                        border-radius:12px;
                        border:2px solid #d9f0e1;
                    "
                >
            </div>

        </div>

    @endif

    {{-- =====================================
         UPLOAD ROOM PICTURE
    ===================================== --}}
    <div class="col-12">

        <label for="roomImage" class="form-label fw-semibold">
            <i class="bi bi-camera text-success me-1"></i>
            {{ $isEdit ? 'Change Room Picture' : 'Upload Room Picture' }}
        </label>

        <input
            type="file"
            name="image"
            id="roomImage"
            class="form-control"
            accept="image/jpeg,image/png,image/webp"
        >

        <small class="text-muted d-block mt-2">
            <i class="bi bi-info-circle me-1"></i>
            JPG, PNG or WEBP. Maximum 4 MB.
            @if($isEdit)
                Leave empty to keep the current picture.
            @endif
        </small>

        {{-- NEW IMAGE PREVIEW --}}
        <div class="mt-3">

            <img
                id="newRoomPreview"
                alt="New Room Picture Preview"
                style="
                    display:none;
                    width:220px;
                    max-width:100%;
                    height:150px;
                    object-fit:cover;
                    border-radius:12px;
                    border:2px solid #198754;
                "
            >

        </div>

    </div>

</div>

{{-- IMAGE PREVIEW JAVASCRIPT --}}
<script>
document.getElementById('roomImage').addEventListener(
    'change',
    function(event) {

        const file = event.target.files[0];
        const preview = document.getElementById('newRoomPreview');

        if (!file) {
            preview.style.display = 'none';
            return;
        }

        const imageURL = URL.createObjectURL(file);

        preview.onload = function() {
            URL.revokeObjectURL(imageURL);
        };

        preview.src = imageURL;
        preview.style.display = 'block';
    }
);
</script>
