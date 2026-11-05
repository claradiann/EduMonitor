@extends('layouts.app')

@section('title', $ls->laporan->judul . ' - EduMonitor')

@section('content')
<div class="space-y-6 max-w-3xl">
    <div>
        <a href="{{ route('laporan') }}" class="section-link"><i class="fa-solid fa-arrow-left mr-1 text-xs"></i>Semua laporan</a>
        <h1 class="page-title mt-2">{{ $ls->laporan->judul }}</h1>
        <p class="page-sub">
            {{ $ls->student->name }} &middot; Kelas {{ $ls->laporan->subjectTeacher->kelas->nama_kelas }}
            &middot; dari {{ $ls->laporan->pengirim->name ?? 'guru' }}, {{ $ls->laporan->created_at->translatedFormat('d F Y') }}
        </p>
    </div>

    @if($ls->laporan->pesan || $ls->catatan)
        <section class="card card-pad space-y-4">
            @if($ls->laporan->pesan)
                <div>
                    <p class="text-sm font-bold text-slate-600"><i class="fa-solid fa-bullhorn mr-1.5 text-nila-500"></i>Pesan guru untuk semua orang tua</p>
                    <p class="text-slate-800 mt-1 leading-relaxed">{{ $ls->laporan->pesan }}</p>
                </div>
            @endif
            @if($ls->catatan)
                <div class="rounded-[10px] bg-soga-50 border border-soga-100 px-4 py-3">
                    <p class="text-sm font-bold text-soga-700"><i class="fa-solid fa-pen-nib mr-1.5"></i>Catatan khusus untuk {{ strtok($ls->student->name, ' ') }}</p>
                    <p class="text-slate-900 mt-1 leading-relaxed">{{ $ls->catatan }}</p>
                </div>
            @endif
        </section>
    @endif

    <section class="card card-pad">
        @include('partials.isi-laporan', ['isi' => $ls->isi])
    </section>

    <p class="hint">Nilai pada laporan ini sesuai keadaan saat laporan dikirim. Nilai terbaru selalu ada di halaman <a href="{{ route('nilai') }}" class="section-link !text-[12.5px]">Nilai</a>.</p>
</div>
@endsection