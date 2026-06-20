@extends('layouts.app')

@section('title', 'Dashboard Siswa - EduMonitor')

@section('content')
<div class="space-y-8">

    <!-- Greeting -->
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Halo, {{ Auth::user()->name }} 👋</h2>
        <p class="text-slate-400 text-sm mt-1">Kelas {{ $kelas->nama_kelas ?? '-' }} &mdash; Semester Genap 2025/2026 berjalan minggu ke-12.</p>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Rata-rata Nilai -->
        <a href="{{ route('siswa.nilai') }}" class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between hover-lift">
            <div class="flex items-center gap-6">
                <div class="relative w-20 h-20 flex items-center justify-center rounded-full border-4 border-indigo-500 font-extrabold text-3xl text-indigo-600">
                    {{ $avgGrade }}
                </div>
                <div>
                    <h3 class="font-bold text-slate-700 text-lg">Rata-rata Nilai</h3>
                    <div class="flex items-center gap-1.5 mt-1">
                        <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-600 text-xs font-bold px-2.5 py-1 rounded-full">
                            <i class="fa-solid fa-arrow-trend-up"></i>
                            + 3 poin
                        </span>
                        <span class="text-xs text-slate-400 font-semibold">dari semester lalu</span>
                    </div>
                </div>
            </div>
        </a>

        <!-- Mapel Count -->
        <a href="{{ route('siswa.nilai') }}" class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between hover-lift">
            <div>
                <h3 class="font-bold text-slate-400 text-sm tracking-wider uppercase">Mapel Diambil</h3>
                <p class="text-5xl font-extrabold text-slate-700 mt-2">{{ $grades->count() }}</p>
            </div>
            <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-500">
                <i class="fa-solid fa-book-open text-2xl"></i>
            </div>
        </a>

        <!-- Evaluasi Count -->
        <a href="{{ route('siswa.evaluasi') }}" class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between hover-lift">
            <div>
                <h3 class="font-bold text-slate-400 text-sm tracking-wider uppercase">Evaluasi Diisi</h3>
                <p class="text-5xl font-extrabold text-slate-700 mt-2">{{ $evaluatedCount }}/{{ $subjectTeachers->count() }}</p>
            </div>
            <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-500">
                <i class="fa-solid fa-pen-to-square text-2xl"></i>
            </div>
        </a>
    </div>

    <!-- Shortcut Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="{{ route('siswa.nilai') }}" class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover-lift flex items-center gap-4">
            <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-500 shrink-0">
                <i class="fa-solid fa-chart-simple text-2xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-700 text-lg">Lihat Nilai</h3>
                <p class="text-sm text-slate-400 font-medium">Cek perkembangan nilai per mapel & per semester</p>
            </div>
        </a>

        <a href="{{ route('siswa.evaluasi') }}" class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover-lift flex items-center gap-4">
            <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-500 shrink-0">
                <i class="fa-solid fa-pen-to-square text-2xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-700 text-lg">Isi Evaluasi Pembelajaran</h3>
                <p class="text-sm text-slate-400 font-medium">Beri evaluasi untuk guru pengampu mapelmu</p>
            </div>
        </a>
    </div>

    <!-- Catatan Guru -->
    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
        <h3 class="text-base font-bold text-slate-600 uppercase tracking-wider mb-4">
            <i class="fa-regular fa-comment-dots mr-2 text-indigo-500"></i>Catatan Terbaru
        </h3>
        <div class="space-y-3">
            @forelse($notes as $note)
                <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-50/20 text-slate-700 text-sm leading-relaxed">
                    <span class="font-bold text-indigo-600">{{ $note->teacher->name }} - {{ $note->subject->kode_mapel }}</span>:
                    <p class="mt-1 text-slate-600">{{ $note->catatan }}</p>
                </div>
            @empty
                <p class="text-slate-400 text-sm font-medium">Belum ada catatan dari guru semester ini.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection