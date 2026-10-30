@extends('layouts.app')

@section('title', 'Jadwal Penilaian - EduMonitor')

@section('content')
@if(!$student)
    @include('partials.tanpa-anak')
@else
<div class="space-y-6">
    <div>
        <h1 class="page-title">Jadwal Penilaian</h1>
        <p class="page-sub">{{ $student->name }} &middot; Kelas {{ $student->kelas->nama_kelas ?? '-' }}</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach(\App\Models\Assessment::STATUS as $key => $s)
            <div class="card px-4 py-3">
                <p class="stat-num !text-2xl">{{ $ringkasan[$key] ?? 0 }}</p>
                <span class="badge {{ $s['class'] }} mt-1 !whitespace-normal"><i class="fa-solid {{ $s['icon'] }} text-[11px]"></i>{{ $s['label'] }}</span>
            </div>
        @endforeach
    </div>

    <div class="flex gap-2">
        <a href="{{ route('jadwal') }}" class="btn btn-sm {{ $filter !== 'semua' ? 'btn-primary' : 'btn-light' }}">Belum ada nilai</a>
        <a href="{{ route('jadwal', ['tampil' => 'semua']) }}" class="btn btn-sm {{ $filter === 'semua' ? 'btn-primary' : 'btn-light' }}">Semua</a>
    </div>

    @forelse($grup as $tgl => $items)
        @php($t = \Illuminate\Support\Carbon::parse($tgl))
        <section>
            <h2 class="text-sm font-bold text-slate-500 mb-2">
                {{ $t->translatedFormat('l, d F Y') }}
                @if($t->isToday())<span class="badge badge-brand ml-1">Hari ini</span>@endif
            </h2>
            <div class="card divide-y divide-slate-100">
                @foreach($items as $p)
                    @php($a = $p['model'])
                    <div class="p-4 flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-800">{{ $a->judul }}</p>
                            <p class="text-sm text-slate-500">{{ $a->subjectTeacher->subject->nama_mapel }} &middot; {{ $a->labelJenis() }} &middot; {{ $a->subjectTeacher->teacher->name }}</p>
                            @if($p['susulan'])
                                <p class="text-sm text-violet-700 mt-1"><i class="fa-solid fa-user-clock mr-1"></i>Susulan. Jadwal kelas {{ $a->tanggal->translatedFormat('d F') }}.</p>
                            @elseif($p['diubah'])
                                <p class="text-sm text-amber-700 mt-1"><i class="fa-solid fa-calendar-days mr-1"></i>Jadwal diubah dari {{ $p['diubah']->tanggal_lama->translatedFormat('d F') }}. {{ $p['diubah']->alasan }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 sm:justify-end">
                            @include('partials.status-penilaian', ['status' => $p['status']])
                            @if($p['nilai'] !== null)
                                <span class="text-xl font-extrabold tabular-nums text-slate-800">{{ rtrim(rtrim(number_format($p['nilai'], 1, ',', ''), '0'), ',') }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @empty
        <div class="card"><div class="empty"><i class="fa-regular fa-calendar-check"></i>Tidak ada penilaian yang menunggu. Semua nilai sudah keluar.</div></div>
    @endforelse
</div>
@endif
@endsection