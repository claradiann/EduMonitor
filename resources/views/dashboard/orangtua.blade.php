@extends('layouts.app')

@section('title', 'Dashboard Orang Tua - EduMonitor')

@section('content')
<div class="space-y-8">

    @if(!$student)
        <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm text-center">
            <div class="w-16 h-16 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-circle-exclamation text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800">Siswa Tidak Ditemukan</h3>
            <p class="text-slate-400 mt-2">Akun orang tua ini belum terhubung dengan data siswa mana pun.</p>
        </div>
    @else
        <!-- Top Greeting Banner -->
        <div class="bg-linear-to-r from-indigo-500 to-indigo-600 rounded-[2rem] p-8 text-white relative overflow-hidden shadow-lg shadow-indigo-100">
            <div class="absolute right-0 top-0 w-80 h-full opacity-10 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-white via-indigo-200 to-indigo-900 pointer-events-none"></div>
            <span class="bg-white/20 text-white font-bold text-xs uppercase px-3 py-1.5 rounded-full tracking-wider inline-block mb-3">
                Memantau: {{ $student->name }} (Kelas {{ $kelas->nama_kelas ?? 'VII' }})
            </span>
            <h2 class="text-4xl font-extrabold tracking-tight">Halo, {{ Auth::user()->name }}!</h2>
            <p class="text-indigo-100 text-sm mt-1 font-medium">Pantau hasil belajar dan masukan guru untuk putra/putri Anda secara berkala.</p>
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Rata-rata Nilai -->
            <a href="{{ route('orangtua.nilai') }}" class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between hover-lift">
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
            <a href="{{ route('orangtua.nilai') }}" class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between hover-lift">
                <div>
                    <h3 class="font-bold text-slate-400 text-sm tracking-wider uppercase">Mata Pelajaran</h3>
                    <p class="text-5xl font-extrabold text-slate-700 mt-2">{{ $grades->count() }}</p>
                </div>
                <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-500">
                    <i class="fa-solid fa-book-open text-2xl"></i>
                </div>
            </a>

            <!-- Ekskul Count -->
            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between hover-lift">
                <div>
                    <h3 class="font-bold text-slate-400 text-sm tracking-wider uppercase">Ekstrakurikuler</h3>
                    <p class="text-5xl font-extrabold text-slate-700 mt-2">2</p>
                </div>
                <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-500">
                    <i class="fa-solid fa-basketball text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Shortcut Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="{{ route('orangtua.nilai') }}" class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover-lift flex items-center gap-4">
                <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-500 shrink-0">
                    <i class="fa-solid fa-chart-simple text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-700 text-lg">Lihat Nilai Siswa</h3>
                    <p class="text-sm text-slate-400 font-medium">Cek perkembangan nilai per mapel & per semester</p>
                </div>
            </a>

            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 shrink-0">
                    <i class="fa-solid fa-lock text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-700 text-lg">Evaluasi Pembelajaran</h3>
                    <p class="text-sm text-slate-400 font-medium">Pengisian kuesioner hanya dapat diakses melalui akun siswa</p>
                </div>
            </div>
        </div>

        <!-- Catatan Guru -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
            <h3 class="text-base font-bold text-slate-600 uppercase tracking-wider mb-4">
                <i class="fa-regular fa-comment-dots mr-2 text-indigo-500"></i>Catatan Terbaru Guru
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
    @endif
</div>
@endsection