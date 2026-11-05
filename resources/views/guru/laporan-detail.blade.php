@extends('layouts.app')

@section('title', $laporan->judul . ' - EduMonitor')

@section('content')
@php
    $fmt = fn ($v) => $v === null ? '-' : rtrim(rtrim(number_format($v, 1, ',', ''), '0'), ',');
    [$baca, $total] = $laporan->keterbacaan();
    $admin = Auth::user()->role === 'admin';
@endphp
<div class="space-y-6">
    <div>
        <a href="{{ $admin ? route('admin.laporan-perkembangan') : route('guru.laporan') }}" class="section-link"><i class="fa-solid fa-arrow-left mr-1 text-xs"></i>Semua laporan</a>
        <h1 class="page-title mt-2">{{ $laporan->judul }}</h1>
        <p class="page-sub">
            {{ $laporan->subjectTeacher->label() }} &middot; {{ $laporan->pengirim->name ?? $laporan->subjectTeacher->teacher->name }}
            &middot; dikirim {{ $laporan->created_at->translatedFormat('l, d F Y H:i') }}
        </p>
    </div>

    <div class="grid sm:grid-cols-3 gap-3">
        <div class="card card-pad sm:col-span-1">
            <p class="stat-label">Orang tua sudah membaca</p>
            <p class="stat-num mt-1">{{ $baca }} <span class="text-base font-semibold text-slate-500">dari {{ $total }}</span></p>
            <div class="h-2 mt-3 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full bg-nila-600" style="width: {{ $total ? round($baca / $total * 100) : 0 }}%"></div></div>
        </div>
        <div class="card card-pad sm:col-span-2">
            <p class="stat-label">Pesan untuk semua orang tua</p>
            <p class="text-slate-800 mt-1">{{ $laporan->pesan ?: 'Tidak ada pesan umum.' }}</p>
        </div>
    </div>

    <section class="card overflow-hidden">
        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr><th>Siswa</th><th class="num">{{ $laporan->siswa->first()['isi']['utama']['label'] ?? 'Nilai' }}</th><th>Catatan guru</th><th>Status baca orang tua</th></tr>
                </thead>
                <tbody>
                    @foreach($laporan->siswa as $ls)
                        <tr class="align-top">
                            <td class="font-bold text-slate-900 whitespace-nowrap">{{ $ls->student->name }}</td>
                            <td class="num font-extrabold">{{ $fmt($ls->isi['utama']['nilai']) }}</td>
                            <td class="text-slate-700 min-w-[220px]">{{ $ls->catatan ?: '-' }}</td>
                            <td class="whitespace-nowrap">
                                @forelse($ls->student->parents as $p)
                                    @php($d = $ls->dibacaOleh($p->id))
                                    <p class="status {{ $d ? 'status-nilai_tersedia' : 'status-menunggu_nilai' }} !font-semibold">
                                        <span class="titik {{ $d ? 'titik-nilai_tersedia' : 'titik-dijadwalkan' }}" style="{{ $d ? '' : '--c: var(--soga-600)' }}"></span>
                                        {{ $p->name }}: {{ $d ? 'dibaca ' . $d->dibaca_at->translatedFormat('d M, H:i') : 'belum dibaca' }}
                                    </p>
                                @empty
                                    <span class="text-sm text-slate-500">Belum ada akun orang tua</span>
                                @endforelse
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection