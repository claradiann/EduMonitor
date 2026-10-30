@extends('layouts.app')

@section('title', 'Input Nilai - EduMonitor')

@php($st = $assessment->subjectTeacher)
@php($maks = rtrim(rtrim(number_format($assessment->skor_maks, 2, ',', ''), '0'), ','))

@section('content')
<div class="space-y-6">
    <a href="{{ route('guru.penilaian', ['kelas' => $st->id]) }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-brand-700"><i class="fa-solid fa-arrow-left"></i>Kembali ke daftar penilaian</a>

    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="page-title">{{ $assessment->judul }}</h1>
            <p class="page-sub">{{ $st->label() }} &middot; {{ $assessment->labelJenis() }}</p>
        </div>
        @include('partials.status-penilaian', ['status' => $status])
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="card px-4 py-3"><p class="stat-label">Tanggal</p><p class="font-bold text-slate-800">{{ $assessment->tanggal->translatedFormat('d M Y') }}</p></div>
        <div class="card px-4 py-3"><p class="stat-label">Batas input</p><p class="font-bold text-slate-800">{{ $assessment->batasUnggah()->translatedFormat('d M Y') }}</p></div>
        <div class="card px-4 py-3"><p class="stat-label">Skor maksimal</p><p class="font-bold text-slate-800">{{ $maks }}</p></div>
        <div class="card px-4 py-3"><p class="stat-label">Sudah dinilai</p><p class="font-bold text-slate-800">{{ $baris->whereNotNull('skor')->count() }}/{{ $baris->count() }}</p></div>
    </div>

    @if($akses)
        <div class="rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-800">
            <i class="fa-solid fa-lock-open mr-1"></i><b>Akses dibuka admin</b> sampai {{ $akses->berakhir_at->translatedFormat('d F Y, H:i') }}. Nilai yang masuk sekarang tercatat terlambat.
        </div>
    @elseif($status === 'dijadwalkan')
        <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
            <i class="fa-solid fa-calendar mr-1"></i>Nilai bisa diinput mulai {{ $assessment->tanggal->translatedFormat('l, d F Y') }}.
        </div>
    @elseif($status === 'melewati_batas')
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 space-y-3">
            <p><i class="fa-solid fa-lock mr-1"></i><b>Masa input sudah ditutup.</b> Untuk memasukkan nilai, ajukan buka akses ke admin. Bila disetujui, akses dibuka selama 24 jam.</p>
            @if($pengajuan)
                <p class="font-bold"><i class="fa-solid fa-hourglass-half mr-1"></i>Pengajuan dikirim {{ $pengajuan->created_at->diffForHumans() }} dan menunggu persetujuan admin.</p>
            @else
                <form method="POST" action="{{ route('guru.penilaian.akses', $assessment) }}" class="flex flex-col sm:flex-row gap-2">
                    @csrf
                    <label for="alasan-akses" class="sr-only">Alasan</label>
                    <input id="alasan-akses" name="alasan" class="input flex-1" required maxlength="300" placeholder="Alasan, misalnya: menunggu nilai remedial dua siswa">
                    <button class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i>Ajukan buka akses</button>
                </form>
            @endif
        </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6 items-start">
        <form method="POST" action="{{ route('guru.penilaian.simpan', $assessment) }}" class="card lg:col-span-2 overflow-hidden">
            @csrf
            <div class="px-5 py-4 border-b border-[color:var(--line)]">
                <h2 class="card-title">Nilai siswa</h2>
                <p class="card-sub">Isi skor asli (0 sampai {{ $maks }}). Nilai 0-100 dihitung otomatis. Remedial cukup mengganti skornya selama masa input.</p>
            </div>
            <div class="tbl-wrap">
                <table class="tbl">
                    <thead>
                        <tr><th>Siswa</th><th class="w-32">Skor</th><th class="num w-20">Nilai</th></tr>
                    </thead>
                    <tbody>
                        @foreach($baris as $b)
                            <tr>
                                <td>
                                    <p class="font-bold text-slate-800">{{ $b['siswa']->name }}</p>
                                    <p class="text-xs text-slate-400">NISN {{ $b['siswa']->nisn ?? '-' }}
                                        @if($b['susulan']) &middot; <span class="text-violet-700 font-semibold">susulan {{ $b['susulan']->tanggal->translatedFormat('d M') }}</span>@endif
                                        @if($b['skor']?->terlambat) &middot; <span class="text-rose-600 font-semibold">masuk terlambat</span>@endif
                                    </p>
                                </td>
                                <td>
                                    <label for="skor-{{ $b['siswa']->id }}" class="sr-only">Skor {{ $b['siswa']->name }}</label>
                                    <input id="skor-{{ $b['siswa']->id }}" type="number" step="any" min="0" max="{{ $assessment->skor_maks }}"
                                           name="skor[{{ $b['siswa']->id }}]" value="{{ old('skor.' . $b['siswa']->id, $b['skor'] ? rtrim(rtrim(number_format($b['skor']->skor, 2, '.', ''), '0'), '.') : '') }}"
                                           class="input !py-2 skor-input" data-maks="{{ $assessment->skor_maks }}" @disabled(!$b['bisa'])
                                           placeholder="{{ $b['bisa'] ? '' : 'terkunci' }}">
                                </td>
                                <td class="num font-extrabold text-slate-800 nk-preview">{{ $b['skor'] ? rtrim(rtrim(number_format($b['skor']->nilai_konversi, 1, ',', ''), '0'), ',') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-[color:var(--line)] flex justify-end">
                <button class="btn btn-primary" @disabled($baris->where('bisa', true)->isEmpty())><i class="fa-solid fa-floppy-disk"></i>Simpan nilai</button>
            </div>
        </form>

        <div class="space-y-6">
            <section class="card card-pad">
                <h2 class="card-title">Susulan</h2>
                <p class="card-sub mb-3">Untuk siswa yang berhalangan. Masa input siswa susulan dihitung dari tanggal susulannya.</p>
                @foreach($assessment->makeups as $m)
                    <div class="flex items-center gap-2 py-2 border-b border-slate-100">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-slate-700">{{ $baris->firstWhere('siswa.id', $m->student_id)['siswa']->name ?? '-' }}</p>
                            <p class="text-xs text-slate-500">{{ $m->tanggal->translatedFormat('d M Y') }} &middot; {{ $m->alasan }}</p>
                        </div>
                        <form method="POST" action="{{ route('guru.penilaian.susulan.hapus', [$assessment, $m]) }}">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" aria-label="Hapus susulan"><i class="fa-solid fa-xmark"></i></button>
                        </form>
                    </div>
                @endforeach
                <form method="POST" action="{{ route('guru.penilaian.susulan', $assessment) }}" class="space-y-3 mt-3">
                    @csrf
                    <div>
                        <label class="label" for="susulan-siswa">Siswa</label>
                        <select id="susulan-siswa" name="student_id" class="input" required>
                            @foreach($baris->whereNull('skor') as $b)
                                <option value="{{ $b['siswa']->id }}">{{ $b['siswa']->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="susulan-tanggal">Tanggal susulan</label>
                        <input id="susulan-tanggal" type="date" name="tanggal" class="input" required min="{{ $assessment->tanggal->copy()->addDay()->toDateString() }}">
                    </div>
                    <div>
                        <label class="label" for="susulan-alasan">Alasan</label>
                        <input id="susulan-alasan" name="alasan" class="input" required maxlength="200" placeholder="Contoh: sakit, surat dokter">
                    </div>
                    <button class="btn btn-light w-full"><i class="fa-solid fa-user-clock"></i>Tetapkan susulan</button>
                </form>
            </section>

            @if($assessment->bolehDiubahGuru())
                <section class="card card-pad">
                    <h2 class="card-title">Ubah tanggal</h2>
                    <p class="card-sub mb-3">Bisa sampai akhir hari pelaksanaan selama belum ada nilai. Siswa dan orang tua mendapat notifikasi.</p>
                    <form method="POST" action="{{ route('guru.penilaian.tanggal', $assessment) }}" class="space-y-3">
                        @csrf @method('PATCH')
                        <input type="date" name="tanggal" class="input" required min="{{ today()->toDateString() }}" value="{{ $assessment->tanggal->toDateString() }}" aria-label="Tanggal baru">
                        <input name="alasan" class="input" required maxlength="200" placeholder="Alasan perubahan" aria-label="Alasan perubahan">
                        <button class="btn btn-light w-full"><i class="fa-solid fa-calendar-days"></i>Simpan tanggal baru</button>
                    </form>
                    <form method="POST" action="{{ route('guru.penilaian.hapus', $assessment) }}" class="mt-3" onsubmit="return document.getElementById('konfirmasi-batal').checked">
                        @csrf @method('DELETE')
                        <label class="flex items-center gap-2 text-sm text-slate-600 mb-2"><input type="checkbox" id="konfirmasi-batal"> Saya yakin membatalkan penilaian ini</label>
                        <button class="btn btn-danger w-full"><i class="fa-solid fa-trash"></i>Batalkan penilaian</button>
                    </form>
                </section>
            @endif

            @if($assessment->scheduleChanges->isNotEmpty())
                <section class="card card-pad">
                    <h2 class="card-title mb-2">Riwayat perubahan jadwal</h2>
                    @foreach($assessment->scheduleChanges as $c)
                        <div class="py-2 border-b border-slate-100 last:border-0 text-sm">
                            <p class="font-bold text-slate-700">{{ $c->tanggal_lama->translatedFormat('d M') }} <i class="fa-solid fa-arrow-right text-xs text-slate-400 mx-1"></i> {{ $c->tanggal_baru->translatedFormat('d M Y') }}</p>
                            <p class="text-slate-500">{{ $c->alasan }} &middot; {{ $c->pengubah->name }}</p>
                        </div>
                    @endforeach
                </section>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Pratinjau konversi NK = S / Smaks x 100
    document.querySelectorAll('.skor-input').forEach(function (el) {
        el.addEventListener('input', function () {
            const maks = parseFloat(el.dataset.maks);
            const s = parseFloat(el.value.replace(',', '.'));
            const cell = el.closest('tr').querySelector('.nk-preview');
            cell.textContent = isNaN(s) ? '-' : (Math.min(s, maks) / maks * 100).toFixed(1).replace('.0', '').replace('.', ',');
        });
    });
</script>
@endsection