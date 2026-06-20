@extends('layouts.app')

@section('title', 'Dashboard Admin - EduMonitor')

@section('content')
<div class="space-y-6">

    <!-- Greeting -->
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Halo, {{ Auth::user()->name }} 👋</h2>
        <p class="text-slate-400 text-sm mt-1">Semester Genap 2025/2026 &mdash; Berikut ringkasan data sekolah hari ini.</p>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover-lift">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 bg-indigo-50 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-users text-indigo-500 text-sm"></i>
                </div>
                <span class="text-xs text-slate-300 font-medium">Siswa</span>
            </div>
            <p class="text-3xl font-extrabold text-slate-800">{{ $stats['total_students'] }}</p>
            <p class="text-slate-400 text-xs mt-1">Total terdaftar</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover-lift">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 bg-violet-50 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-chalkboard-user text-violet-500 text-sm"></i>
                </div>
                <span class="text-xs text-slate-300 font-medium">Guru</span>
            </div>
            <p class="text-3xl font-extrabold text-slate-800">{{ $stats['total_teachers'] }}</p>
            <p class="text-slate-400 text-xs mt-1">Total aktif</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover-lift">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 bg-sky-50 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-school text-sky-500 text-sm"></i>
                </div>
                <span class="text-xs text-slate-300 font-medium">Kelas</span>
            </div>
            <p class="text-3xl font-extrabold text-slate-800">{{ $stats['total_classes'] }}</p>
            <p class="text-slate-400 text-xs mt-1">Total kelas</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover-lift">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-square-poll-vertical text-emerald-500 text-sm"></i>
                </div>
                <span class="text-xs text-slate-300 font-medium">Evaluasi</span>
            </div>
            <p class="text-3xl font-extrabold text-slate-800">{{ $stats['total_evaluations'] }}</p>
            <p class="text-slate-400 text-xs mt-1">Respon masuk</p>
        </div>
    </div>

    <!-- Bottom Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <!-- Distribusi User -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <h3 class="font-bold text-slate-700 text-sm mb-4">Distribusi Pengguna</h3>
            <div class="space-y-3">
                <div>
                    <div class="flex justify-between text-xs text-slate-500 mb-1">
                        <span>Siswa</span>
                        <span class="font-semibold text-slate-700">{{ $stats['total_students'] }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        @php $total = $stats['total_students'] + $stats['total_teachers'] + $stats['total_parents']; @endphp
                        <div class="bg-indigo-400 h-1.5 rounded-full" style="width: {{ $total > 0 ? round($stats['total_students']/$total*100) : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs text-slate-500 mb-1">
                        <span>Guru</span>
                        <span class="font-semibold text-slate-700">{{ $stats['total_teachers'] }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="bg-violet-400 h-1.5 rounded-full" style="width: {{ $total > 0 ? round($stats['total_teachers']/$total*100) : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs text-slate-500 mb-1">
                        <span>Orang Tua</span>
                        <span class="font-semibold text-slate-700">{{ $stats['total_parents'] }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="bg-sky-400 h-1.5 rounded-full" style="width: {{ $total > 0 ? round($stats['total_parents']/$total*100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Sistem -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm md:col-span-2">
            <h3 class="font-bold text-slate-700 text-sm mb-4">Info Sistem</h3>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-xs text-slate-400 mb-1">Total Mata Pelajaran</p>
                    <p class="text-xl font-bold text-slate-700">{{ $stats['total_subjects'] }}</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-xs text-slate-400 mb-1">Total Orang Tua</p>
                    <p class="text-xl font-bold text-slate-700">{{ $stats['total_parents'] }}</p>
                </div>
                <div class="bg-indigo-50 rounded-xl p-4 col-span-2 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-indigo-400 mb-1">Respon Evaluasi Masuk</p>
                        <p class="text-xl font-bold text-indigo-700">{{ $stats['total_evaluations'] }} respon</p>
                    </div>
                    <a href="{{ route('admin.recap') }}" class="text-xs font-semibold text-indigo-500 hover:text-indigo-700 flex items-center gap-1">
                        Lihat Rekap <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection