@extends('layouts.app')

@section('title', 'Susun Laporan - EduMonitor')

@section('content')
@php($fmt = fn ($v) => $v === null ? '-' : rtrim(rtrim(number_format($v, 1, ',', ''), '0'), ','))
<div class="space-y-6">
    <div>
        <a href="{{ route('guru.laporan') }}" class="section-link"><i class="fa-solid fa-arrow-left mr-1 text-xs"></i>Laporan ke Orang Tua</a>
        <h1 class="page-title mt-2">Susun laporan</h1>
        <p class="page-sub">{{ $st->subject->nama_mapel }} &middot; Kelas {{ $st->kelas->nama_kelas }} &middot; {{ $siswa->count() }} siswa</p>
    </div>

    <!-- Pilih cakupan laporan -->
    <form method="GET" class="card card-pad flex flex-wrap items-end gap-4">
        <div>
            <label class="label" for="cakupan">Isi laporan</label>
            <select id="cakupan" name="cakupan" class="input !w-auto" onchange="this.form.submit()">
                @foreach($babTersedia as $b)
                    <option value="{{ $b }}" @selected((string) $cakupan === (string) $b)>Bab {{ $b }}: tugas, kuis, dan ulangan</option>
                @endforeach
                <option value="semester" @selected($cakupan === 'semester')>Ringkasan semester: nilai per bab, UTS, UAS, nilai akhir</option>
            </select>
        </div>
        <noscript><button class="btn btn-light">Tampilkan</button></noscript>
        <p class="hint !mt-0 pb-2.5">Nilai disalin saat laporan dikirim. Perubahan nilai setelahnya tidak mengubah laporan ini.</p>
    </form>

    <form method="POST" action="{{ route('guru.laporan.kirim', $st) }}" class="space-y-6">
        @csrf
        <input type="hidden" name="cakupan" value="{{ $cakupan }}">

        <section class="card card-pad space-y-4">
            <div>
                <label class="label" for="judul">Judul laporan</label>
                <input id="judul" name="judul" class="input" required maxlength="150" value="{{ old('judul', $judul) }}">
            </div>
            <div>
                <label class="label" for="pesan">Pesan untuk semua orang tua (boleh dikosongkan)</label>
                <textarea id="pesan" name="pesan" rows="3" maxlength="1000" class="input" placeholder="Contoh: Bab 2 tentang pecahan sudah selesai. Mohon dampingi anak berlatih soal cerita di rumah.">{{ old('pesan') }}</textarea>
            </div>
        </section>

        <section>
            <div class="section-head">
                <h2 class="section-title"><span class="ikon-judul"><i class="fa-solid fa-user-pen"></i></span>Catatan per siswa</h2>
                <span class="text-sm text-slate-600">Opsional. Hanya dibaca orang tua siswa tersebut.</span>
            </div>
            <div class="card rows overflow-hidden">
                @foreach($siswa as $s)
                    @php($u = $isi[$s->id]['utama'])
                    <div class="p-4 sm:px-5">
                        <div class="flex flex-wrap items-start gap-x-4 gap-y-2">
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-slate-900">{{ $s->name }}</p>
                                <p class="text-sm text-slate-600">
                                    @if($s->parents->isNotEmpty())
                                        <i class="fa-solid fa-user-group mr-1 text-xs text-slate-400"></i>{{ $s->parents->pluck('name')->join(', ') }}
                                    @else
                                        <span class="text-soga-700"><i class="fa-solid fa-circle-info mr-1 text-xs"></i>Belum ada akun orang tua</span>
                                    @endif
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-[13px] font-semibold text-slate-600">{{ $u['label'] }}</p>
                                <p><span class="nilai text-xl">{{ $fmt($u['nilai']) }}</span> <span class="text-[13px] text-slate-500 tabular">kelas {{ $fmt($u['kelas']) }}</span></p>
                            </div>
                        </div>
                        <details class="tabel-grafik mt-2">
                            <summary>Lihat isi laporan</summary>
                            <div class="mt-3">@include('partials.isi-laporan', ['isi' => $isi[$s->id]])</div>
                        </details>
                        <label class="sr-only" for="catatan-{{ $s->id }}">Catatan untuk {{ $s->name }}</label>
                        <textarea id="catatan-{{ $s->id }}" name="catatan[{{ $s->id }}]" rows="2" maxlength="500" class="input mt-3 !text-sm" placeholder="Catatan guru (opsional), misalnya: sudah lancar, perlu latihan soal cerita.">{{ old('catatan.' . $s->id) }}</textarea>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <button class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i>Kirim ke orang tua</button>
            <p class="text-sm text-slate-600">Orang tua akan menerima notifikasi. Anda bisa melihat siapa yang sudah membaca.</p>
        </div>
    </form>
</div>
@endsection