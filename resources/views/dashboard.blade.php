@extends('layouts.app')

@section('title', 'Dashboard - Pito Hotel')
@section('heading', 'Dashboard')

@section('content')

    {{-- ========================================= --}}
    {{-- WELCOME SECTION --}}
    {{-- ========================================= --}}

    <div class="card p-4 mb-4">

        <div class="d-flex align-items-center gap-3 mb-3">

            {{-- Welcome Icon --}}
            <div class="d-flex align-items-center justify-content-center"
                style="
                width: 55px;
                height: 55px;
                background: #e0f3e7;
                border-radius: 14px;
             ">

                <i class="bi bi-buildings-fill text-success" style="font-size: 27px;"></i>

            </div>

            <div>
                <h3 class="text-success fw-bold mb-1">
                    Welcome, {{ auth()->user()->name }}!
                </h3>

                <small class="text-muted">
                    PITO HOTEL Management System
                </small>
            </div>

        </div>

        <p class="text-muted mb-3">
            <i class="bi bi-info-circle text-success me-2"></i>

            Welcome to Pito Hotel Management System.
            Manage your hotel easily and efficiently.
        </p>

        {{-- Display User Role --}}
        <div>
            <span class="badge bg-success px-3 py-2">

                <i class="bi bi-person-check-fill me-1"></i>

                {{ ucfirst(auth()->user()->role) }}

            </span>
        </div>

    </div>


    {{-- ========================================= --}}
    {{-- HOTEL MANAGEMENT SECTION --}}
    {{-- ========================================= --}}

    <div class="row g-4">

        {{-- ===================================== --}}
        {{-- 1. ROOM TYPES MANAGEMENT --}}
        {{-- ===================================== --}}

        <div class="col-md-6">

            <div class="card p-4 h-100">

                {{-- Card Icon --}}
                <div class="mb-3">

                    <div class="d-flex align-items-center justify-content-center"
                        style="
                        width: 55px;
                        height: 55px;
                        background: #e0f3e7;
                        border-radius: 14px;
                     ">

                        <i class="bi bi-grid-3x3-gap-fill text-success" style="font-size: 25px;"></i>

                    </div>

                </div>

                {{-- Card Title --}}
                <h4 class="text-success mb-3">

                    <i class="bi bi-grid-3x3-gap me-2"></i>

                    Room Types

                </h4>

                {{-- Description --}}
                <p class="text-muted">

                    Manage Standard, Deluxe, VIP,
                    and Suite room categories.

                </p>

                {{-- Manage Room Types Button --}}
                <div class="mt-auto">

                    <a href="{{ route('room-types.index') }}" class="btn btn-success">

                        <i class="bi bi-arrow-right-circle me-2"></i>

                        Manage Room Types

                    </a>

                </div>


                {{-- ================================= --}}
                {{-- STAFF MANAGEMENT CARD --}}
                {{-- ================================= --}}

                @can('admin')
                    <div class="col-md-6">

                        <div class="card p-4 h-100">

                            {{-- CARD ICON --}}
                            <div class="mb-3">

                                <div class="d-flex align-items-center justify-content-center"
                                    style="width:55px;height:55px;
                        background:#e0f3e7;
                        border-radius:14px;">

                                    <i class="bi bi-person-badge-fill text-success" style="font-size:26px;"></i>

                                </div>

                            </div>

                            {{-- TITLE --}}
                            <h4 class="text-success mb-3">

                                <i class="bi bi-people me-2"></i>
                                Staff Management

                            </h4>

                            {{-- DESCRIPTION --}}
                            <p class="text-muted">

                                Manage hotel employees, staff accounts,
                                passwords and profile pictures.

                            </p>

                            {{-- BUTTON --}}
                            <div class="mt-auto">

                                <a href="{{ route('staff.index') }}" class="btn btn-success">

                                    <i class="bi bi-arrow-right-circle me-1"></i>
                                    Manage Staff

                                </a>

                            </div>

                        </div>

                    </div>
                @endcan


            </div>

        </div>


        {{-- ===================================== --}}
        {{-- 2. ROOM MANAGEMENT --}}
        {{-- ===================================== --}}

        <div class="col-md-6">

            <div class="card p-4 h-100">

                {{-- Card Icon --}}
                <div class="mb-3">

                    <div class="d-flex align-items-center justify-content-center"
                        style="
                        width: 55px;
                        height: 55px;
                        background: #e0f3e7;
                        border-radius: 14px;
                     ">

                        <i class="bi bi-door-open-fill text-success" style="font-size: 25px;"></i>

                    </div>

                </div>

                {{-- Card Title --}}
                <h4 class="text-success mb-3">

                    <i class="bi bi-door-open me-2"></i>

                    Room Management

                </h4>

                {{-- Description --}}
                <p class="text-muted">

                    Manage room numbers, room pictures,
                    room prices, floor numbers, and status.

                </p>

                {{-- Manage Rooms Button --}}
                <div class="mt-auto">

                    <a href="{{ route('rooms.index') }}" class="btn btn-success">

                        <i class="bi bi-arrow-right-circle me-2"></i>

                        Manage Rooms

                    </a>

                </div>

            </div>

        </div>

    </div>

@endsection
