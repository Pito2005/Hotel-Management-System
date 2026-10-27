
@php
    // Hotel branding provided by AppServiceProvider
    $hotel = $hotelSettings ?? null;

    $hotelName = $hotel?->hotel_name ?? 'PITO HOTEL';

    // ======================================
    // DYNAMIC LOGIN BACKGROUND
    // ======================================

    // Prefer image uploaded through Admin Settings
    if ($hotel?->login_background) {
        $loginBackgroundUrl = asset(
            'storage/' . $hotel->login_background
        );
    } elseif (is_file(public_path('images/login-hotel.jpg'))) {
        // Optional default image
        $loginBackgroundUrl = asset('images/login-hotel.jpg');
    } else {
        // Fall back to green graphic
        $loginBackgroundUrl = null;
    }
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Login') - {{ $hotelName }}</title>

    {{-- Bootstrap 5 --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="{{ asset('css/views/layouts/auth.css') }}">

    {{-- Additional styles from Login/Register --}}
    @stack('styles')

</head>

<body class="{{ request()->routeIs('login') ? 'login-screen' : '' }}">

    <main class="auth-page">

        <div class="auth-container">

            {{-- =====================================
                 LEFT: HOTEL LOGO + AUTH FORM
            ===================================== --}}

            <section class="auth-left">

                {{-- HOTEL BRANDING --}}
                <header class="hotel-brand">

                    @if($hotel?->logo)

                        <img
                            src="{{ asset('storage/' . $hotel->logo) }}"
                            alt="{{ $hotelName }} Logo"
                            class="brand-logo">

                    @else

                        <div class="brand-logo-fallback">
                            P
                        </div>

                    @endif

                    <div>

                        <div class="brand-name">
                            {{ $hotelName }}
                        </div>

                        <div class="brand-subtitle">
                            HOTEL MANAGEMENT SYSTEM
                        </div>

                    </div>

                </header>

                {{-- LOGIN OR REGISTER FORM --}}
                <div class="auth-form-area">
                    @yield('content')
                </div>

                {{-- COPYRIGHT --}}
                <footer class="auth-footer">

                    <span>
                        <i class="bi bi-shield-lock me-1"></i>
                        Secure Staff & Admin Access
                    </span>

                    <span>
                        &copy; {{ date('Y') }} {{ $hotelName }}
                    </span>

                </footer>

            </section>


            {{-- =====================================
                 RIGHT: DYNAMIC HOTEL PHOTO
            ===================================== --}}

            <aside class="auth-right">

                {{-- Uploaded hotel photo from Admin Settings --}}
                @if($loginBackgroundUrl)

                    <img
                        src="{{ $loginBackgroundUrl }}"
                        class="auth-right-photo"
                        alt="Hotel Interior">

                @else

                    {{-- Default building graphic --}}
                    <div class="hero-art">
                        <i class="bi bi-buildings-fill"></i>
                    </div>

                @endif

                {{-- OVERLAY CONTENT --}}
                <div class="hero-content">

                    <div class="hero-badge">

                        <i class="bi bi-stars"></i>

                        YOUR HOTEL WORKSPACE

                    </div>

                    <h1 class="hero-heading">
                        Hospitality,<br>
                        Made Simple.
                    </h1>

                    <p class="hero-description">
                        Manage reservations, rooms, guests
                        and daily operations in one secure
                        hotel management system.
                    </p>

                    <div class="hero-divider"></div>

                </div>

            </aside>

        </div>

    </main>

    {{-- Additional scripts from Login/Register --}}
    @stack('scripts')

</body>
</html>
