@extends('layouts.app')

@section('title', 'Lapor Kejadian - EduMonitor')

@section('content')
<div class="space-y-6 max-w-2xl">
    <div>
        <h1 class="page-title">Lapor Kejadian di Kelas</h1>
        <p class="page-sub">Untuk kejadian hari ini, misalnya jam pelajaran kosong tanpa keterangan. Identitasmu tidak ditampilkan kepada guru.</p>
    </div>

    <form method="POST" action="{{ route('siswa.lapor.store') }}" class="card card-pad space-y-4">
        @csrf
        <div>
            <p class="label">Tanggal</p>
            <p class="font-semibold text-slate-700">{{ today()->translatedFormat('l, d F Y') }}</p>
        </div>
        <div>
            <label class="label" for="l-mapel">Mata pelajaran</label>
            <select id="l-mapel" name="subject_teacher_id" class="input" required>
                <option value="">Pilih mata pelajaran</option>
                @foreach($mapel as $st)
                    <option value="{{ $st->id }}" @selected(old('subject_teacher_id') == $st->id)>{{ $st->subject->nama_mapel }} &middot; {{ $st->teacher->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="l-jenis">Jenis kejadian</label>
            <select id="l-jenis" name="jenis" class="input" required>
                @foreach(\App\Models\IncidentReport::JENIS as $k => $label)
                    <option value="{{ $k }}" @selected(old('jenis') == $k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="l-ket">Keterangan (boleh dikosongkan)</label>
            <textarea id="l-ket" name="keterangan" rows="3" maxlength="500" class="input" placeholder="Contoh: jam ke-3 dan ke-4 tidak ada guru dan tidak ada tugas.">{{ old('keterangan') }}</textarea>
        </div>
        <button class="btn btn-primary w-full"><i class="fa-solid fa-paper-plane"></i>Kirim laporan</button>
        <p class="hint text-center">Laporan ini tidak memengaruhi nilai evaluasi guru di akhir semester.</p>
    </form>

    <section class="card card-pad">
        <h2 class="card-title mb-3">Laporan saya</h2>
        @forelse($riwayat as $r)
            <div class="py-3 border-b border-slate-100 last:border-0 text-sm">
                <p class="font-bold text-slate-700">{{ $r->subjectTeacher->subject->nama_mapel }} <span class="font-semibold text-slate-400">&middot; {{ $r->tanggal->translatedFormat('d M Y') }}</span></p>
                <p class="text-slate-500">{{ $r->jenisLabel() }}@if($r->keterangan): {{ $r->keterangan }}@endif</p>
            </div>
        @empty
            <p class="text-sm text-slate-400">Belum ada laporan.</p>
        @endforelse
    </section>
</div>
@endsection