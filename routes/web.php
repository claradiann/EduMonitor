<?php

use App\Http\Controllers\AdminPenilaianController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\AnakController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EkskulController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanInsidentalController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PemantauanController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
| Aturan hak akses (BR-02, 03-aturan.md):
| - Setiap route berada di dalam grup peran ->middleware('peran:...').
| - Cek kepemilikan data (guru hanya kelasnya, orang tua hanya anaknya) tetap di controller.
| - Route baru WAJIB dimasukkan ke salah satu grup di bawah.
*/

// ---------- Tamu ----------
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

// Login demo tanpa password: hanya terdaftar di lingkungan pengembangan (BR-04).
if (app()->environment('local')) {
    Route::get('/demo-login/{username}', [AuthController::class, 'quickLogin'])->name('login.demo');
}

// ---------- Semua peran yang sudah login ----------
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notifikasi
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');
    Route::get('/notifikasi/{notifikasi}', [NotifikasiController::class, 'buka'])->name('notifikasi.buka');

    // Ganti password sendiri (KF-U06)
    Route::get('/akun/password', [AkunController::class, 'formPassword'])->name('akun.password');
    Route::put('/akun/password', [AkunController::class, 'ubahPassword'])->name('akun.password.ubah');

    // ---------- Siswa dan orang tua ----------
    Route::middleware('peran:siswa,orang_tua')->group(function () {
        Route::get('/nilai', [NilaiController::class, 'index'])->name('nilai');
        Route::get('/siswa/nilai', [NilaiController::class, 'index'])->name('siswa.nilai');
        Route::get('/orangtua/nilai', [NilaiController::class, 'index'])->name('orangtua.nilai');
        Route::get('/jadwal', [PemantauanController::class, 'index'])->name('jadwal');
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
        Route::get('/laporan/{laporanSiswa}', [LaporanController::class, 'showSiswa'])->name('laporan.show');
    });

    // ---------- Siswa ----------
    Route::middleware('peran:siswa')->group(function () {
        Route::get('/siswa/evaluasi', [DashboardController::class, 'siswaEvaluasi'])->name('siswa.evaluasi');
        Route::get('/siswa/profile', [DashboardController::class, 'siswaProfile'])->name('siswa.profile');
        Route::get('/siswa/lapor', [LaporanInsidentalController::class, 'create'])->name('siswa.lapor');
        Route::post('/siswa/lapor', [LaporanInsidentalController::class, 'store'])->name('siswa.lapor.store');
        Route::get('/evaluation/fill/{subject_teacher_id}', [EvaluationController::class, 'showForm'])->name('evaluation.fill');
        Route::post('/evaluation/submit/{subject_teacher_id}', [EvaluationController::class, 'storeResponse'])->name('evaluation.submit');
    });

    // ---------- Orang tua ----------
    Route::middleware('peran:orang_tua')->group(function () {
        Route::get('/orangtua/profile', [DashboardController::class, 'orangTuaProfile'])->name('orangtua.profile');
        Route::post('/pilih-anak', [AnakController::class, 'pilih'])->name('anak.pilih');
    });

    // ---------- Guru ----------
    Route::middleware('peran:guru')->group(function () {
        Route::get('/guru/profile', [DashboardController::class, 'guruProfile'])->name('guru.profile');
        Route::get('/guru/feedback', [DashboardController::class, 'guruFeedback'])->name('guru.feedback');

        Route::get('/guru/penilaian', [PenilaianController::class, 'index'])->name('guru.penilaian');
        Route::post('/guru/kelas/{subjectTeacher}/penilaian', [PenilaianController::class, 'store'])->name('guru.penilaian.store');
        Route::get('/guru/penilaian/{assessment}', [PenilaianController::class, 'input'])->name('guru.penilaian.input');
        Route::post('/guru/penilaian/{assessment}', [PenilaianController::class, 'simpan'])->name('guru.penilaian.simpan');
        Route::patch('/guru/penilaian/{assessment}/tanggal', [PenilaianController::class, 'ubahTanggal'])->name('guru.penilaian.tanggal');
        Route::delete('/guru/penilaian/{assessment}', [PenilaianController::class, 'destroy'])->name('guru.penilaian.hapus');
        Route::post('/guru/penilaian/{assessment}/susulan', [PenilaianController::class, 'susulan'])->name('guru.penilaian.susulan');
        Route::delete('/guru/penilaian/{assessment}/susulan/{makeup}', [PenilaianController::class, 'hapusSusulan'])->name('guru.penilaian.susulan.hapus');
        Route::post('/guru/penilaian/{assessment}/ajukan-akses', [PenilaianController::class, 'ajukanAkses'])->name('guru.penilaian.akses');
        Route::get('/guru/kelas/{subjectTeacher}/rekap-bab', [PenilaianController::class, 'rekapBab'])->name('guru.rekap-bab');
        Route::get('/guru/ekspor/{subjectTeacher}', [PenilaianController::class, 'ekspor'])->name('guru.ekspor');

        Route::get('/guru/ekskul', [EkskulController::class, 'pembina'])->name('guru.ekskul');

        Route::get('/guru/laporan', [LaporanController::class, 'guruIndex'])->name('guru.laporan');
        Route::get('/guru/laporan/buat/{subjectTeacher}', [LaporanController::class, 'create'])->name('guru.laporan.buat');
        Route::post('/guru/laporan/kirim/{subjectTeacher}', [LaporanController::class, 'store'])->name('guru.laporan.kirim');
    });

    // ---------- Guru dan admin ----------
    // Kepemilikan dicek di controller: guru hanya laporan/ekskul miliknya.
    Route::middleware('peran:guru,admin')->group(function () {
        Route::get('/guru/laporan/{laporan}', [LaporanController::class, 'show'])->name('guru.laporan.show');

        Route::get('/ekskul/{ekskul}', [EkskulController::class, 'show'])->name('ekskul.show');
        Route::post('/ekskul/{ekskul}/nilai', [EkskulController::class, 'simpanNilai'])->name('ekskul.nilai');
        Route::get('/ekskul/{ekskul}/ekspor', [EkskulController::class, 'ekspor'])->name('ekskul.ekspor');
    });

    // ---------- Admin ----------
    Route::middleware('peran:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/profile', [DashboardController::class, 'adminProfile'])->name('profile');

        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/recap', [DashboardController::class, 'recap'])->name('recap');
        Route::get('/laporan', [LaporanInsidentalController::class, 'index'])->name('laporan');
        Route::get('/laporan-perkembangan', [LaporanController::class, 'adminIndex'])->name('laporan-perkembangan');

        Route::get('/pengaturan', [PengaturanController::class, 'edit'])->name('pengaturan');
        Route::put('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');

        Route::get('/jadwal', [AdminPenilaianController::class, 'jadwal'])->name('jadwal');
        Route::post('/jadwal/ujian', [AdminPenilaianController::class, 'storeUjian'])->name('jadwal.ujian');
        Route::post('/jadwal/geser', [AdminPenilaianController::class, 'geser'])->name('jadwal.geser');
        Route::get('/jadwal/{assessment}', [AdminPenilaianController::class, 'show'])->name('jadwal.show');
        Route::patch('/jadwal/{assessment}/tanggal', [AdminPenilaianController::class, 'ubahTanggal'])->name('jadwal.tanggal');
        Route::post('/jadwal/{assessment}/susulan', [AdminPenilaianController::class, 'susulan'])->name('jadwal.susulan');
        Route::post('/jadwal/{assessment}/buka-akses', [AdminPenilaianController::class, 'bukaLangsung'])->name('jadwal.buka');

        Route::get('/akses', [AdminPenilaianController::class, 'akses'])->name('akses');
        Route::post('/akses/{unlock}/setujui', [AdminPenilaianController::class, 'setujui'])->name('akses.setujui');
        Route::post('/akses/{unlock}/tolak', [AdminPenilaianController::class, 'tolak'])->name('akses.tolak');

        Route::get('/ekskul', [EkskulController::class, 'index'])->name('ekskul');
        Route::post('/ekskul', [EkskulController::class, 'store'])->name('ekskul.store');
        Route::put('/ekskul/{ekskul}', [EkskulController::class, 'update'])->name('ekskul.update');
        Route::delete('/ekskul/{ekskul}', [EkskulController::class, 'destroy'])->name('ekskul.hapus');
        Route::post('/ekskul/{ekskul}/anggota', [EkskulController::class, 'tambahAnggota'])->name('ekskul.anggota');
        Route::delete('/ekskul/{ekskul}/anggota/{anggota}', [EkskulController::class, 'hapusAnggota'])->name('ekskul.anggota.hapus');
    });
});