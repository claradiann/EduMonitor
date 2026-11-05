@extends('layouts.app')

@section('title', 'Laporan ke Orang Tua - EduMonitor')

@section('content')
<div class="space-y-7">
    <div>
        <h1 class="page-title">Laporan ke Orang Tua</h1>
        <p class="page-sub">Kirim laporan perkembangan belajar per bab atau ringkasan semester. Nilai dan rata-rata kelas tersusun otomatis, Anda cukup menambah catatan bila perlu.</p>
    </div>

    <section>
        <div class="section-head">
            <h2 class="section-title"><span class="ikon-judul"><i class="fa-solid fa-paper-plane"></i></span>Buat laporan baru</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @forelse($kelasMapel as $st)
                <a href="{{ route('guru.laporan.buat', $st) }}" class="card card-pad flex items-center justify-between gap-3 hover:border-nila-300">
                    <span>
                        <span class="block font-bold text-slate-900">{{ $st->subject->nama_mapel }}</span>
                        <span class="block text-sm text-slate-600">Kelas {{ $st->kelas->nama_kelas }}</span>
                    </span>
                    <span class="btn btn-soft btn-sm">Susun<i class="fa-solid fa-arrow-right text-xs"></i></span>
                </a>
            @empty
                <div class="card"><div class="empty"><i class="fa-regular fa-folder-open"></i>Anda belum mengampu kelas.</div></div>
            @endforelse
        </div>
    </section>

    <section>
        <div class="section-head">
            <h2 class="section-title"><span class="ikon-judul soga"><i class="fa-solid fa-envelope-open-text"></i></span>Laporan terkirim</h2>
        </div>
        <div class="card rows overflow-hidden">
            @forelse($laporan as $l)
                @php([$baca, $total] = $l->keterbacaan())
                <a href="{{ route('guru.laporan.show', $l) }}" class="row">
                    <span class="ikon-judul !mr-0 shrink-0"><i class="fa-solid fa-file-lines"></i></span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-bold text-slate-900 truncate">{{ $l->judul }}</span>
                        <span class="block text-sm text-slate-600">Kelas {{ $l->subjectTeacher->kelas->nama_kelas }} &middot; dikirim {{ $l->created_at->translatedFormat('d M Y, H:i') }}</span>
                    </span>
                    @include('partials.keterbacaan', ['baca' => $baca, 'total' => $total])
                </a>
            @empty
                <div class="empty"><i class="fa-regular fa-envelope"></i>Belum ada laporan yang dikirim.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection