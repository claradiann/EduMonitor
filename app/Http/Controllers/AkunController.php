<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/** Pengaturan akun milik pengguna yang sedang login (semua peran). */
class AkunController extends Controller
{
    public function formPassword()
    {
        return view('akun.password');
    }

    /** Ganti password sendiri (KF-U06). Password lama wajib benar, password baru min. 6 karakter (BR-06). */
    public function ubahPassword(Request $request)
    {
        $request->validate([
            'password_lama' => 'required|current_password',
            'password' => 'required|string|min:6|confirmed|different:password_lama',
        ], [
            'password_lama.required' => 'Password lama wajib diisi.',
            'password_lama.current_password' => 'Password lama salah.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak sama.',
            'password.different' => 'Password baru harus berbeda dari password lama.',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        // Sesi lain (misalnya di perangkat lain) tidak lagi berlaku setelah password diganti.
        Auth::logoutOtherDevices($request->password);

        return redirect()->route('akun.password')->with('success', 'Password berhasil diganti.');
    }
}