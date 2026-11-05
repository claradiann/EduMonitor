<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /** Halaman login. Pengguna yang sudah login langsung ke dashboard (diatur middleware guest). */
    public function showLogin()
    {
        return view('auth.login');
    }

    /** Login dengan username atau email, ditambah password (BR-01). */
    public function login(Request $request)
    {
        $data = $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ], [
            'login_id.required' => 'Username atau email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Isian berbentuk email dicocokkan ke kolom email; selain itu ke username (disimpan huruf kecil).
        $kolom = filter_var($data['login_id'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $nilai = $kolom === 'username' ? strtolower($data['login_id']) : $data['login_id'];

        if (Auth::attempt([$kolom => $nilai, 'password' => $data['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withErrors(['login_id' => 'Username/email atau password salah.'])
            ->onlyInput('login_id');
    }

    /**
     * Login demo tanpa password, untuk presentasi di laptop sendiri.
     * Route-nya hanya didaftarkan saat APP_ENV=local (BR-04). Cek di sini adalah lapis kedua.
     */
    public function quickLogin(string $username)
    {
        abort_unless(app()->environment('local'), 404);

        $user = User::where('username', $username)->first();
        if (! $user) {
            return redirect()->route('login')->withErrors(['login_id' => 'Akun demo tidak ditemukan.']);
        }

        Auth::login($user);
        session()->regenerate();

        return redirect()->route('dashboard');
    }

    /** Logout: sesi dihapus dan token CSRF diganti (KF-U02). */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}