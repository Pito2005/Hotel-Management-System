
@php
    // Shared hotel branding
    $hotel = $hotelSettings ?? null;

    // Authenticated user
    $currentUser = auth()->user();

    // Hotel name
    $hotelName = $hotel?->hotel_name ?? 'PITO HOTEL';

    // Page icons
    $pageIcon = match (true) {
        request()->routeIs('dashboard') => 'bi-speedometer2',
        request()->routeIs('room-types.*') => 'bi-grid-3x3-gap',
        request()->routeIs('rooms.*') => 'bi-door-open',
        request()->routeIs('guests.*') => 'bi-people',
        request()->routeIs('bookings.*') => 'bi-calendar-check',
        request()->routeIs('front-desk.*') => 'bi-clipboard2-check',
        request()->routeIs('staff.*') => 'bi-person-badge',
        request()->routeIs('reports.*') => 'bi-bar-chart-line',
        request()->routeIs('settings.*') => 'bi-gear-fill',
        default => 'bi-house-door',
    };
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>@yield('title', $hotelName)</title>

    {{-- BOOTSTRAP CSS --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    {{-- BOOTSTRAP ICONS --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    {{-- EXISTING LAYOUT CSS --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/views/layouts/app.css') }}"
    >

    @stack('styles')
</head>

<body>

    {{-- LEFT SIDEBAR --}}
    <aside class="sidebar">

        {{-- HOTEL BRANDING --}}
        <div class="hotel-title">

            @if ($hotel?->logo)

                <img
                    src="{{ asset('storage/' . $hotel->logo) }}"
                    alt="{{ $hotelName }} Logo"
                >

            @else

                <i class="bi bi-buildings-fill"></i>

            @endif

            <span class="hotel-name-text">
                {{ $hotelName }}
            </span>

        </div>

        {{-- NAVIGATION --}}
        <nav class="sidebar-menu">

            {{-- DASHBOARD --}}
            <a
                href="{{ route('dashboard') }}"
                class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
            >

                <i class="bi bi-speedometer2"></i>

                <span>Dashboard</span>

            </a>

            {{-- ROOM MANAGEMENT --}}
            <a
                href="{{ route('rooms.index') }}"
                class="{{ request()->routeIs('rooms.*', 'room-types.*') ? 'active' : '' }}"
            >

                <i class="bi bi-door-open"></i>

                <span>Room Management</span>

            </a>

            {{-- RESERVATION MANAGEMENT --}}
            <a
                href="{{ route('bookings.index') }}"
                class="{{ request()->routeIs('bookings.*', 'guests.*') ? 'active' : '' }}"
            >

                <i class="bi bi-calendar-check"></i>

                <span>Reservation Management</span>

            </a>

            {{-- FRONT DESK - ADMIN & STAFF --}}
            <a
                href="{{ route('front-desk.index') }}"
                class="{{ request()->routeIs('front-desk.*') ? 'active' : '' }}"
            >

                <i class="bi bi-clipboard2-check"></i>

                <span>Front Desk / Check-in</span>

            </a>

            {{-- ADMIN / OWNER ONLY --}}
            @can('admin')

                {{-- STAFF MANAGEMENT --}}
                @if (Route::has('staff.index'))

                    <a
                        href="{{ route('staff.index') }}"
                        class="{{ request()->routeIs('staff.*') ? 'active' : '' }}"
                    >

                        <i class="bi bi-person-badge"></i>

                        <span>Staff Management</span>

                    </a>

                @endif

                {{-- REPORTS --}}
                @if (Route::has('reports.index'))

                    <a
                        href="{{ route('reports.index') }}"
                        class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"
                    >

                        <i class="bi bi-bar-chart-line"></i>

                        <span>Reports</span>

                    </a>

                @endif

                {{-- SETTINGS --}}
                @if (Route::has('settings.index'))

                    <a
                        href="{{ route('settings.index') }}"
                        class="{{ request()->routeIs('settings.*') ? 'active' : '' }}"
                    >

                        <i class="bi bi-gear-fill"></i>

                        <span>Settings</span>

                    </a>

                @endif

            @endcan

        </nav>

        {{-- USER PROFILE + LOGOUT --}}
        <div class="sidebar-bottom">

            <div class="user-profile">

                <div class="user-avatar">

                    @if ($currentUser?->photo)

                        <img
                            src="{{ asset('storage/' . $currentUser->photo) }}"
                            alt="{{ $currentUser->name }} Profile"
                        >

                    @else

                        <i class="bi bi-person-fill"></i>

                    @endif

                </div>

                <div>

                    <div class="user-name">
                        {{ $currentUser?->name ?? 'User' }}
                    </div>

                    <div class="user-role">
                        {{ ucfirst($currentUser?->role ?? 'staff') }}
                    </div>

                </div>

            </div>

            {{-- LOGOUT --}}
            <form
                method="POST"
                action="{{ route('logout') }}"
            >

                @csrf

                <button
                    type="submit"
                    class="btn btn-light w-100"
                >

                    <i class="bi bi-box-arrow-right me-2"></i>

                    Logout

                </button>

            </form>

        </div>

    </aside>

    {{-- MAIN CONTENT --}}
    <main class="main">

        <h2 class="page-heading text-success fw-bold mb-4">

            <span class="heading-icon">

                <i
                    class="bi {{ $pageIcon }}"
                    aria-hidden="true"
                ></i>

            </span>

            @yield('heading', $hotelName)

        </h2>

        {{-- VALIDATION ERRORS --}}
        @if ($errors->any())

            <div class="alert alert-danger">

                <ul class="mb-0">

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif

        {{-- PAGE CONTENT --}}
        @yield('content')

    </main>

    @stack('scripts')

</body>

</html>
