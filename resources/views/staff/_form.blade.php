
@php
    $isEdit = isset($staff);
@endphp

<div class="row g-3">

    {{-- ================================= --}}
    {{-- STAFF NAME --}}
    {{-- ================================= --}}

    <div class="col-md-6">

        <label for="name" class="form-label fw-semibold">
            <i class="bi bi-person text-success me-1"></i>
            Staff Full Name *
        </label>

        <div class="input-group">

            <span class="input-group-text">
                <i class="bi bi-person-fill text-success"></i>
            </span>

            <input type="text"
                   name="name"
                   id="name"
                   class="form-control"
                   value="{{ old('name', $staff->name ?? '') }}"
                   placeholder="Enter employee full name"
                   required>

        </div>
    </div>


    {{-- ================================= --}}
    {{-- STAFF EMAIL --}}
    {{-- ================================= --}}

    <div class="col-md-6">

        <label for="email" class="form-label fw-semibold">
            <i class="bi bi-envelope text-success me-1"></i>
            Email Address *
        </label>

        <div class="input-group">

            <span class="input-group-text">
                <i class="bi bi-envelope-fill text-success"></i>
            </span>

            <input type="email"
                   name="email"
                   id="email"
                   class="form-control"
                   value="{{ old('email', $staff->email ?? '') }}"
                   placeholder="staff@example.com"
                   required>

        </div>
    </div>


    {{-- ================================= --}}
    {{-- PASSWORD --}}
    {{-- ================================= --}}

    <div class="col-md-6">

        <label for="password" class="form-label fw-semibold">
            <i class="bi bi-lock text-success me-1"></i>
            Password {{ $isEdit ? '(Optional)' : '*' }}
        </label>

        <div class="input-group">

            <span class="input-group-text">
                <i class="bi bi-key-fill text-success"></i>
            </span>

            <input type="password"
                   name="password"
                   id="password"
                   class="form-control"
                   minlength="8"
                   autocomplete="new-password"
                   placeholder="{{ $isEdit ? 'Leave blank to keep password' : 'Minimum 8 characters' }}"
                   {{ $isEdit ? '' : 'required' }}>

        </div>
    </div>


    {{-- ================================= --}}
    {{-- CONFIRM PASSWORD --}}
    {{-- ================================= --}}

    <div class="col-md-6">

        <label for="password_confirmation"
               class="form-label fw-semibold">

            <i class="bi bi-shield-lock text-success me-1"></i>
            Confirm Password

        </label>

        <div class="input-group">

            <span class="input-group-text">
                <i class="bi bi-check-circle text-success"></i>
            </span>

            <input type="password"
                   name="password_confirmation"
                   id="password_confirmation"
                   class="form-control"
                   autocomplete="new-password"
                   placeholder="Confirm password"
                   {{ $isEdit ? '' : 'required' }}>

        </div>
    </div>


    {{-- ================================= --}}
    {{-- STAFF ROLE --}}
    {{-- ================================= --}}

    <div class="col-12">

        <label class="form-label fw-semibold">
            <i class="bi bi-shield-check text-success me-1"></i>
            Account Role
        </label>

        <input type="text"
               class="form-control"
               value="Staff"
               disabled>

        <small class="text-muted">
            This account will have Staff permissions.
            Only an Admin can manage employees.
        </small>

    </div>


    {{-- ================================= --}}
    {{-- STAFF PROFILE PICTURE --}}
    {{-- ================================= --}}

    <div class="col-12">

        <label for="staffPhoto"
               class="form-label fw-semibold">

            <i class="bi bi-image text-success me-1"></i>
            Staff Profile Picture

        </label>

        <div class="mb-3">

            <img
                id="photoPreview"
                src="{{ $isEdit && $staff->photo ? asset('storage/' . $staff->photo) : '' }}"
                alt="Staff Photo Preview"
                style="
                    width:140px;
                    height:140px;
                    object-fit:cover;
                    border-radius:14px;
                    border:2px solid #198754;
                    display:{{ $isEdit && $staff->photo ? 'block' : 'none' }};
                "
            >

        </div>

        <input type="file"
               name="photo"
               id="staffPhoto"
               class="form-control"
               accept="image/jpeg,image/png,image/webp">

        <small class="text-muted">
            JPG, PNG or WEBP. Maximum 2 MB.
            {{ $isEdit ? 'Leave empty to keep the existing photo.' : '' }}
        </small>

    </div>

</div>


{{-- ================================= --}}
{{-- PREVIEW SELECTED STAFF PHOTO --}}
{{-- ================================= --}}

<script>
document.getElementById('staffPhoto').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('photoPreview');

    if (!file) {
        return;
    }

    const url = URL.createObjectURL(file);

    preview.onload = function () {
        URL.revokeObjectURL(url);
    };

    preview.src = url;
    preview.style.display = 'block';
});
</script>
