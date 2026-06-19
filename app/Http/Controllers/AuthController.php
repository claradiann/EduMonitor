<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Handle authentication attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ]);

        // Attempt login with username or email
        $loginField = filter_var($credentials['login_id'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$loginField => $credentials['login_id'], 'password' => $credentials['password']], $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'login_id' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->onlyInput('login_id');
    }

    /**
     * Handle quick-login for demo purposes.
     */
    public function quickLogin($username)
    {
        $user = \App\Models\User::where('username', $username)->first();
        if ($user) {
            Auth::login($user);
            session()->regenerate();
            return redirect()->route('dashboard');
        }
        return redirect()->route('login')->withErrors(['login_id' => 'User demo tidak ditemukan.']);
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
