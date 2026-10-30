@extends('layouts.app')

@section('title', 'Beranda - EduMonitor')

@section('content')
@if(!$student)
    @include('partials.tanpa-anak')
@else
@php
    $ortu = Auth::user()->role === 'orang_tua';
    $fmt = fn ($n) => rtrim(rtrim(number_format($n, 1, ',', ''), '0'), ',');
    $namaDepan = strtok($student->name, ' ');

    // Pita 7 hari mulai hari ini, berisi penilaian yang dijadwalkan pada hari itu
    $pekan = collect(range(0, 6))->map(fn ($i) => today()->addDays($i));
    $perHari = $mendatang->groupBy(fn ($p) => $p['tanggal']->toDateString());
    $menunggu = ($ringkasan['menunggu_nilai'] ?? 0) + ($ringkasan['melewati_batas'] ?? 0);

    $totalPenilaian = $ringkasan->sum();
    $urutStatus = ['nilai_tersedia', 'melewati_batas', 'menunggu_nilai', 'dijadwalkan'];
@endphp
<div class="space-y-7">
    @include('partials.sapaan', ['nama' => $ortu ? 'Bapak/Ibu' : $namaDepan])
    @if($ortu)
        <p class="-mt-3 text-sm text-slate-600">Memantau <b class="text-slate-900">{{ $student->name }}</b> &middot; Kelas {{ $student->kelas->nama_kelas ?? '-' }}</p>
    @endif

    <!-- Pekan ini -->
    <section>
        <div class="section-head">
            <h2 class="section-title"><span class="ikon-judul"><i class="fa-solid fa-calendar-week"></i></span>7 hari ke depan</h2>
        </div>
        <div class="card p-3 sm:p-4">
            <div class="pekan" role="list" aria-label="Penilaian tujuh hari ke depan">
                @foreach($pekan as $hari)
                    @php($isi = $perHari->get($hari->toDateString(), collect()))
                    <div role="listitem" class="pekan-hari {{ $hari->isToday() ? 'ini' : '' }} {{ $hari->isSunday() ? 'libur' : '' }}"
                         title="{{ $isi->map(fn ($x) => $x['model']->judul . ' ' . $x['model']->subjectTeacher->subject->nama_mapel)->join(', ') }}"
                         aria-label="{{ $hari->translatedFormat('l d F') }}: {{ $isi->count() }} penilaian">
                        <p class="h">{{ $hari->isToday() ? 'Hari ini' : $hari->translatedFormat('D') }}</p>
                        <p class="t">{{ $hari->format('j') }}</p>
                        <p class="d">@foreach($isi->take(3) as $x)<span class="titik titik-dijadwalkan"></span>@endforeach</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Status penilaian semester -->
    @if($totalPenilaian > 0)
        <section class="card card-pad">
            <div class="flex items-baseline justify-between gap-3 mb-3">
                <h2 class="card-title">Penilaian semester ini</h2>
                <span class="text-sm font-bold text-slate-600 tabular">{{ $ringkasan['nilai_tersedia'] ?? 0 }} dari {{ $totalPenilaian }} sudah ada nilai</span>
            </div>
            <div class="bar-status" role="img" aria-label="Status {{ $totalPenilaian }} penilaian">
                @foreach($urutStatus as $key)
                    @if(($ringkasan[$key] ?? 0) > 0)
                        <span class="seg-{{ $key }}" style="flex-grow: {{ $ringkasan[$key] }}"></span>
                    @endif
                @endforeach
            </div>
            <div class="legenda mt-3">
                @foreach($urutStatus as $key)
                    <a href="{{ route('jadwal', $key === 'nilai_tersedia' ? ['tampil' => 'semua'] : []) }}" class="legenda-item hover:text-slate-900">
                        <span class="kotak seg-{{ $key }}"></span>{{ \App\Models\Assessment::STATUS[$key]['label'] }} <b>{{ $ringkasan[$key] ?? 0 }}</b>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if($evaluasiBelum > 0)
        <a href="{{ route('siswa.evaluasi') }}" class="flex items-center gap-4 rounded-[10px] bg-soga-50 border border-soga-100 px-4 py-3 hover:bg-soga-100/70">
            <span class="ikon-judul soga !mr-0"><i class="fa-solid fa-star-half-stroke"></i></span>
            <span class="flex-1">
                <span class="block font-bold text-slate-900">{{ $evaluasiBelum }} evaluasi guru belum diisi</span>
                <span class="block text-sm text-slate-600">Anonim. Membantu guru mengajar lebih baik.</span>
            </span>
            <i class="fa-solid fa-chevron-right text-soga-400"></i>
        </a>
    @endif

    <!-- Grafik perkembangan nilai -->
    @if($tren->isNotEmpty())
        <section class="card card-pad">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="card-title"><span class="ikon-judul"><i class="fa-solid fa-chart-line"></i></span>Perkembangan nilai</h2>
                    <p class="card-sub mt-1">Nilai tugas, kuis, dan ulangan dibandingkan dengan rata-rata kelas.</p>
                </div>
                <label class="sr-only" for="pilih-mapel">Mata pelajaran</label>
                <select id="pilih-mapel" class="input !w-auto !py-2 !text-sm font-semibold">
                    @foreach($tren->keys() as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach
                </select>
            </div>
            <div class="legenda mt-4">
                <span class="legenda-item" style="color:#4652a8"><span class="garis-legenda"></span><span class="text-slate-700">Nilai {{ $namaDepan }}</span></span>
                <span class="legenda-item" style="color:#c06a2c"><span class="garis-legenda putus"></span><span class="text-slate-700">Rata-rata kelas</span></span>
            </div>
            <div class="grafik h-[240px] sm:h-[280px] mt-2"><canvas id="grafik-tren" aria-label="Grafik perkembangan nilai" role="img"></canvas></div>
            <p id="tren-catatan" class="text-sm text-slate-700 mt-3"></p>
        </section>
    @endif

    <div class="grid lg:grid-cols-5 gap-7">
        <!-- Penilaian mendatang -->
        <section class="lg:col-span-3">
            <div class="section-head">
                <h2 class="section-title"><span class="ikon-judul"><i class="fa-solid fa-calendar-days"></i></span>Akan datang</h2>
                <a href="{{ route('jadwal') }}" class="section-link">Semua jadwal</a>
            </div>
            <div class="card rows overflow-hidden">
                @forelse($mendatang as $p)
                    @php($hari = (int) today()->diffInDays($p['tanggal']))
                    <div class="row">
                        @include('partials.datechip', ['tanggal' => $p['tanggal']])
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-900 truncate">{{ $p['model']->judul }}</p>
                            <p class="text-sm text-slate-600 truncate">{{ $p['model']->subjectTeacher->subject->nama_mapel }} &middot; {{ $p['model']->labelJenis() }}</p>
                        </div>
                        <span class="text-[13px] font-bold whitespace-nowrap {{ $hari <= 1 ? 'text-soga-700' : 'text-slate-600' }}">
                            {{ $hari === 0 ? 'Hari ini' : ($hari === 1 ? 'Besok' : $hari . ' hari lagi') }}
                        </span>
                    </div>
                @empty
                    <div class="empty"><i class="fa-regular fa-calendar"></i>Belum ada penilaian yang dijadwalkan.</div>
                @endforelse
            </div>
        </section>

        <!-- Nilai akhir per mapel -->
        <section class="lg:col-span-2">
            <div class="section-head">
                <h2 class="section-title"><span class="ikon-judul"><i class="fa-solid fa-ranking-star"></i></span>Nilai akhir per mapel</h2>
                <a href="{{ route('nilai') }}" class="section-link">Rincian</a>
            </div>
            <div class="card card-pad">
                @if($rataNilai !== null)
                    <div class="flex items-baseline gap-2 mb-3">
                        <span class="nilai text-[30px] leading-none">{{ str_replace('.', ',', $rataNilai) }}</span>
                        <span class="text-sm font-semibold text-slate-600">rata-rata dari {{ $jumlahMapel }} mapel</span>
                    </div>
                @endif
                @if($perMapel->isNotEmpty())
                    <div class="grafik" style="height: {{ $perMapel->count() * 36 + 36 }}px"><canvas id="grafik-mapel" role="img" aria-label="Nilai akhir per mata pelajaran"></canvas></div>
                    <p class="hint flex items-center gap-2 mt-2"><span class="kotak" style="background:#c6cbe8"></span>Batang muda: nilai sementara, UTS/UAS belum lengkap.</p>
                    <details class="tabel-grafik mt-3">
                        <summary>Lihat sebagai tabel</summary>
                        <table class="tbl mt-2">
                            <tbody>
                                @foreach($perMapel as $m)
                                    <tr><td>{{ $m['mapel'] }}</td><td class="num font-bold">{{ $m['nilai'] }}{{ $m['sementara'] ? ' (sementara)' : '' }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </details>
                @else
                    <div class="empty"><i class="fa-regular fa-file-lines"></i>Belum ada nilai akhir.</div>
                @endif
            </div>
        </section>
    </div>

    <div class="grid lg:grid-cols-2 gap-7">
        <!-- Nilai terbaru -->
        <section>
            <div class="section-head">
                <h2 class="section-title"><span class="ikon-judul"><i class="fa-solid fa-circle-check"></i></span>Nilai terbaru</h2>
                <a href="{{ route('nilai') }}" class="section-link">Semua nilai</a>
            </div>
            <div class="card rows overflow-hidden">
                @forelse($nilaiTerbaru as $s)
                    <div class="row">
                        <span class="titik titik-nilai_tersedia"></span>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-900 truncate">{{ $s->assessment->judul }}</p>
                            <p class="text-sm text-slate-600 truncate">{{ $s->assessment->subjectTeacher->subject->nama_mapel }}</p>
                        </div>
                        <span class="nilai text-xl">{{ $fmt($s->nilai_konversi) }}</span>
                    </div>
                @empty
                    <div class="empty"><i class="fa-regular fa-file-lines"></i>Belum ada nilai semester ini.</div>
                @endforelse
            </div>
        </section>

        <!-- Pemberitahuan terbaru -->
        <section>
            <div class="section-head">
                <h2 class="section-title"><span class="ikon-judul soga"><i class="fa-solid fa-bell"></i></span>Pemberitahuan</h2>
                <a href="{{ route('notifikasi') }}" class="section-link">Semua</a>
            </div>
            <div class="card rows overflow-hidden">
                @forelse($notifikasi as $n)
                    @php([$ikon, $warna] = $n->ikon())
                    <a href="{{ route('notifikasi.buka', $n) }}" class="row !items-start">
                        <span class="w-9 h-9 shrink-0 rounded-lg flex items-center justify-center {{ $warna }}"><i class="fa-solid {{ $ikon }}"></i></span>
                        <span class="min-w-0">
                            <span class="block text-sm font-bold text-slate-900">{{ $n->judul }}</span>
                            <span class="block text-sm text-slate-600">{{ $n->pesan }}</span>
                        </span>
                    </a>
                @empty
                    <div class="empty"><i class="fa-regular fa-bell"></i>Belum ada pemberitahuan.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endif
@endsection

@if($student)
@section('scripts')
<script>
    (function () {
        const tren = @json($tren);
        const perMapel = @json($perMapel);
        const nama = @json($namaDepan);

        if (document.getElementById('grafik-mapel')) Grafik.perMapel(document.getElementById('grafik-mapel'), perMapel);

        const kanvas = document.getElementById('grafik-tren');
        if (!kanvas) return;
        const pilih = document.getElementById('pilih-mapel');
        const catatan = document.getElementById('tren-catatan');
        let grafik;

        // Kalimat singkat di bawah grafik supaya tidak perlu membaca angka satu per satu
        function tulisCatatan(data) {
            const akhir = data[data.length - 1];
            const selisih = Math.round((akhir.nilai - akhir.kelas) * 10) / 10;
            let teks = akhir.label + ': ' + nama + ' mendapat ' + Grafik.angka(akhir.nilai);
            if (selisih > 0) teks += ', ' + Grafik.angka(selisih) + ' di atas rata-rata kelas.';
            else if (selisih < 0) teks += ', ' + Grafik.angka(-selisih) + ' di bawah rata-rata kelas.';
            else teks += ', sama dengan rata-rata kelas.';
            if (data.length > 1) {
                const naik = akhir.nilai - data[data.length - 2].nilai;
                if (naik > 0) teks += ' Naik ' + Grafik.angka(naik) + ' dari penilaian sebelumnya.';
                else if (naik < 0) teks += ' Turun ' + Grafik.angka(-naik) + ' dari penilaian sebelumnya.';
            }
            catatan.textContent = teks;
        }

        function tampil(mapel) {
            if (grafik) grafik.destroy();
            grafik = Grafik.tren(kanvas, tren[mapel]);
            tulisCatatan(tren[mapel]);
        }
        pilih.addEventListener('change', () => tampil(pilih.value));
        tampil(pilih.value);
    })();
</script>
@endsection
@endif