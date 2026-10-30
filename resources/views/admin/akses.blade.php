@extends('layouts.app')

@section('title', 'Buka Akses Nilai - EduMonitor')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="page-title">Buka Akses Unggah Nilai</h1>
        <p class="page-sub">Akses dibuka selama {{ $durasi }} jam setelah disetujui. Nilai yang masuk tetap tercatat terlambat beserta jamnya.</p>
    </div>

    <section class="space-y-3">
        <h2 class="card-title">Menunggu persetujuan</h2>
        @forelse($menunggu as $u)
            @php($a = $u->assessment)
            <div class="card card-pad flex flex-col md:flex-row md:items-center gap-4">
                <div class="min-w-0 flex-1">
                    <p class="font-bold text-slate-800">{{ $a->judul }} <span class="font-semibold text-slate-400">&middot; {{ $a->subjectTeacher->label() }}</span></p>
                    <p class="text-sm text-slate-500">Diajukan {{ $u->pengaju->name ?? '-' }}, {{ $u->created_at->diffForHumans() }}. Batas semula {{ $a->batasUnggah()->translatedFormat('d M Y') }}.</p>
                    <p class="text-sm text-slate-700 mt-1"><b>Alasan:</b> {{ $u->alasan }}</p>
                </div>
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('admin.akses.tolak', $u) }}">@csrf<button class="btn btn-danger">Tolak</button></form>
                    <form method="POST" action="{{ route('admin.akses.setujui', $u) }}">@csrf<button class="btn btn-primary"><i class="fa-solid fa-lock-open"></i>Buka {{ $durasi }} jam</button></form>
                </div>
            </div>
        @empty
            <div class="card"><div class="empty"><i class="fa-regular fa-circle-check"></i>Tidak ada pengajuan yang menunggu.</div></div>
        @endforelse
    </section>

    <section class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-[color:var(--line)]"><h2 class="card-title">Riwayat</h2></div>
        <div class="tbl-wrap">
            <table class="tbl">
                <thead><tr><th>Penilaian</th><th>Guru</th><th>Status</th><th>Dibuka sampai</th></tr></thead>
                <tbody>
                    @forelse($riwayat as $u)
                        <tr>
                            <td><p class="font-bold text-slate-800">{{ $u->assessment->judul }}</p><p class="text-xs text-slate-400">{{ $u->assessment->subjectTeacher->label() }}</p></td>
                            <td>{{ $u->pengaju->name ?? 'Dibuka admin' }}</td>
                            <td><span class="badge {{ $u->status === 'ditolak' ? 'badge-rose' : ($u->aktif() ? 'badge-brand' : 'badge-slate') }}">{{ $u->statusLabel() }}</span></td>
                            <td class="whitespace-nowrap">{{ $u->berakhir_at?->translatedFormat('d M Y, H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty">Belum ada riwayat.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection