@extends('layouts.app')

@section('title', 'Dashboard Admin - EduMonitor')

@section('content')
<div class="space-y-8">

    <!-- Top Greeting Banner -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-[2rem] p-8 text-white relative overflow-hidden shadow-lg shadow-indigo-100">
        <div class="absolute right-0 top-0 w-80 h-full opacity-10 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-white via-indigo-200 to-indigo-900 pointer-events-none"></div>
        <h2 class="text-4xl font-extrabold tracking-tight">Halo, Admin / Wakasek!</h2>
        <p class="text-indigo-100 text-sm mt-1 font-medium">Kelola data pengguna sekolah dan pantau kualitas pengajaran melalui rekap kuesioner evaluasi.</p>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between hover-lift">
            <div>
                <h3 class="font-bold text-slate-400 text-sm tracking-wider uppercase">Total Siswa</h3>
                <p class="text-4xl font-extrabold text-slate-700 mt-2">{{ $stats['total_students'] }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500">
                <i class="fa-solid fa-users text-xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between hover-lift">
            <div>
                <h3 class="font-bold text-slate-400 text-sm tracking-wider uppercase">Total Guru</h3>
                <p class="text-4xl font-extrabold text-slate-700 mt-2">{{ $stats['total_teachers'] }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500">
                <i class="fa-solid fa-chalkboard-user text-xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between hover-lift">
            <div>
                <h3 class="font-bold text-slate-400 text-sm tracking-wider uppercase">Total Kelas</h3>
                <p class="text-4xl font-extrabold text-slate-700 mt-2">{{ $stats['total_classes'] }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500">
                <i class="fa-solid fa-school text-xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between hover-lift">
            <div>
                <h3 class="font-bold text-slate-400 text-sm tracking-wider uppercase">Evaluasi Masuk</h3>
                <p class="text-4xl font-extrabold text-slate-700 mt-2">{{ $stats['total_evaluations'] }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500">
                <i class="fa-solid fa-square-poll-vertical text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Shortcut Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="{{ route('admin.users') }}" class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center gap-5 hover-lift transition-all hover:border-indigo-200">
            <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-500 shrink-0">
                <i class="fa-solid fa-users-gear text-2xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-700 text-lg">Manajemen User</h3>
                <p class="text-slate-400 text-sm font-medium">Kelola akun siswa, guru, orang tua, dan admin.</p>
            </div>
            <i class="fa-solid fa-chevron-right text-slate-300 ml-auto"></i>
        </a>

        <a href="{{ route('admin.recap') }}" class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center gap-5 hover-lift transition-all hover:border-indigo-200">
            <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-500 shrink-0">
                <i class="fa-solid fa-square-poll-horizontal text-2xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-700 text-lg">Rekap Evaluasi</h3>
                <p class="text-slate-400 text-sm font-medium">Lihat peringkat dan rekap kuesioner evaluasi guru.</p>
            </div>
            <i class="fa-solid fa-chevron-right text-slate-300 ml-auto"></i>
        </a>
    </div>

</div>
@endsection