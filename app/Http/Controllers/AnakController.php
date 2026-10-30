<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Orang tua dengan lebih dari satu anak memilih anak yang ingin dipantau. */
class AnakController extends Controller
{
    public function pilih(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->role === 'orang_tua', 403);

        $id = (int) $request->input('anak_id');
        abort_unless($user->children->contains('id', $id), 403);

        session(['anak_id' => $id]);

        return back();
    }
}