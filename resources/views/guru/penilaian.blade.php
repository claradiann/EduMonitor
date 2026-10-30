@extends('layouts.app')

@section('title', 'Penilaian - EduMonitor')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="page-title">Penilaian</h1>
        <p class="page-sub">Jadwalkan tugas, kuis, dan ulangan harian per bab. Siswa dan orang tua langsung mendapat notifikasi.</p>
    </div>

    @if($kelasMapel->isEmpty())
        <div class="card"><div class="empty"><i class="fa-solid fa-chalkboard"></i>Anda belum mengampu kelas.</div></div>
    @else
    <div class="flex gap-2 overflow-x-auto pb-1 -mx-1 px-1">
        @foreach($kelasMapel as $km)
            <a href="{{ route('guru.penilaian', ['kelas' => $km['st']->id]) }}"
               class="shrink-0 rounded-xl border px-4 py-2.5 text-sm font-bold {{ $aktif['st']->id === $km['st']->id ? 'bg-brand-600 border-brand-600 text-white' : 'bg-white border-[color:var(--line)] text-slate-600 hover:border-brand-300' }}">
                {{ $km['st']->subject->nama_mapel }} &middot; {{ $km['st']->kelas->nama_kelas }}
                @if($km['terlambat'])<span class="ml-1 inline-block w-2 h-2 rounded-full bg-rose-500 align-middle"></span>@endif
            </a>
        @endforeach
    </div>

    @php($st = $aktif['st'])
    <div class="grid lg:grid-cols-3 gap-6 items-start">
        <section class="card lg:col-span-2 overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-[color:var(--line)]">
                <div>
                    <h2 class="card-title">{{ $st->subject->nama_mapel }} &middot; {{ $st->kelas->nama_kelas }}</h2>
                    <p class="card-sub">{{ $aktif['jumlah_siswa'] }} siswa &middot; {{ $aktif['penilaian']->count() }} penilaian</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('guru.rekap-bab', $st) }}" class="btn btn-light btn-sm"><i class="fa-solid fa-table-cells"></i>Rekap per bab</a>
                    <a href="{{ route('guru.ekspor', $st) }}" class="btn btn-soft btn-sm"><i class="fa-solid fa-file-export"></i>Ekspor nilai</a>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($aktif['penilaian']->sortBy(fn ($p) => $p['model']->tanggal) as $p)
                    @php($a = $p['model'])
                    <a href="{{ route('guru.penilaian.input', $a) }}" class="flex flex-wrap items-center gap-3 px-5 py-3.5 hover:bg-slate-50">
                        @include('partials.datechip', ['tanggal' => $a->tanggal])
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-800">{{ $a->judul }}</p>
                            <p class="text-sm text-slate-500">{{ $a->labelJenis() }} &middot; {{ $p['dinilai'] }}/{{ $aktif['jumlah_siswa'] }} dinilai
                                @if($a->makeups->count()) &middot; {{ $a->makeups->count() }} susulan @endif
                            </p>
                        </div>
                        @include('partials.status-penilaian', ['status' => $p['status']])
                    </a>
                @empty
                    <div class="empty"><i class="fa-regular fa-calendar-plus"></i>Belum ada penilaian. Jadwalkan lewat formulir di samping.</div>
                @endforelse
            </div>
        </section>

        <section class="card card-pad lg:sticky lg:top-24">
            <h2 class="card-title">Jadwalkan penilaian</h2>
            <p class="card-sub mb-4">Untuk kuis dadakan, pilih tanggal hari ini.</p>
            <form method="POST" action="{{ route('guru.penilaian.store', $st) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="jenis">Jenis</label>
                        <select id="jenis" name="jenis" class="input" required>
                            @foreach(\App\Models\Assessment::JENIS_HARIAN as $j)
                                <option value="{{ $j }}" @selected(old('jenis') === $j)>{{ \App\Models\Assessment::JENIS[$j] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="bab">Bab</label>
                        <select id="bab" name="bab" class="input" required>
                            @for($b = 1; $b <= \App\Models\Assessment::MAKS_BAB; $b++)
                                <option value="{{ $b }}" @selected(old('bab') == $b)>Bab {{ $b }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div>
                    <label class="label" for="judul">Judul</label>
                    <input id="judul" name="judul" class="input" required maxlength="100" value="{{ old('judul') }}" placeholder="Contoh: Kuis Pecahan">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="tanggal">Tanggal</label>
                        <input id="tanggal" type="date" name="tanggal" class="input" required min="{{ today()->toDateString() }}" value="{{ old('tanggal', today()->addDays(3)->toDateString()) }}">
                    </div>
                    <div>
                        <label class="label" for="skor_maks">Skor maksimal</label>
                        <input id="skor_maks" type="number" name="skor_maks" class="input" required min="1" max="1000" step="any" value="{{ old('skor_maks', 100) }}">
                    </div>
                </div>
                <p class="hint">Skor dikonversi otomatis ke skala 0 sampai 100. Nilai bisa diinput sampai {{ $batasHari }} hari setelah tanggal penilaian.</p>
                <button class="btn btn-primary w-full"><i class="fa-solid fa-paper-plane"></i>Jadwalkan & kirim notifikasi</button>
            </form>
        </section>
    </div>
    @endif
</div>
@endsection