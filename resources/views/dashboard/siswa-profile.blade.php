@extends('layouts.app')

@section('title', 'Profile - EduMonitor')

@section('content')
<div class="space-y-8">

    <!-- Header -->
    <div>
        <h2 class="text-3xl font-extrabold tracking-tight text-slate-800">Profile Saya</h2>
        <p class="text-slate-400 text-sm mt-1 font-medium">Informasi akun dan data pribadi</p>
    </div>

    <!-- Profile Card -->
    <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm">
        <div class="flex items-center gap-5 mb-8">
            <div class="w-20 h-20 bg-brand-100 rounded-full flex items-center justify-center text-brand-600 font-extrabold text-2xl uppercase">
                {{ substr($user->name, 0, 2) }}
            </div>
            <div>
                <h3 class="text-2xl font-extrabold text-slate-800">{{ $user->name }}</h3>
                <span class="inline-block mt-1 bg-indigo-50 text-indigo-600 text-xs font-bold uppercase px-3 py-1 rounded-full tracking-wider">
                    Siswa
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Lengkap</p>
                <p class="font-bold text-slate-700">{{ $user->name }}</p>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">NISN</p>
                <p class="font-bold text-slate-700">{{ $user->nisn ?? '-' }}</p>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Username</p>
                <p class="font-bold text-slate-700">{{ $user->username }}</p>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Email</p>
                <p class="font-bold text-slate-700">{{ $user->email ?? '-' }}</p>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Kelas</p>
                <p class="font-bold text-slate-700">{{ $kelas->nama_kelas ?? '-' }}</p>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Bergabung Sejak</p>
                <p class="font-bold text-slate-700">{{ $user->created_at?->format('d M Y') ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection