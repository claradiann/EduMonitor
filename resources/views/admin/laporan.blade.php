@extends('layouts.app')

@section('title', 'Laporan Insidental - EduMonitor')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="page-title">Laporan Insidental</h1>
        <p class="page-sub">Laporan kejadian dari siswa. Identitas pelapor tidak ditampilkan dan laporan tidak memengaruhi skor evaluasi guru.</p>
    </div>

    <section class="card overflow-hidden">
        <div class="tbl-wrap">
            <table class="tbl">
                <thead><tr><th>Tanggal</th><th>Guru</th><th>Mapel &middot; Kelas</th><th>Kejadian</th><th>Keterangan</th></tr></thead>
                <tbody>
                    @forelse($laporan as $l)
                        <tr class="align-top">
                            <td class="whitespace-nowrap">{{ $l->tanggal->translatedFormat('d M Y') }}</td>
                            <td class="font-bold text-slate-800 whitespace-nowrap">{{ $l->subjectTeacher->teacher->name }}</td>
                            <td class="whitespace-nowrap">{{ $l->subjectTeacher->label() }}</td>
                            <td><span class="badge badge-amber">{{ $l->jenisLabel() }}</span></td>
                            <td class="text-slate-600">{{ $l->keterangan ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty"><i class="fa-regular fa-flag"></i>Belum ada laporan.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection