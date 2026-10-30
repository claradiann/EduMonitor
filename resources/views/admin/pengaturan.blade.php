@extends('layouts.app')

@section('title', 'Pengaturan - EduMonitor')

@section('content')
<div class="space-y-6 max-w-2xl">
    <div>
        <h1 class="page-title">Pengaturan</h1>
        <p class="page-sub">Berlaku untuk seluruh guru.</p>
    </div>

    <form method="POST" action="{{ route('admin.pengaturan.update') }}" class="card card-pad space-y-5">
        @csrf @method('PUT')
        <div>
            <label class="label" for="semester_aktif">Semester aktif</label>
            <input id="semester_aktif" name="semester_aktif" class="input" required value="{{ old('semester_aktif', $semester) }}">
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="label" for="batas_unggah_hari">Masa input nilai</label>
                <div class="flex items-center gap-2">
                    <input id="batas_unggah_hari" type="number" name="batas_unggah_hari" min="1" max="60" class="input !w-24" value="{{ old('batas_unggah_hari', $batasHari) }}">
                    <span class="text-sm text-slate-500">hari setelah penilaian</span>
                </div>
                <p class="hint">Selama masa ini nilai bisa diubah, termasuk untuk remedial. Setelahnya terkunci.</p>
            </div>
            <div>
                <label class="label" for="durasi_buka_akses_jam">Lama buka akses</label>
                <div class="flex items-center gap-2">
                    <input id="durasi_buka_akses_jam" type="number" name="durasi_buka_akses_jam" min="1" max="168" class="input !w-24" value="{{ old('durasi_buka_akses_jam', $durasiJam) }}">
                    <span class="text-sm text-slate-500">jam</span>
                </div>
                <p class="hint">Lama akses dibuka setelah admin menyetujui pengajuan guru.</p>
            </div>
        </div>
        <div>
            <label class="label" for="batas_nilai_akhir">Batas pengumpulan nilai akhir</label>
            <input id="batas_nilai_akhir" type="date" name="batas_nilai_akhir" class="input sm:!w-56" value="{{ old('batas_nilai_akhir', $batasNilaiAkhir) }}">
            <p class="hint">Sesuai kalender akademik. Dipakai juga sebagai batas input nilai ekstrakurikuler.</p>
        </div>
        <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i>Simpan</button>
    </form>
</div>
@endsection