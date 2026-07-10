<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->ROLE);
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        $rows = DB::select(
            "SELECT * FROM USERS WHERE EMAIL = :email AND ROWNUM = 1",
            ['email' => strtolower($request->email)]
        );

        if (empty($rows)) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid email or password.']);
        }

        $row = $rows[0];

        if (!Hash::check($request->password, $row->password_hash)) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid email or password.']);
        }

        if ((string) $row->is_active !== '1') {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Your account has been deactivated. Contact admin.']);
        }

        // Load into Eloquent User for Auth::login() compatibility
        $user = \App\Models\User::find($row->user_id);
        Auth::login($user, $request->boolean('remember'));

        return $this->redirectByRole($row->role);
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->ROLE);
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|max:100',
            'password' => 'required|min:8|confirmed',
            'role'     => 'required|in:student,company',
        ]);

        // Check uniqueness
        $exists = DB::select(
            "SELECT COUNT(*) AS CNT FROM USERS WHERE EMAIL = :email",
            ['email' => strtolower($request->email)]
        );


        if ($exists[0]->cnt > 0) {
            return back()->withInput($request->only('email', 'role'))
                ->withErrors(['email' => 'This email is already registered.']);
        }

        DB::transaction(function () use ($request) {
            // Insert user
            DB::insert(
                "INSERT INTO USERS (EMAIL, PASSWORD_HASH, ROLE, IS_ACTIVE)
                 VALUES (:email, :password_hash, :role, 1)",
                [
                    'email'         => strtolower($request->email),
                    'password_hash' => Hash::make($request->password),
                    'role'          => $request->role,
                ]
            );

            // Get the new user ID
            $userRow = DB::select(
                "SELECT USER_ID FROM USERS WHERE EMAIL = :email AND ROWNUM = 1",
                ['email' => strtolower($request->email)]
            );
            $userId = $userRow[0]->user_id;

            if ($request->role === 'student') {
                DB::insert(
                    "INSERT INTO STUDENTS (USER_ID, FIRST_NAME, LAST_NAME, UNIVERSITY, DEPARTMENT)
                    VALUES (:user_id, 'New', 'Student', 'Not Set', 'Not Set')",
                    ['user_id' => $userId]
                );
            } elseif ($request->role === 'company') {
                DB::insert(
                    "INSERT INTO COMPANIES (USER_ID, COMPANY_NAME, INDUSTRY, LOCATION)
                    VALUES (:user_id, 'New Company', 'Not Set', 'Not Set')",
                    ['user_id' => $userId]
                );
            }

            // Log into Auth session
            $user = \App\Models\User::find($userId);
            Auth::login($user);
        });

        return $this->redirectByRole($request->role);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }

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