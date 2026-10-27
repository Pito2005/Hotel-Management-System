@extends('layouts.auth')

@section('title', 'Register')

@section('content')

<h4 class="text-center mb-4">Create Account</h4>

<form action="{{ route('register.post') }}" method="POST">
    @csrf

    {{-- Full name --}}
    <div class="mb-3">
        <label class="form-label">Full Name</label>

        <input name="name"
               class="form-control"
               value="{{ old('name') }}"
               placeholder="Enter full name"
               required>

        @error('name')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    {{-- Email --}}
    <div class="mb-3">
        <label class="form-label">Email Address</label>

        <input type="email"
               name="email"
               class="form-control"
               value="{{ old('email') }}"
               placeholder="Enter email"
               required>

        @error('email')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    {{-- Password --}}
    <div class="mb-3">
        <label class="form-label">Password</label>

        <input type="password"
               name="password"
               class="form-control"
               minlength="8"
               required>

        @error('password')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    {{-- Confirm password --}}
    <div class="mb-3">
        <label class="form-label">Confirm Password</label>

        <input type="password"
               name="password_confirmation"
               class="form-control"
               minlength="8"
               required>
    </div>

    <button type="submit" class="btn btn-green w-100 py-2">
        Create Account
    </button>

    <p class="text-center mt-3 mb-0">
        Already have an account?
        <a href="{{ route('login') }}">Login</a>
    </p>
</form>

@endsection