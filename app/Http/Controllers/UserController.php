<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Store a new user (admin, guru, siswa, orang_tua).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'username' => 'required|string|unique:users,username|alpha_dash|max:50',
            'role' => 'required|in:admin,guru,siswa,orang_tua',
            'password' => 'required|string|min:6',
            'nisn' => 'nullable|string|required_if:role,siswa',
            'nip' => 'nullable|string|required_if:role,guru',
            'kelas_id' => 'nullable|exists:kelas,id|required_if:role,siswa',
            'parent_of_id' => 'nullable|exists:users,id',
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'username' => strtolower($request->username),
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'nisn' => $request->role === 'siswa' ? $request->nisn : null,
            'nip' => $request->role === 'guru' ? $request->nip : null,
            'kelas_id' => $request->role === 'siswa' ? $request->kelas_id : null,
            'parent_of_id' => $request->role === 'orang_tua' ? $request->parent_of_id : null,
        ];

        User::create($userData);

        return redirect()->route('dashboard')->with('success', 'Pengguna berhasil ditambahkan!');
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'username' => 'required|string|alpha_dash|max:50|unique:users,username,' . $id,
            'nisn' => 'nullable|string|required_if:role,siswa',
            'nip' => 'nullable|string|required_if:role,guru',
            'kelas_id' => 'nullable|exists:kelas,id|required_if:role,siswa',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->username = strtolower($request->username);
        $user->nisn = $user->role === 'siswa' ? $request->nisn : null;
        $user->nip = $user->role === 'guru' ? $request->nip : null;
        $user->kelas_id = $user->role === 'siswa' ? $request->kelas_id : null;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('dashboard')->with('success', 'Data pengguna berhasil diperbarui!');
    }

    /**
     * Delete a user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting oneself
        if (auth()->id() === $user->id) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('dashboard')->with('success', 'Pengguna berhasil dihapus!');
    }
}
