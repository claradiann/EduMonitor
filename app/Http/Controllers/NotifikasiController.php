<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    public function index()
    {
        $notifikasi = Auth::user()->notifikasi()->paginate(30);

        return view('notifikasi.index', compact('notifikasi'));
    }

    /** Buka notifikasi: tandai dibaca lalu arahkan ke halaman terkait. */
    public function buka(Notifikasi $notifikasi)
    {
        abort_unless($notifikasi->user_id === Auth::id(), 403);
        $notifikasi->update(['dibaca_at' => $notifikasi->dibaca_at ?? now()]);

        return redirect($notifikasi->url ?: route('notifikasi'));
    }

    public function bacaSemua()
    {
        Auth::user()->notifikasi()->whereNull('dibaca_at')->update(['dibaca_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}