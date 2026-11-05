@extends('layouts.app')

@section('title', 'Laporan dari Guru - EduMonitor')

@section('content')
@if(!$student)
    @include('partials.tanpa-anak')
@else
@php($ortu = Auth::user()->role === 'orang_tua')
<div class="space-y-6">
    <div>
        <h1 class="page-title">Laporan dari Guru</h1>
        <p class="page-sub">Laporan perkembangan belajar {{ $ortu ? $student->name : 'kamu' }} yang dikirim guru mata pelajaran.</p>
    </div>

    <section class="card rows overflow-hidden">
        @forelse($laporan as $ls)
            @php($baru = $ortu && !$ls->dibacaOleh(Auth::id()))
            <a href="{{ route('laporan.show', $ls) }}" class="row {{ $baru ? 'bg-soga-50/60' : '' }}">
                <span class="ikon-judul {{ $baru ? 'soga' : '' }} !mr-0 shrink-0"><i class="fa-solid fa-file-lines"></i></span>
                <span class="min-w-0 flex-1">
                    <span class="block font-bold text-slate-900">{{ $ls->laporan->judul }}</span>
                    <span class="block text-sm text-slate-600">{{ $ls->laporan->pengirim->name ?? '-' }} &middot; {{ $ls->laporan->created_at->translatedFormat('d M Y') }}</span>
                </span>
                @if($baru)
                    <span class="badge badge-amber">Baru</span>
                @endif
                <i class="fa-solid fa-chevron-right text-slate-300"></i>
            </a>
        @empty
            <div class="empty"><i class="fa-regular fa-envelope"></i>Belum ada laporan dari guru.</div>
        @endforelse
    </section>
</div>
@endif
@endsection