@extends('layouts.app')

@section('title', 'Rekap Kinerja Guru - EduMonitor')

@php($lama = function ($menit) {
    if (!$menit) return '-';
    $hari = intdiv($menit, 1440); $jam = intdiv($menit % 1440, 60);
    return trim(($hari ? $hari . ' hari ' : '') . ($jam ? $jam . ' jam' : ($hari ? '' : '< 1 jam')));
})

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="page-title">Rekap Kinerja Guru</h1>
        <p class="page-sub">Semester {{ $semester }}. Hanya dapat dilihat admin.</p>
    </div>

    <div class="rounded-xl bg-brand-50 border border-brand-100 px-4 py-3 text-sm text-brand-800">
        Skor evaluasi berasal dari penilaian siswa (skala 1 sampai 5). Keterlambatan unggah nilai, buka akses, dan laporan insidental dicatat terpisah sebagai bahan penilaian dari sisi manajemen sekolah.
    </div>

    <section class="card overflow-hidden">
        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Guru</th>
                        <th class="num">Skor evaluasi</th>
                        <th class="num">Penilaian</th>
                        <th class="num">Terlambat unggah</th>
                        <th class="num">Total keterlambatan</th>
                        <th class="num">Buka akses</th>
                        <th class="num">Laporan insidental</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($guru as $g)
                        <tr>
                            <td>
                                <p class="font-bold text-slate-800 whitespace-nowrap">{{ $g['name'] }}</p>
                                <p class="text-xs text-slate-400">
                                    @foreach($g['kelas'] as $k){{ $k['label'] }}: {{ $k['rata'] !== null ? str_replace('.', ',', $k['rata']) : '-' }}@if(!$loop->last) &middot; @endif @endforeach
                                </p>
                            </td>
                            <td class="num">
                                <span class="font-extrabold text-slate-800">{{ $g['skor'] !== null ? str_replace('.', ',', $g['skor']) : '-' }}</span>
                                <span class="block text-xs text-slate-400">{{ $g['responden'] }} responden</span>
                            </td>
                            <td class="num">{{ $g['penilaian'] }}</td>
                            <td class="num"><span class="{{ $g['terlambat'] ? 'badge badge-rose' : 'text-slate-400' }}">{{ $g['terlambat'] }}</span></td>
                            <td class="num whitespace-nowrap">{{ $lama($g['lama_terlambat']) }}</td>
                            <td class="num">{{ $g['buka_akses'] ?: '-' }}</td>
                            <td class="num">{{ $g['laporan'] ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="px-5 py-3 text-xs text-slate-400 border-t border-[color:var(--line)]">Angka di bawah nama guru adalah rata-rata nilai akhir siswa per kelas yang diampu.</p>
    </section>

    <section class="card card-pad">
        <h2 class="card-title mb-4">Rata-rata per aspek evaluasi (seluruh guru)</h2>
        <div class="space-y-3">
            @foreach($indicators as $i)
                <div class="flex items-center gap-3">
                    <span class="w-48 sm:w-64 text-sm font-semibold text-slate-600 shrink-0">{{ $i['name'] }}</span>
                    <div class="flex-1 h-2.5 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full bg-brand-500" style="width: {{ $i['average'] / 5 * 100 }}%"></div></div>
                    <span class="w-10 text-right font-bold tabular-nums">{{ $i['average'] ? str_replace('.', ',', $i['average']) : '-' }}</span>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection