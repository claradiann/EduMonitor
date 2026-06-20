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
                    Guru
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Lengkap</p>
                <p class="font-bold text-slate-700">{{ $user->name }}</p>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">NIP</p>
                <p class="font-bold text-slate-700">{{ $user->nip ?? '-' }}</p>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Username</p>
                <p class="font-bold text-slate-700">{{ $user->username }}</p>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Email</p>
                <p class="font-bold text-slate-700">{{ $user->email ?? '-' }}</p>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 md:col-span-2">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Bergabung Sejak</p>
                <p class="font-bold text-slate-700">{{ $user->created_at?->format('d M Y') ?? '-' }}</p>
            </div>
        </div>
    </div>

    <!-- Mapel & Kelas yang Diampu -->
    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
        <h3 class="text-base font-bold text-slate-600 uppercase tracking-wider mb-4">
            <i class="fa-solid fa-chalkboard-user mr-2 text-indigo-500"></i>Mata Pelajaran & Kelas yang Diampu
        </h3>
        <div class="space-y-3">
            @forelse($subjectTeachers as $st)
                <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-100 rounded-2xl">
                    <div>
                        <h4 class="font-bold text-slate-700 text-sm">{{ $st->subject->nama_mapel ?? $st->subject->kode_mapel }}</h4>
                        <p class="text-xs text-slate-400 font-medium">Kelas {{ $st->kelas->nama_kelas ?? '-' }}</p>
                    </div>
                </div>
            @empty
                <p class="text-slate-400 text-sm font-medium">Belum ada mata pelajaran yang diampu.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection