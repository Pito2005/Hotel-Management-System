
@extends('layouts.auth')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/views/auth/login.css') }}">
@endpush

@section('title', 'Login')

{{-- ============================================
     PITO HOTEL - MODERN LOGIN PAGE
     Laravel 13 | Bootstrap 5 | Green Theme
============================================ --}}

@push('styles')

@endpush


@section('content')

{{-- =====================================
     WELCOME HEADER
===================================== --}}

<div class="login-welcome">

    <h1 class="login-heading">
        Welcome Back!
    </h1>

    <p class="login-subtitle">
        Sign in to your account to manage
        your hotel with confidence.
    </p>

</div>


{{-- =====================================
     SUCCESS MESSAGE
===================================== --}}

@if(session('status'))

    <div class="alert alert-success">

        <i class="bi bi-check-circle me-2"></i>

        {{ session('status') }}

    </div>

@endif


{{-- =====================================
     LOGIN FORM
===================================== --}}

<form method="POST"
      action="{{ route('login.post') }}">

    @csrf

    {{-- =====================================
         EMAIL ADDRESS
    ===================================== --}}

    <div class="mb-4">

        <label for="email" class="login-label">
            Email Address
        </label>

        <div class="login-input-wrap">

            <i class="bi bi-envelope field-icon"
               aria-hidden="true"></i>

            <input
                type="email"
                id="email"
                name="email"
                class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email') }}"
                placeholder="Enter your email address"
                autocomplete="username"
                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                required
                autofocus
            >

        </div>

        {{-- EMAIL VALIDATION --}}
        @error('email')

            <div class="text-danger small mt-2">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ $message }}
            </div>

        @enderror

    </div>


    {{-- =====================================
         PASSWORD
    ===================================== --}}

    <div class="mb-3">

        <label for="password" class="login-label">
            Password
        </label>

        <div class="login-input-wrap">

            <i class="bi bi-lock field-icon"
               aria-hidden="true"></i>

            <input
                type="password"
                id="password"
                name="password"
                class="form-control password-field @error('password') is-invalid @enderror"
                placeholder="Enter your password"
                autocomplete="current-password"
                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                required
            >

            {{-- SHOW / HIDE PASSWORD --}}
            <button
                type="button"
                id="togglePassword"
                class="toggle-password"
                aria-label="Show password"
                aria-controls="password"
                aria-pressed="false"
            >

                <i id="passwordIcon"
                   class="bi bi-eye"
                   aria-hidden="true"></i>

            </button>

        </div>

        {{-- PASSWORD VALIDATION --}}
        @error('password')

            <div class="text-danger small mt-2">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ $message }}
            </div>

        @enderror

    </div>


    {{-- =====================================
         REMEMBER ME + FORGOT PASSWORD
    ===================================== --}}

    <div class="d-flex justify-content-between
                align-items-center gap-2 mb-4">

        {{-- REMEMBER ME --}}
        <div class="form-check login-remember">

            <input
                type="checkbox"
                id="remember"
                name="remember"
                value="1"
                class="form-check-input"
                @checked(old('remember'))
            >

            <label for="remember"
                   class="form-check-label">

                Remember Me

            </label>

        </div>


        {{-- FORGOT PASSWORD --}}
        @if(Route::has('password.request'))

            <a href="{{ route('password.request') }}"
               class="forgot-link">

                Forgot Password?

            </a>

        @endif

    </div>


    {{-- =====================================
         LOGIN BUTTON
    ===================================== --}}

    <button type="submit" class="login-button">

        <i class="bi bi-box-arrow-in-right me-2"
           aria-hidden="true"></i>

        Sign In

        <i class="bi bi-arrow-right ms-2"
           aria-hidden="true"></i>

    </button>


    {{-- =====================================
         REGISTER LINK
    ===================================== --}}

    @if(Route::has('register'))

        <div class="register-divider">
            New here?
        </div>

        <p class="register-message">

            Don't have an account?

            <a href="{{ route('register') }}">
                Create Account
            </a>

        </p>

    @endif

</form>


{{-- =====================================
     SECURITY FOOTER
===================================== --}}

<div class="login-security">

    <i class="bi bi-shield-lock me-1"></i>

    Secure Access to Hotel Management System

</div>

@endsection


{{-- =====================================
     SHOW / HIDE PASSWORD JAVASCRIPT
===================================== --}}

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Get password field elements
    const passwordInput =
        document.getElementById('password');

    const toggleButton =
        document.getElementById('togglePassword');

    const passwordIcon =
        document.getElementById('passwordIcon');

    // Make sure elements exist
    if (!passwordInput || !toggleButton || !passwordIcon) {
        return;
    }

    // Toggle password visibility
    toggleButton.addEventListener('click', function () {

        const isHidden =
            passwordInput.type === 'password';

        // Change input type
        passwordInput.type =
            isHidden ? 'text' : 'password';

        // Change eye icon
        passwordIcon.className =
            isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';

        // Accessibility
        toggleButton.setAttribute(
            'aria-label',
            isHidden ? 'Hide password' : 'Show password'
        );

        toggleButton.setAttribute(
            'aria-pressed',
            String(isHidden)
        );

    });

});
</script>

@endpush
