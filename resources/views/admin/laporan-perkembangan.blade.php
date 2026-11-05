@extends('layouts.app')

@section('title', 'Laporan ke Orang Tua - EduMonitor')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="page-title">Laporan ke Orang Tua</h1>
        <p class="page-sub">Laporan perkembangan belajar yang dikirim guru semester ini, beserta berapa orang tua yang sudah membacanya.</p>
    </div>

    <section class="card rows overflow-hidden">
        @forelse($laporan as $l)
            @php([$baca, $total] = $l->keterbacaan())
            <a href="{{ route('guru.laporan.show', $l) }}" class="row">
                <span class="ikon-judul !mr-0 shrink-0"><i class="fa-solid fa-file-lines"></i></span>
                <span class="min-w-0 flex-1">
                    <span class="block font-bold text-slate-900 truncate">{{ $l->judul }}</span>
                    <span class="block text-sm text-slate-600">{{ $l->subjectTeacher->teacher->name }} &middot; Kelas {{ $l->subjectTeacher->kelas->nama_kelas }} &middot; {{ $l->created_at->translatedFormat('d M Y') }}</span>
                </span>
                @include('partials.keterbacaan', ['baca' => $baca, 'total' => $total])
            </a>
        @empty
            <div class="empty"><i class="fa-regular fa-envelope"></i>Belum ada laporan yang dikirim guru semester ini.</div>
        @endforelse
    </section>
</div>
@endsection