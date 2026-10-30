@extends('layouts.app')

@section('title', 'Beranda Admin - EduMonitor')

@section('content')
<div class="space-y-6">
    @php
        $perlu = [];
        if ($stats['pengajuan']) $perlu[] = '<b>' . $stats['pengajuan'] . ' pengajuan buka akses</b> menunggu persetujuan';
        if ($stats['terlambat']) $perlu[] = '<b>' . $stats['terlambat'] . ' penilaian</b> melewati batas unggah';
        $kalimat = $perlu ? 'Perlu ditindaklanjuti: ' . implode(' dan ', $perlu) . '.' : 'Tidak ada yang perlu ditindaklanjuti hari ini.';
        if ($ujianMendatang->isNotEmpty()) {
            $u = $ujianMendatang->first()['contoh'];
            $kalimat .= ' ' . $u->jenisLabel() . ' berikutnya ' . $u->tanggal->translatedFormat('l d F') . '.';
        }
        $totalStatus = $statusSekolah->sum();
        $urutStatus = ['nilai_tersedia', 'melewati_batas', 'menunggu_nilai', 'dijadwalkan'];
    @endphp
    @include('partials.sapaan', [
        'judul' => 'Ringkasan Sekolah',
        'teks' => $kalimat,
        'chips' => [
            [route('admin.akses'), 'fa-lock-open', 'Buka akses nilai'],
            [route('admin.jadwal'), 'fa-calendar-days', 'Jadwal UTS/UAS'],
            [route('admin.recap'), 'fa-chart-column', 'Rekap kinerja'],
        ],
    ])

    <!-- Yang perlu ditindaklanjuti -->
    <div class="grid sm:grid-cols-3 gap-3">
        <a href="{{ route('admin.akses') }}" class="card card-pad flex items-start justify-between gap-3 hover:border-nila-300 {{ $stats['pengajuan'] ? '!bg-soga-50 !border-soga-200' : '' }}">
            <span><span class="block stat-num {{ $stats['pengajuan'] ? '!text-soga-700' : '' }}">{{ $stats['pengajuan'] }}</span><span class="stat-label">Pengajuan buka akses menunggu</span></span>
            <i class="fa-solid fa-lock-open mt-1 {{ $stats['pengajuan'] ? 'text-soga-500' : 'text-slate-300' }}"></i>
        </a>
        <a href="{{ route('admin.recap') }}" class="card card-pad flex items-start justify-between gap-3 hover:border-nila-300 {{ $stats['terlambat'] ? '!bg-bata-50 !border-bata-100' : '' }}">
            <span><span class="block stat-num {{ $stats['terlambat'] ? '!text-bata-600' : '' }}">{{ $stats['terlambat'] }}</span><span class="stat-label">Penilaian melewati batas unggah</span></span>
            <span class="titik titik-melewati_batas mt-2"></span>
        </a>
        <a href="{{ route('admin.laporan') }}" class="card card-pad flex items-start justify-between gap-3 hover:border-nila-300">
            <span><span class="block stat-num">{{ $stats['laporan_minggu_ini'] }}</span><span class="stat-label">Laporan insidental 7 hari terakhir</span></span>
            <i class="fa-solid fa-flag mt-1 text-slate-300"></i>
        </a>
    </div>

    <div class="strip">
        <div><p class="strip-num">{{ $stats['siswa'] }}</p><p class="strip-label">Siswa</p></div>
        <div><p class="strip-num">{{ $stats['guru'] }}</p><p class="strip-label">Guru</p></div>
        <div><p class="strip-num">{{ $stats['kelas'] }}</p><p class="strip-label">Kelas</p></div>
        <div><p class="strip-num">{{ $stats['penilaian'] }}</p><p class="strip-label">Penilaian semester ini</p></div>
    </div>

    <div class="grid lg:grid-cols-5 gap-6">
        <section class="card card-pad lg:col-span-2">
            <h2 class="card-title"><span class="ikon-judul"><i class="fa-solid fa-layer-group"></i></span>Status penilaian sekolah</h2>
            <p class="card-sub mt-1 mb-4">{{ $totalStatus }} penilaian di semester ini.</p>
            @if($totalStatus > 0)
                <div class="bar-status !h-4" role="img" aria-label="Status penilaian sekolah">
                    @foreach($urutStatus as $key)
                        @if(($statusSekolah[$key] ?? 0) > 0)<span class="seg-{{ $key }}" style="flex-grow: {{ $statusSekolah[$key] }}"></span>@endif
                    @endforeach
                </div>
                <div class="mt-4 rows">
                    @foreach($urutStatus as $key)
                        <div class="flex items-center gap-3 py-2.5">
                            <span class="kotak seg-{{ $key }}"></span>
                            <span class="flex-1 text-sm font-semibold text-slate-700">{{ \App\Models\Assessment::STATUS[$key]['label'] }}</span>
                            <span class="text-sm font-extrabold tabular">{{ $statusSekolah[$key] ?? 0 }}</span>
                            <span class="w-12 text-right text-[13px] text-slate-500 tabular">{{ round(($statusSekolah[$key] ?? 0) / $totalStatus * 100) }}%</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="card card-pad lg:col-span-3">
            <h2 class="card-title"><span class="ikon-judul"><i class="fa-solid fa-user-clock"></i></span>Ketepatan unggah nilai per guru</h2>
            <p class="card-sub mt-1">Dihitung dari penilaian yang masa inputnya sudah berjalan.</p>
            <div class="legenda mt-4">
                <span class="legenda-item"><span class="kotak" style="background:#4652a8"></span>Tepat waktu</span>
                <span class="legenda-item"><span class="kotak" style="background:#c6372a"></span>Terlambat</span>
                <span class="legenda-item"><span class="kotak" style="background:#dc9a5e"></span>Masih dalam masa input</span>
            </div>
            @if($ketepatan->isNotEmpty())
                <div class="grafik mt-2" style="height: {{ $ketepatan->count() * 38 + 40 }}px"><canvas id="grafik-ketepatan" role="img" aria-label="Ketepatan unggah nilai per guru"></canvas></div>
                <details class="tabel-grafik mt-3">
                    <summary>Lihat sebagai tabel</summary>
                    <table class="tbl mt-2">
                        <thead><tr><th>Guru</th><th class="num">Tepat</th><th class="num">Terlambat</th><th class="num">Berjalan</th></tr></thead>
                        <tbody>
                            @foreach($ketepatan as $g)
                                <tr><td>{{ $g['guru'] }}</td><td class="num">{{ $g['tepat'] }}</td><td class="num">{{ $g['terlambat'] }}</td><td class="num">{{ $g['berjalan'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </details>
            @else
                <div class="empty"><i class="fa-regular fa-clock"></i>Belum ada penilaian yang masa inputnya berjalan.</div>
            @endif
        </section>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <section class="card">
            <div class="flex items-center justify-between px-5 pt-5">
                <h2 class="card-title"><span class="ikon-judul soga"><i class="fa-solid fa-lock-open"></i></span>Pengajuan buka akses</h2>
                <a href="{{ route('admin.akses') }}" class="text-sm font-bold text-brand-700">Kelola</a>
            </div>
            <div class="p-3">
                @forelse($pengajuan as $u)
                    <div class="p-2">
                        <p class="font-bold text-slate-700">{{ $u->assessment->judul }} <span class="font-semibold text-slate-400">&middot; {{ $u->assessment->subjectTeacher->label() }}</span></p>
                        <p class="text-sm text-slate-500">{{ $u->pengaju->name ?? '-' }}: {{ $u->alasan }}</p>
                    </div>
                @empty
                    <div class="empty"><i class="fa-regular fa-circle-check"></i>Tidak ada pengajuan.</div>
                @endforelse
            </div>
        </section>

        <section class="card">
            <div class="flex items-center justify-between px-5 pt-5">
                <h2 class="card-title"><span class="ikon-judul"><i class="fa-solid fa-calendar-check"></i></span>UTS dan UAS mendatang</h2>
                <a href="{{ route('admin.jadwal') }}" class="text-sm font-bold text-brand-700">Atur jadwal</a>
            </div>
            <div class="p-3">
                @forelse($ujianMendatang as $u)
                    <div class="flex items-center gap-3 p-2">
                        @include('partials.datechip', ['tanggal' => $u['contoh']->tanggal])
                        <div>
                            <p class="font-bold text-slate-700">{{ $u['contoh']->jenisLabel() }}</p>
                            <p class="text-sm text-slate-500">{{ $u['jumlah'] }} kelas-mapel</p>
                        </div>
                    </div>
                @empty
                    <div class="empty"><i class="fa-regular fa-calendar"></i>Belum ada jadwal UTS/UAS.</div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="card">
        <div class="px-5 pt-5">
            <h2 class="card-title"><span class="ikon-judul soga"><i class="fa-solid fa-hourglass-end"></i></span>Nilai melewati batas unggah</h2>
            <p class="card-sub">Tercatat sebagai keterlambatan pada rekap kinerja guru.</p>
        </div>
        <div class="p-3">
            @forelse($terlambat as $a)
                <a href="{{ route('admin.jadwal.show', $a) }}" class="flex flex-wrap items-center gap-3 p-2 rounded-xl hover:bg-slate-50">
                    @include('partials.datechip', ['tanggal' => $a->tanggal])
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-slate-700">{{ $a->judul }}</p>
                        <p class="text-sm text-slate-500">{{ $a->subjectTeacher->label() }} &middot; {{ $a->subjectTeacher->teacher->name }}</p>
                    </div>
                    <span class="status status-melewati_batas"><span class="titik titik-melewati_batas"></span>batas {{ $a->batasUnggah()->translatedFormat('d M') }}</span>
                </a>
            @empty
                <div class="empty"><i class="fa-regular fa-circle-check"></i>Tidak ada keterlambatan.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
    if (document.getElementById('grafik-ketepatan')) Grafik.ketepatan(document.getElementById('grafik-ketepatan'), @json($ketepatan));
</script>
@endsection