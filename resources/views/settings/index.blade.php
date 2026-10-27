
@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/views/settings/index.css') }}">
@endpush

@section('title', 'Hotel Settings - Pito Hotel')
@section('heading', 'Admin Settings')

@push('styles')

@endpush

@section('content')

{{-- =====================================
     ADMIN PROFILE
===================================== --}}

<div class="setting-card">

    <h4 class="setting-section-title">
        <i class="bi bi-person-circle"></i>
        My Admin Profile
    </h4>

    <p class="setting-description">
        Update your personal account information and profile picture.
    </p>

    <form method="POST"
          action="{{ route('settings.profile.update') }}"
          enctype="multipart/form-data">

        @csrf
        @method('PUT')

        {{-- PROFILE HEADER --}}
        <div class="d-flex align-items-center flex-wrap gap-3 mb-4">

            @if(auth()->user()->photo)
                <img
                    id="profilePreview"
                    src="{{ asset('storage/' . auth()->user()->photo) }}"
                    class="profile-photo"
                    alt="Profile Picture">
            @else
                <img
                    id="profilePreview"
                    class="profile-photo"
                    alt="New Profile Picture"
                    style="display:none;">

                <div id="profileFallback"
                     class="profile-fallback">
                    <i class="bi bi-person-fill"></i>
                </div>
            @endif

            <div>
                <h5 class="fw-bold mb-2">
                    {{ auth()->user()->name }}
                </h5>

                <span class="badge bg-success">
                    <i class="bi bi-shield-check me-1"></i>
                    Admin / Owner
                </span>

                <div class="text-muted small mt-2">
                    {{ auth()->user()->email }}
                </div>
            </div>

        </div>

        <div class="row g-3">

            {{-- NAME --}}
            <div class="col-md-6">

                <label class="setting-label" for="profile_name">
                    <i class="bi bi-person"></i>
                    Full Name *
                </label>

                <input
                    type="text"
                    name="name"
                    id="profile_name"
                    class="form-control"
                    value="{{ old('name', auth()->user()->name) }}"
                    maxlength="255"
                    required>
            </div>

            {{-- EMAIL --}}
            <div class="col-md-6">

                <label class="setting-label" for="profile_email">
                    <i class="bi bi-envelope"></i>
                    Email Address *
                </label>

                <input
                    type="email"
                    name="email"
                    id="profile_email"
                    class="form-control"
                    value="{{ old('email', auth()->user()->email) }}"
                    required>
            </div>

            {{-- PHOTO --}}
            <div class="col-12">

                <label class="setting-label" for="profilePhotoInput">
                    <i class="bi bi-camera"></i>
                    Change Profile Picture
                </label>

                <input
                    type="file"
                    name="photo"
                    id="profilePhotoInput"
                    class="form-control"
                    accept="image/jpeg,image/png,image/webp">

                <p class="setting-hint mb-0">
                    JPG, PNG or WEBP. Maximum 2 MB.
                </p>
            </div>

        </div>

        <hr class="setting-divider">

        {{-- CHANGE PASSWORD --}}
        <h5 class="text-success fw-bold">
            <i class="bi bi-lock me-2"></i>
            Change Password
        </h5>

        <p class="setting-description">
            Leave the password fields empty if you don't want to change it.
        </p>

        <div class="row g-3">

            <div class="col-md-4">

                <label class="setting-label" for="current_password">
                    Current Password
                </label>

                <input
                    type="password"
                    name="current_password"
                    id="current_password"
                    class="form-control"
                    autocomplete="current-password">
            </div>

            <div class="col-md-4">

                <label class="setting-label" for="new_password">
                    New Password
                </label>

                <input
                    type="password"
                    name="password"
                    id="new_password"
                    class="form-control"
                    minlength="8"
                    autocomplete="new-password">
            </div>

            <div class="col-md-4">

                <label class="setting-label" for="confirm_password">
                    Confirm Password
                </label>

                <input
                    type="password"
                    name="password_confirmation"
                    id="confirm_password"
                    class="form-control"
                    autocomplete="new-password">
            </div>

        </div>

        <div class="mt-4">
            <button type="submit"
                    class="btn btn-success setting-save">

                <i class="bi bi-floppy me-2"></i>
                Save My Profile
            </button>
        </div>

    </form>
</div>


{{-- =====================================
     HOTEL BRANDING
===================================== --}}

