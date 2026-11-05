<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi route hanya untuk peran tertentu.
 * Pemakaian di route: ->middleware('peran:admin') atau ->middleware('peran:siswa,orang_tua').
 *
 * Cek peran dilakukan di sini, satu tempat untuk semua route, supaya tidak ada
 * controller yang lupa mengecek (penyebab BUG-01 di halaman kelola pengguna).
 */
class CekPeran
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();

        abort_unless($user && in_array($user->role, $peran, true), 403, 'Anda tidak memiliki akses ke halaman ini.');

        return $next($request);
    }
}