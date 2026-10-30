@extends('layouts.app')

@section('title', 'Nilai - EduMonitor')

@php($fmt = fn ($v) => $v === null ? '-' : rtrim(rtrim(number_format($v, 1, ',', ''), '0'), ','))

@section('content')
@if(!$student)
    @include('partials.tanpa-anak')
@else
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="page-title">Nilai {{ Auth::user()->role === 'orang_tua' ? $student->name : 'Saya' }}</h1>
            <p class="page-sub">Kelas {{ $student->kelas->nama_kelas ?? '-' }} &middot; Semester {{ $semester }}</p>
        </div>
        <div class="card px-4 py-3 flex items-center gap-3">
            <span class="stat-label">Rata-rata nilai akhir</span>
            <span class="text-2xl font-extrabold text-brand-700">{{ $rataNilai !== null ? str_replace('.', ',', $rataNilai) : '-' }}</span>
        </div>
    </div>

    <div class="rounded-xl bg-brand-50 border border-brand-100 px-4 py-3 text-sm text-brand-800">
        <b>Cara hitung:</b> nilai tiap bab adalah rata-rata tugas, kuis, dan ulangan harian di bab itu.
        Nilai harian adalah rata-rata semua bab. Nilai akhir = (3 &times; nilai harian + UTS + UAS) &divide; 5, dibulatkan.
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        @foreach($mapel as $m)
            @php($g = $m['grade'])
            <article class="card card-pad flex flex-col gap-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="font-bold text-slate-800 text-[16px]">{{ $m['st']->subject->nama_mapel }}</h2>
                        <p class="text-sm text-slate-500">{{ $m['st']->teacher->name }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-3xl font-extrabold tabular-nums {{ $g?->average ? 'text-slate-800' : 'text-slate-300' }}">{{ $g?->average ?: '-' }}</p>
                        @if($g && $g->average && $g->sementara)
                            <span class="badge badge-amber !text-[11px] !px-2 !py-0.5">sementara</span>
                        @endif
                    </div>
                </div>

                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nilai per bab</p>
                    @if(count($m['bab']))
                        <div class="flex flex-wrap gap-2">
                            @foreach($m['bab'] as $bab => $nilai)
                                <span class="inline-flex items-center gap-2 rounded-lg border border-[color:var(--line)] bg-slate-50 px-2.5 py-1.5 text-sm">
                                    <span class="text-slate-500 font-semibold">Bab {{ $bab }}</span>
                                    <b class="tabular-nums text-slate-800">{{ $fmt($nilai) }}</b>
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-400">Belum ada penilaian harian.</p>
                    @endif
                </div>

                <dl class="grid grid-cols-3 gap-2 text-center mt-auto">
                    <div class="rounded-xl bg-slate-50 py-2">
                        <dt class="text-[11px] font-bold text-slate-400 uppercase">Harian</dt>
                        <dd class="font-extrabold text-slate-700 tabular-nums">{{ $g && $g->harian ? $fmt($g->harian) : '-' }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 py-2">
                        <dt class="text-[11px] font-bold text-slate-400 uppercase">UTS</dt>
                        <dd class="font-extrabold text-slate-700 tabular-nums">{{ $g?->nilai_uts ?: '-' }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 py-2">
                        <dt class="text-[11px] font-bold text-slate-400 uppercase">UAS</dt>
                        <dd class="font-extrabold text-slate-700 tabular-nums">{{ $g?->nilai_uas ?: '-' }}</dd>
                    </div>
                </dl>
            </article>
        @endforeach
    </div>

    <section class="card card-pad">
        <h2 class="card-title mb-3">Ekstrakurikuler</h2>
        @forelse($ekskul as $e)
            <div class="flex items-start gap-4 py-3 border-b border-slate-100 last:border-0">
                <span class="w-12 h-12 shrink-0 rounded-xl flex items-center justify-center text-xl font-extrabold {{ $e->predikat ? 'bg-brand-50 text-brand-700' : 'bg-slate-100 text-slate-300' }}">{{ $e->predikat ?? '-' }}</span>
                <div class="min-w-0">
                    <p class="font-bold text-slate-700">{{ $e->ekstrakurikuler->nama }}</p>
                    <p class="text-sm text-slate-500">{{ $e->deskripsi ?: 'Nilai diisi pembina pada akhir semester.' }}</p>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-400">Tidak terdaftar di ekstrakurikuler semester ini.</p>
        @endforelse
    </section>

    <div class="grid lg:grid-cols-2 gap-6">
        <section class="card card-pad">
            <h2 class="card-title mb-3">Catatan guru</h2>
            @forelse($catatan as $c)
                <div class="py-3 border-b border-slate-100 last:border-0">
                    <p class="text-sm font-bold text-slate-700">{{ $c->subject->nama_mapel }} <span class="font-semibold text-slate-400">&middot; {{ $c->teacher->name }}</span></p>
                    <p class="text-sm text-slate-600 mt-1 whitespace-pre-line">{{ $c->catatan }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $c->created_at->translatedFormat('d F Y') }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-400">Belum ada catatan.</p>
            @endforelse
        </section>

        <section class="card card-pad">
            <h2 class="card-title mb-3">Rata-rata per semester</h2>
            @forelse($riwayat as $smt => $rata)
                <div class="flex items-center gap-3 py-2">
                    <span class="w-36 text-sm font-semibold text-slate-600 shrink-0">{{ $smt }}</span>
                    <div class="flex-1 h-2.5 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full bg-brand-500" style="width: {{ min(100, $rata) }}%"></div></div>
                    <span class="w-10 text-right font-bold tabular-nums">{{ str_replace('.', ',', $rata) }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-400">Belum ada data.</p>
            @endforelse
        </section>
    </div>
</div>
@endif
@endsection