@extends('layouts.app')

@section('title', $assessment->judul . ' - EduMonitor')

@php($st = $assessment->subjectTeacher)

@section('content')
<div class="space-y-6">
    <a href="{{ route('admin.jadwal') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-brand-700"><i class="fa-solid fa-arrow-left"></i>Kembali ke jadwal</a>

    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="page-title">{{ $assessment->judul }}</h1>
            <p class="page-sub">{{ $st->label() }} &middot; {{ $st->teacher->name }} &middot; {{ $assessment->labelJenis() }}</p>
        </div>
        @include('partials.status-penilaian', ['status' => $status])
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="card px-4 py-3"><p class="stat-label">Tanggal</p><p class="font-bold">{{ $assessment->tanggal->translatedFormat('d M Y') }}</p></div>
        <div class="card px-4 py-3"><p class="stat-label">Batas input</p><p class="font-bold">{{ $assessment->batasUnggah()->translatedFormat('d M Y') }}</p></div>
        <div class="card px-4 py-3"><p class="stat-label">Dinilai</p><p class="font-bold">{{ $assessment->scores->count() }}/{{ $siswa->count() }}</p></div>
        <div class="card px-4 py-3"><p class="stat-label">Susulan</p><p class="font-bold">{{ $assessment->makeups->count() }} siswa</p></div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 items-start">
        <section class="card card-pad">
            <h2 class="card-title">Ubah tanggal</h2>
            <p class="card-sub mb-3">Siswa dan orang tua di kelas ini mendapat notifikasi. Batas input ikut bergeser.</p>
            @if($assessment->scores->isNotEmpty())
                <p class="text-sm text-slate-500">Nilai sudah masuk, jadi tanggal tidak bisa diubah. Gunakan susulan untuk siswa yang belum ikut.</p>
            @else
                <form method="POST" action="{{ route('admin.jadwal.tanggal', $assessment) }}" class="space-y-3">
                    @csrf @method('PATCH')
                    <input type="date" name="tanggal" class="input" required value="{{ $assessment->tanggal->toDateString() }}" aria-label="Tanggal baru">
                    <input name="alasan" class="input" required maxlength="200" placeholder="Alasan perubahan" aria-label="Alasan">
                    <button class="btn btn-primary w-full">Simpan tanggal baru</button>
                </form>
            @endif
        </section>

        <section class="card card-pad">
            <h2 class="card-title">Susulan beberapa siswa</h2>
            <p class="card-sub mb-3">Misalnya untuk siswa yang mengikuti lomba.</p>
            <form method="POST" action="{{ route('admin.jadwal.susulan', $assessment) }}" class="space-y-3">
                @csrf
                <div class="max-h-56 overflow-y-auto border border-[color:var(--line)] rounded-xl divide-y divide-slate-100">
                    @foreach($siswa as $s)
                        @php($m = $assessment->makeups->firstWhere('student_id', $s->id))
                        <label class="flex items-center gap-3 px-3 py-2 text-sm">
                            <input type="checkbox" name="student_ids[]" value="{{ $s->id }}" @disabled($assessment->scores->contains('student_id', $s->id))>
                            <span class="flex-1">{{ $s->name }}</span>
                            @if($m)<span class="badge badge-violet">susulan {{ $m->tanggal->translatedFormat('d M') }}</span>@endif
                        </label>
                    @endforeach
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="tanggal" class="input" required min="{{ $assessment->tanggal->copy()->addDay()->toDateString() }}" aria-label="Tanggal susulan">
                    <input name="alasan" class="input" required maxlength="200" placeholder="Alasan" aria-label="Alasan">
                </div>
                <button class="btn btn-light w-full"><i class="fa-solid fa-user-clock"></i>Tetapkan susulan</button>
            </form>
        </section>

        <section class="card card-pad">
            <h2 class="card-title">Buka akses unggah</h2>
            <p class="card-sub mb-3">Akses dibuka selama {{ \App\Models\Setting::durasiBukaAksesJam() }} jam. Nilai yang masuk tetap tercatat terlambat.</p>
            @if($assessment->aksesAktif())
                <p class="text-sm font-semibold text-brand-700"><i class="fa-solid fa-lock-open mr-1"></i>Sedang dibuka sampai {{ $assessment->aksesAktif()->berakhir_at->translatedFormat('d M Y, H:i') }}.</p>
            @else
                <form method="POST" action="{{ route('admin.jadwal.buka', $assessment) }}" class="flex flex-col sm:flex-row gap-2">
                    @csrf
                    <input name="alasan" class="input flex-1" required maxlength="300" placeholder="Alasan" aria-label="Alasan">
                    <button class="btn btn-primary"><i class="fa-solid fa-lock-open"></i>Buka akses</button>
                </form>
            @endif
            @foreach($assessment->unlocks as $u)
                <div class="text-sm py-2 border-b border-slate-100 last:border-0 mt-2">
                    <span class="badge {{ $u->status === 'ditolak' ? 'badge-rose' : ($u->status === 'menunggu' ? 'badge-amber' : 'badge-slate') }}">{{ $u->statusLabel() }}</span>
                    <span class="text-slate-500 ml-1">{{ $u->alasan }}</span>
                </div>
            @endforeach
        </section>

        <section class="card card-pad">
            <h2 class="card-title mb-2">Riwayat perubahan jadwal</h2>
            @forelse($assessment->scheduleChanges as $c)
                <div class="py-2 border-b border-slate-100 last:border-0 text-sm">
                    <p class="font-bold text-slate-700">{{ $c->tanggal_lama->translatedFormat('d M') }} <i class="fa-solid fa-arrow-right text-xs text-slate-400 mx-1"></i> {{ $c->tanggal_baru->translatedFormat('d M Y') }}</p>
                    <p class="text-slate-500">{{ $c->alasan }} &middot; {{ $c->pengubah->name }}, {{ $c->created_at->translatedFormat('d M H:i') }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-400">Belum pernah diubah.</p>
            @endforelse
        </section>
    </div>
</div>
@endsection