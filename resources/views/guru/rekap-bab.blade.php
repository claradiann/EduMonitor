@extends('layouts.app')

@section('title', 'Rekap per Bab - EduMonitor')

@php($fmt = fn ($v) => $v === null ? '-' : rtrim(rtrim(number_format($v, 1, ',', ''), '0'), ','))

@section('content')
<div class="space-y-6">
    <a href="{{ route('guru.penilaian', ['kelas' => $subjectTeacher->id]) }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-brand-700"><i class="fa-solid fa-arrow-left"></i>Kembali ke penilaian</a>

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="page-title">Rekap Nilai per Bab</h1>
            <p class="page-sub">{{ $subjectTeacher->label() }} &middot; Semester {{ $semester }}</p>
        </div>
        <a href="{{ route('guru.ekspor', $subjectTeacher) }}" class="btn btn-soft"><i class="fa-solid fa-file-export"></i>Ekspor nilai akhir</a>
    </div>

    <div class="card overflow-hidden">
        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Siswa</th>
                        @foreach($babDipakai as $bab)<th class="num">Bab {{ $bab }}</th>@endforeach
                        <th class="num">Harian</th><th class="num">UTS</th><th class="num">UAS</th><th class="num">Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($baris as $b)
                        @php($g = $b['grade'])
                        <tr>
                            <td class="font-bold text-slate-800 whitespace-nowrap">{{ $b['siswa']->name }}</td>
                            @foreach($babDipakai as $bab)
                                @php($v = $b['bab'][$bab] ?? null)
                                <td class="num">{{ $fmt($v) }}</td>
                            @endforeach
                            <td class="num font-semibold">{{ $g && $g->harian ? $fmt($g->harian) : '-' }}</td>
                            <td class="num">{{ $g?->nilai_uts ?: '-' }}</td>
                            <td class="num">{{ $g?->nilai_uas ?: '-' }}</td>
                            <td class="num font-extrabold text-brand-700">{{ $g?->average ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                @if($babDipakai->isNotEmpty())
                    <tfoot>
                        <tr class="bg-slate-50">
                            <td class="font-bold text-slate-500">Rata-rata kelas</td>
                            @foreach($babDipakai as $bab)<td class="num font-bold">{{ $fmt($rataBab[$bab]) }}</td>@endforeach
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        @if($babDipakai->isEmpty())
            <div class="empty"><i class="fa-regular fa-file-lines"></i>Belum ada nilai harian yang masuk.</div>
        @endif
    </div>
    <p class="text-sm text-slate-500">Bab yang belum punya penilaian tidak ditampilkan dan tidak ikut dihitung.</p>
</div>
@endsection