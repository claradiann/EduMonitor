@extends('layouts.app')

@section('title', 'Ekskul Binaan - EduMonitor')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="page-title">Ekstrakurikuler Binaan</h1>
        <p class="page-sub">Isi predikat A sampai D dan deskripsi untuk setiap anggota pada akhir semester {{ $semester }}.</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($ekskul as $e)
            <a href="{{ route('ekskul.show', $e) }}" class="card card-pad block hover:border-brand-300">
                <span class="w-11 h-11 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center mb-3"><i class="fa-solid fa-people-group"></i></span>
                <p class="font-bold text-slate-800">{{ $e->nama }}</p>
                <p class="text-sm text-slate-500">{{ $e->jumlah }} anggota</p>
                <div class="mt-3 h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-brand-500 rounded-full" style="width: {{ $e->jumlah ? round($e->terisi / $e->jumlah * 100) : 0 }}%"></div>
                </div>
                <p class="text-xs font-semibold text-slate-500 mt-1">{{ $e->terisi }}/{{ $e->jumlah }} nilai terisi</p>
            </a>
        @empty
            <div class="card sm:col-span-2 lg:col-span-3"><div class="empty"><i class="fa-solid fa-people-group"></i>Anda belum ditetapkan sebagai pembina ekstrakurikuler.</div></div>
        @endforelse
    </div>
</div>
@endsection