<div class="setting-card">

    <h4 class="setting-section-title">
        <i class="bi bi-buildings-fill"></i>
        Hotel Branding
    </h4>

    <p class="setting-description">
        Customize hotel name, logo and login page background.
        Changes are shared across the system.
    </p>

    <div class="alert alert-success">
        <i class="bi bi-info-circle me-2"></i>
        Any authorized Admin can update this hotel's branding.
    </div>

    <form method="POST"
          action="{{ route('settings.hotel.update') }}"
          enctype="multipart/form-data">

        @csrf
        @method('PUT')

        {{-- HOTEL NAME --}}
        <div class="mb-4">

            <label class="setting-label" for="hotel_name">
                <i class="bi bi-buildings"></i>
                Hotel Name *
            </label>

            <input
                type="text"
                name="hotel_name"
                id="hotel_name"
                class="form-control form-control-lg"
                maxlength="120"
                value="{{ old('hotel_name', $hotelSettings->hotel_name) }}"
                required>

            <p class="setting-hint">
                This name will appear on the Login Page,
                Sidebar and supported PDF reports.
            </p>
        </div>

        <hr class="setting-divider">

        {{-- =====================================
             HOTEL LOGO
        ===================================== --}}

        <h5 class="text-success fw-bold mb-3">
            <i class="bi bi-image me-2"></i>
            Hotel Logo
        </h5>

        <div class="row g-4 align-items-center">

            <div class="col-md-3">

                @if($hotelSettings->logo)

                    <img
                        id="hotelLogoPreview"
                        src="{{ asset('storage/' . $hotelSettings->logo) }}"
                        class="hotel-logo-preview"
                        alt="Hotel Logo">

                @else

                    <img
                        id="hotelLogoPreview"
                        class="hotel-logo-preview"
                        style="display:none;"
                        alt="New Hotel Logo">

                    <div id="hotelLogoFallback"
                         class="image-fallback"
                         style="width:130px;height:120px;">

                        <i class="bi bi-buildings-fill"></i>
                    </div>

                @endif

            </div>

            <div class="col-md-9">

                <label class="setting-label" for="hotelLogoInput">
                    Upload New Hotel Logo
                </label>

                <input
                    type="file"
                    name="logo"
                    id="hotelLogoInput"
                    class="form-control"
                    accept="image/jpeg,image/png,image/webp">

                <p class="setting-hint">
                    PNG, JPG or WEBP. Maximum 2 MB.
                    Leave empty to keep the current logo.
                </p>

            </div>
        </div>

        <hr class="setting-divider">

        {{-- =====================================
             LOGIN BACKGROUND
        ===================================== --}}

        <h5 class="text-success fw-bold mb-2">
            <i class="bi bi-images me-2"></i>
            Login Background Image
        </h5>

        <p class="setting-description">
            The selected image will appear on the right side
            of the Login and Registration pages.
        </p>

        <div class="row g-4">

            {{-- CURRENT BACKGROUND PREVIEW --}}
            <div class="col-lg-6">

                <label class="setting-label">
                    Current Login Background
                </label>

                @if($hotelSettings->login_background)

                    <img
                        id="loginBackgroundPreview"
                        src="{{ asset('storage/' . $hotelSettings->login_background) }}"
                        class="background-preview"
                        alt="Hotel Login Background">

                @else

                    <img
                        id="loginBackgroundPreview"
                        class="background-preview"
                        style="display:none;"
                        alt="New Hotel Login Background">

                    <div id="backgroundFallback"
                         class="background-placeholder">

                        <i class="bi bi-buildings-fill"></i>
                    </div>

                @endif

                <p class="setting-hint">
                    Recommended: landscape hotel photo,
                    such as a lobby, room or swimming pool.
                </p>

            </div>

            {{-- UPLOAD IMAGE --}}
            <div class="col-lg-6">

                <label class="setting-label" for="loginBackgroundInput">
                    <i class="bi bi-cloud-arrow-up"></i>
                    Upload New Login Background
                </label>

                <input
                    type="file"
                    name="login_background"
                    id="loginBackgroundInput"
                    class="form-control"
                    accept="image/jpeg,image/png,image/webp">

                <p class="setting-hint">
                    JPG, PNG or WEBP. Maximum 5 MB.
                </p>

                <div class="mt-3 p-3 rounded-3"
                     style="background:#f0f8f2;">

                    <h6 class="text-success fw-bold">
                        <i class="bi bi-lightbulb me-1"></i>
                        Recommended Image
                    </h6>

                    <p class="small text-muted mb-0">
                        Use a high-quality hotel photo
                        around 1600 × 1200 pixels or larger.
                        The image will automatically cover
                        the Login Page's right panel.
                    </p>

                </div>

                {{-- REMOVE BACKGROUND --}}
                @if($hotelSettings->login_background)

                    <div class="form-check mt-3">

                        <input
                            type="checkbox"
                            name="remove_login_background"
                            value="1"
                            id="removeBackground"
                            class="form-check-input">

                        <label for="removeBackground"
                               class="form-check-label text-danger">

                            Remove Current Login Background
                        </label>

                    </div>

                @endif

            </div>
        </div>

        <hr class="setting-divider">

        {{-- SAVE HOTEL BRANDING --}}
        <button type="submit"
                class="btn btn-success setting-save">

            <i class="bi bi-check-circle me-2"></i>
            Save Hotel Branding
        </button>

    </form>

    {{-- LAST UPDATED INFORMATION --}}
    @if($hotelSettings->updatedBy)

        <div class="border-top mt-4 pt-3 text-muted small">

            <i class="bi bi-clock-history me-1"></i>

            Last updated by:
            <strong>{{ $hotelSettings->updatedBy->name }}</strong>

            —
            {{ $hotelSettings->updated_at?->format('d M Y H:i') }}

        </div>

    @endif

</div>

@endsection


{{-- =====================================
     CLIENT-SIDE IMAGE PREVIEWS
===================================== --}}

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    function setupImagePreview(inputId, previewId, fallbackId) {

        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const fallback = document.getElementById(fallbackId);

        if (!input || !preview) {
            return;
        }

        input.addEventListener('change', function () {

            const file = input.files[0];

            if (!file) return;
            if (!file.type.startsWith('image/')) return;

            const imageUrl = URL.createObjectURL(file);

            preview.onload = function () {
                URL.revokeObjectURL(imageUrl);
            };

            preview.src = imageUrl;
            preview.style.display = 'block';

            if (fallback) {
                fallback.style.display = 'none';
            }

        });
    }

    // Preview Admin Profile Photo
    setupImagePreview(
        'profilePhotoInput',
        'profilePreview',
        'profileFallback'
    );

    // Preview Hotel Logo
    setupImagePreview(
        'hotelLogoInput',
        'hotelLogoPreview',
        'hotelLogoFallback'
    );

    // Preview Login Background
    setupImagePreview(
        'loginBackgroundInput',
        'loginBackgroundPreview',
        'backgroundFallback'
    );

});
</script>
@endpush
