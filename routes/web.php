<?php

use App\Http\Controllers\AdminPenilaianController;
use App\Http\Controllers\AnakController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EkskulController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\LaporanInsidentalController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PemantauanController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/demo-login/{username}', [AuthController::class, 'quickLogin'])->name('login.demo');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notifikasi (semua peran)
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi');
    Route::get('/notifikasi/{notifikasi}', [NotifikasiController::class, 'buka'])->name('notifikasi.buka');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');

    // Siswa & orang tua: nilai dan jadwal penilaian
    Route::get('/nilai', [NilaiController::class, 'index'])->name('nilai');
    Route::get('/siswa/nilai', [NilaiController::class, 'index'])->name('siswa.nilai');
    Route::get('/orangtua/nilai', [NilaiController::class, 'index'])->name('orangtua.nilai');
    Route::get('/jadwal', [PemantauanController::class, 'index'])->name('jadwal');
    Route::post('/pilih-anak', [AnakController::class, 'pilih'])->name('anak.pilih');

    // Siswa
    Route::get('/siswa/evaluasi', [DashboardController::class, 'siswaEvaluasi'])->name('siswa.evaluasi');
    Route::get('/siswa/profile', [DashboardController::class, 'siswaProfile'])->name('siswa.profile');
    Route::get('/siswa/lapor', [LaporanInsidentalController::class, 'create'])->name('siswa.lapor');
    Route::post('/siswa/lapor', [LaporanInsidentalController::class, 'store'])->name('siswa.lapor.store');
    Route::get('/evaluation/fill/{subject_teacher_id}', [EvaluationController::class, 'showForm'])->name('evaluation.fill');
    Route::post('/evaluation/submit/{subject_teacher_id}', [EvaluationController::class, 'storeResponse'])->name('evaluation.submit');

    // Orang tua
    Route::get('/orangtua/profile', [DashboardController::class, 'orangTuaProfile'])->name('orangtua.profile');

    // Guru
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

    // Ekstrakurikuler (admin dan guru pembina)
    Route::get('/ekskul/{ekskul}', [EkskulController::class, 'show'])->name('ekskul.show');
    Route::post('/ekskul/{ekskul}/nilai', [EkskulController::class, 'simpanNilai'])->name('ekskul.nilai');
    Route::get('/ekskul/{ekskul}/ekspor', [EkskulController::class, 'ekspor'])->name('ekskul.ekspor');

    // Admin
    Route::get('/admin/profile', [DashboardController::class, 'adminProfile'])->name('admin.profile');
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('/admin/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/admin/recap', [DashboardController::class, 'recap'])->name('admin.recap');
    Route::get('/admin/laporan', [LaporanInsidentalController::class, 'index'])->name('admin.laporan');
    Route::get('/admin/pengaturan', [PengaturanController::class, 'edit'])->name('admin.pengaturan');
    Route::put('/admin/pengaturan', [PengaturanController::class, 'update'])->name('admin.pengaturan.update');

    Route::get('/admin/jadwal', [AdminPenilaianController::class, 'jadwal'])->name('admin.jadwal');
    Route::post('/admin/jadwal/ujian', [AdminPenilaianController::class, 'storeUjian'])->name('admin.jadwal.ujian');
    Route::post('/admin/jadwal/geser', [AdminPenilaianController::class, 'geser'])->name('admin.jadwal.geser');
    Route::get('/admin/jadwal/{assessment}', [AdminPenilaianController::class, 'show'])->name('admin.jadwal.show');
    Route::patch('/admin/jadwal/{assessment}/tanggal', [AdminPenilaianController::class, 'ubahTanggal'])->name('admin.jadwal.tanggal');
    Route::post('/admin/jadwal/{assessment}/susulan', [AdminPenilaianController::class, 'susulan'])->name('admin.jadwal.susulan');
    Route::post('/admin/jadwal/{assessment}/buka-akses', [AdminPenilaianController::class, 'bukaLangsung'])->name('admin.jadwal.buka');

    Route::get('/admin/akses', [AdminPenilaianController::class, 'akses'])->name('admin.akses');
    Route::post('/admin/akses/{unlock}/setujui', [AdminPenilaianController::class, 'setujui'])->name('admin.akses.setujui');
    Route::post('/admin/akses/{unlock}/tolak', [AdminPenilaianController::class, 'tolak'])->name('admin.akses.tolak');

    Route::get('/admin/ekskul', [EkskulController::class, 'index'])->name('admin.ekskul');
    Route::post('/admin/ekskul', [EkskulController::class, 'store'])->name('admin.ekskul.store');
    Route::put('/admin/ekskul/{ekskul}', [EkskulController::class, 'update'])->name('admin.ekskul.update');
    Route::delete('/admin/ekskul/{ekskul}', [EkskulController::class, 'destroy'])->name('admin.ekskul.hapus');
    Route::post('/admin/ekskul/{ekskul}/anggota', [EkskulController::class, 'tambahAnggota'])->name('admin.ekskul.anggota');
    Route::delete('/admin/ekskul/{ekskul}/anggota/{anggota}', [EkskulController::class, 'hapusAnggota'])->name('admin.ekskul.anggota.hapus');
});