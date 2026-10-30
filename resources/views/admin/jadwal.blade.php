@extends('layouts.app')

@section('title', 'Jadwal Penilaian - EduMonitor')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="page-title">Jadwal Penilaian</h1>
        <p class="page-sub">Atur UTS dan UAS dari kalender akademik, dan geser jadwal bila bentrok dengan agenda sekolah atau dinas.</p>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 items-start">
        <section class="card card-pad lg:order-2">
            <h2 class="card-title">Buat jadwal UTS / UAS</h2>
            <p class="card-sub mb-4">Dibuat untuk setiap mata pelajaran di kelas yang dipilih.</p>
            <form method="POST" action="{{ route('admin.jadwal.ujian') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="u-jenis">Jenis</label>
                        <select id="u-jenis" name="jenis" class="input"><option value="uts">UTS</option><option value="uas">UAS</option></select>
                    </div>
                    <div>
                        <label class="label" for="u-tanggal">Tanggal</label>
                        <input id="u-tanggal" type="date" name="tanggal" class="input" required>
                    </div>
                </div>
                <div>
                    <label class="label" for="u-mapel">Mata pelajaran</label>
                    <select id="u-mapel" name="subject_id" class="input">
                        <option value="">Semua mata pelajaran</option>
                        @foreach($mapel as $m)<option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>@endforeach
                    </select>
                </div>
                <fieldset>
                    <legend class="label">Kelas</legend>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($kelas as $k)
                            <label class="flex items-center gap-2 text-sm rounded-lg border border-[color:var(--line)] px-3 py-2"><input type="checkbox" name="kelas_ids[]" value="{{ $k->id }}" checked>{{ $k->nama_kelas }}</label>
                        @endforeach
                    </div>
                </fieldset>
                <button class="btn btn-primary w-full"><i class="fa-solid fa-calendar-plus"></i>Buat jadwal & kirim notifikasi</button>
            </form>
        </section>

        <section class="lg:col-span-2 lg:order-1 space-y-3">
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="hidden" name="jenis" value="{{ $jenis }}">
                @foreach(['ujian' => 'UTS & UAS', 'harian' => 'Penilaian harian', 'semua' => 'Semua'] as $k => $v)
                    <button name="jenis" value="{{ $k }}" class="btn btn-sm {{ $jenis === $k ? 'btn-primary' : 'btn-light' }}">{{ $v }}</button>
                @endforeach
                <label for="f-kelas" class="sr-only">Kelas</label>
                <select id="f-kelas" name="kelas" class="input !w-auto !py-1.5 text-sm" onchange="this.form.submit()">
                    <option value="">Semua kelas</option>
                    @foreach($kelas as $k)<option value="{{ $k->id }}" @selected($kelasId == $k->id)>{{ $k->nama_kelas }}</option>@endforeach
                </select>
            </form>

            <form method="POST" action="{{ route('admin.jadwal.geser') }}" class="card overflow-hidden">
                @csrf
                <div class="tbl-wrap">
                    <table class="tbl">
                        <thead><tr><th class="w-8"><span class="sr-only">Pilih</span></th><th>Tanggal</th><th>Penilaian</th><th>Kelas</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($baris as $b)
                                @php($a = $b['model'])
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="{{ $a->id }}" aria-label="Pilih {{ $a->judul }}" @disabled($a->scores->isNotEmpty())></td>
                                    <td class="whitespace-nowrap font-semibold">{{ $a->tanggal->translatedFormat('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.jadwal.show', $a) }}" class="font-bold text-slate-800 hover:text-brand-700">{{ $a->judul }}</a>
                                        <p class="text-xs text-slate-400">{{ $a->labelJenis() }} &middot; {{ $a->subjectTeacher->teacher->name }}</p>
                                    </td>
                                    <td class="whitespace-nowrap">{{ $a->subjectTeacher->kelas->nama_kelas }}</td>
                                    <td>@include('partials.status-penilaian', ['status' => $b['status']])</td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><div class="empty"><i class="fa-regular fa-calendar"></i>Belum ada jadwal.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($baris->isNotEmpty())
                    <div class="border-t border-[color:var(--line)] bg-slate-50 p-4 space-y-3">
                        <p class="text-sm font-bold text-slate-700"><i class="fa-solid fa-calendar-days mr-1"></i>Geser jadwal yang dicentang</p>
                        <div class="grid sm:grid-cols-[160px_1fr_auto] gap-2">
                            <input type="date" name="tanggal" class="input" required aria-label="Tanggal baru">
                            <input name="alasan" class="input" required maxlength="200" placeholder="Alasan, misalnya: bentrok undangan lomba dari dinas" aria-label="Alasan">
                            <button class="btn btn-primary">Geser</button>
                        </div>
                        <p class="hint">Penilaian yang nilainya sudah masuk tidak bisa digeser. Gunakan susulan untuk siswa yang berhalangan.</p>
                    </div>
                @endif
            </form>
        </section>
    </div>
</div>
@endsection