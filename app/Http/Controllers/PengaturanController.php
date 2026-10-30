<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengaturanController extends Controller
{
    public function edit()
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        return view('admin.pengaturan', [
            'batasHari' => Setting::batasUnggahHari(),
            'durasiJam' => Setting::durasiBukaAksesJam(),
            'semester' => Setting::semesterAktif(),
            'batasNilaiAkhir' => Setting::get('batas_nilai_akhir'),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $data = $request->validate([
            'batas_unggah_hari' => 'required|integer|min:1|max:60',
            'durasi_buka_akses_jam' => 'required|integer|min:1|max:168',
            'semester_aktif' => 'required|string|max:50',
            'batas_nilai_akhir' => 'nullable|date',
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}