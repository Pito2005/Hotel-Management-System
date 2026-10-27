<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SHOW LOGIN PAGE
    |--------------------------------------------------------------------------
    */

    public function showLogin()
    {
        return view('auth.login');
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW REGISTER PAGE
    |--------------------------------------------------------------------------
    */

    public function showRegister()
    {
        return view('auth.register');
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    | Admin and Staff use the same login form.
    | Record only one activity for each successful login.
    |--------------------------------------------------------------------------
    */

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(
            $credentials,
            $request->boolean('remember')
        )) {

            // Prevent session fixation
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user instanceof User) {

                // Staff and Admin have different log actions.
                $action = $user->role === 'staff'
                    ? 'staff.login'
                    : 'login';

                $this->recordAuthActivity(
                    $action,
                    $user,
                    $user->name . ' logged into PITO HOTEL.',
                    $user
                );
            }

            return redirect()->intended('/dashboard');
        }

        // Invalid credentials
        return back()
            ->withErrors([
                'email' => 'Invalid email or password.',
            ])
            ->onlyInput('email');
    }

    /*
    |--------------------------------------------------------------------------
    | REGISTER
    |--------------------------------------------------------------------------
    | Public registration creates a Staff account only.
    | Admin accounts cannot be created from this form.
    |--------------------------------------------------------------------------
    */

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
        ]);

        // Register as Staff
        $staff = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'staff',
        ]);

        // Record registration activity
        $this->recordAuthActivity(
            'register',
            $staff,
            'New staff account registered: ' . $staff->name,
            $staff,
            [
                'source' => 'public_registration',
                'role' => 'staff',
            ]
        );

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Registration successful! Please log in.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    | Record activity before destroying the session.
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user instanceof User) {

            $action = $user->role === 'staff'
                ? 'staff.logout'
                : 'logout';

            $this->recordAuthActivity(
                $action,
                $user,
                $user->name . ' logged out of PITO HOTEL.',
                $user
            );
        }

        // Logout
        Auth::logout();

        // Clear session securely
        $request->session()->invalidate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG HELPER
    |--------------------------------------------------------------------------
    | Save login, logout and registration activities.
    |
    | If logging fails, login/logout must still work.
    | Never store passwords in activity logs.
    |--------------------------------------------------------------------------
    */

    private function recordAuthActivity(
        string $action,
        ?User $actor,
        string $description,
        ?User $subject = null,
        array $details = []
    ): void {

        try {

            ActivityLog::record(
                $action,
                $actor,
                $description,
                $subject,
                $details
            );

        } catch (\Throwable $exception) {

            Log::warning(
                'PITO HOTEL activity log could not be saved.',
                [
                    'action' => $action,
                    'error' => $exception->getMessage(),
                ]
            );
        }
    }
}
