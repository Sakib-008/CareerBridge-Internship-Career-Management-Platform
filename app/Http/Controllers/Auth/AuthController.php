<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    // ─── Show Login Form ───────────────────────────────────────────────
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->ROLE);
        }
        return view('auth.login');
    }

    // ─── Handle Login ──────────────────────────────────────────────────
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('EMAIL', strtolower($request->email))->first();

        if (!$user || !Hash::check($request->password, $user->PASSWORD_HASH)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid email or password.']);
        }

        if (!$user->isActive()) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Your account has been deactivated. Contact admin.']);
        }

        Auth::login($user, $request->boolean('remember'));

        return $this->redirectByRole($user->ROLE);
    }

    // ─── Show Registration Form ────────────────────────────────────────
    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->ROLE);
        }
        return view('auth.register');
    }

    // ─── Handle Registration ───────────────────────────────────────────
    public function register(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|max:100',
            'password' => 'required|min:8|confirmed',
            'role'     => 'required|in:student,company',
        ]);

        // Check email uniqueness manually (Oracle-safe)
        $exists = User::where('EMAIL', strtolower($request->email))->exists();
        if ($exists) {
            return back()
                ->withInput($request->only('email', 'role'))
                ->withErrors(['email' => 'This email is already registered.']);
        }

        DB::transaction(function () use ($request) {
            $user = User::create([
                'EMAIL'         => strtolower($request->email),
                'PASSWORD_HASH' => Hash::make($request->password),
                'ROLE'          => $request->role,
                'IS_ACTIVE'     => 1,
            ]);

            // Create a placeholder profile so FK references work immediately
            if ($request->role === 'student') {
                Student::create([
                    'USER_ID'         => $user->USER_ID,
                    'FIRST_NAME'      => 'New',
                    'LAST_NAME'       => 'Student',
                    'UNIVERSITY'      => 'Not Set',
                    'DEPARTMENT'      => 'Not Set',
                ]);
            } elseif ($request->role === 'company') {
                Company::create([
                    'USER_ID'      => $user->USER_ID,
                    'COMPANY_NAME' => 'New Company',
                    'INDUSTRY'     => 'Not Set',
                    'LOCATION'     => 'Not Set',
                ]);
            }

            Auth::login($user);
        });

        return $this->redirectByRole($request->role);
    }

    // ─── Logout ────────────────────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }

    // ─── Role-Based Redirect Helper ────────────────────────────────────
    private function redirectByRole(string $role)
    {
        return match ($role) {
            'student' => redirect()->route('student.dashboard'),
            'company' => redirect()->route('company.dashboard'),
            'admin'   => redirect()->route('admin.dashboard'),
            default   => redirect()->route('login'),
        };
    }
}