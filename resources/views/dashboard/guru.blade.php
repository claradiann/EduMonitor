@extends('layouts.app')

@section('title', 'Beranda Guru - EduMonitor')

@section('content')
<div class="space-y-6">
    @php($grafikKelas = $kelas->filter(fn ($k) => $k['grafik']->isNotEmpty())->mapWithKeys(fn ($k) => [$k['st']->label() => $k['grafik']]))
    @include('partials.sapaan', ['nama' => Auth::user()->name])

    <!-- Perlu input nilai -->
    <section class="card">
        <div class="px-5 pt-5">
            <h2 class="card-title"><span class="ikon-judul soga"><i class="fa-solid fa-pen-to-square"></i></span>Perlu input nilai</h2>
            <p class="card-sub">Masa input nilai 7 hari sejak tanggal penilaian. Setelah itu nilai terkunci.</p>
        </div>
        <div class="p-3">
            @forelse($perluInput as $a)
                @php($status = $a->statusKelas($jumlahPerKelas[$a->subjectTeacher->kelas_id] ?? 0))
                @php($sisa = (int) now()->diffInDays($a->batasUnggah(), false))
                <a href="{{ route('guru.penilaian.input', $a) }}" class="flex flex-wrap items-center gap-3 p-3 rounded-xl hover:bg-slate-50">
                    @include('partials.datechip', ['tanggal' => $a->tanggal])
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-slate-800">{{ $a->judul }}</p>
                        <p class="text-sm text-slate-500">{{ $a->subjectTeacher->label() }} &middot; {{ $a->labelJenis() }} &middot; {{ $a->scores->count() }} siswa dinilai</p>
                    </div>
                    @if($status === 'melewati_batas')
                        @include('partials.status-penilaian', ['status' => $status])
                    @else
                        <span class="status {{ $sisa <= 1 ? 'status-melewati_batas' : 'status-menunggu_nilai' }}"><span class="titik titik-menunggu_nilai" style="{{ $sisa <= 1 ? '--c: var(--bata)' : '' }}"></span>{{ $sisa < 1 ? 'Hari terakhir' : 'Sisa ' . $sisa . ' hari' }}</span>
                    @endif
                </a>
            @empty
                <div class="empty"><i class="fa-regular fa-circle-check"></i>Semua nilai sudah masuk. Tidak ada yang perlu diinput.</div>
            @endforelse
        </div>
    </section>

    @if($grafikKelas->isNotEmpty())
        <section class="card card-pad">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="card-title"><span class="ikon-judul"><i class="fa-solid fa-chart-column"></i></span>Rata-rata kelas per penilaian</h2>
                    <p class="card-sub mt-1">Bantu melihat materi mana yang perlu diulang.</p>
                </div>
                <label class="sr-only" for="pilih-kelas">Kelas</label>
                <select id="pilih-kelas" class="input !w-auto !py-2 !text-sm font-semibold">
                    @foreach($grafikKelas->keys() as $label)<option value="{{ $label }}">{{ $label }}</option>@endforeach
                </select>
            </div>
            <div class="grafik h-[240px] mt-4"><canvas id="grafik-kelas" role="img" aria-label="Rata-rata nilai kelas per penilaian"></canvas></div>
            <p id="kelas-catatan" class="text-sm text-slate-700 mt-3"></p>
        </section>
    @endif

    <div class="grid lg:grid-cols-5 gap-6">
        <!-- Kelas yang diampu -->
        <section class="lg:col-span-3 space-y-3">
            <h2 class="section-title"><span class="ikon-judul"><i class="fa-solid fa-users"></i></span>Kelas yang diampu</h2>
            @foreach($kelas as $k)
                <a href="{{ route('guru.penilaian', ['kelas' => $k['st']->id]) }}" class="card card-pad block hover:border-brand-300">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-bold text-slate-800">{{ $k['st']->subject->nama_mapel }}</p>
                            <p class="text-sm text-slate-500">Kelas {{ $k['st']->kelas->nama_kelas }} &middot; {{ $k['siswa'] }} siswa</p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-slate-300 mt-1"></i>
                    </div>
                    @php($urut = ['nilai_tersedia', 'melewati_batas', 'menunggu_nilai', 'dijadwalkan'])
                    @if($k['status']->isEmpty())
                        <p class="text-sm text-slate-500 mt-3">Belum ada penilaian.</p>
                    @else
                        <div class="flex flex-wrap gap-[5px] mt-3" aria-hidden="true">
                            @foreach($urut as $key)
                                @for($i = 0; $i < ($k['status'][$key] ?? 0); $i++)<span class="titik titik-{{ $key }}"></span>@endfor
                            @endforeach
                        </div>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2">
                            @foreach($urut as $key)
                                @if(($k['status'][$key] ?? 0) > 0)
                                    <span class="status status-{{ $key }} !font-semibold"><span class="tabular font-extrabold">{{ $k['status'][$key] }}</span> {{ strtolower(\App\Models\Assessment::STATUS[$key]['label']) }}</span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    @if($k['ortu_total'] > 0)
                        <div class="mt-3">
                            <div class="flex justify-between text-xs font-semibold text-slate-500 mb-1">
                                <span><i class="fa-solid fa-check-double mr-1"></i>Orang tua sudah membaca nilai terbaru</span>
                                <span>{{ $k['ortu_baca'] }}/{{ $k['ortu_total'] }}</span>
                            </div>
                            <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden"><div class="h-full bg-nila-600 rounded-full" style="width: {{ round($k['ortu_baca'] / $k['ortu_total'] * 100) }}%"></div></div>
                        </div>
                    @endif
                </a>
            @endforeach
        </section>

        <!-- Jadwal 2 minggu & evaluasi -->
        <div class="lg:col-span-2 space-y-6">
            <section class="card">
                <div class="px-5 pt-5"><h2 class="card-title"><span class="ikon-judul"><i class="fa-solid fa-calendar-days"></i></span>Jadwal 2 minggu ke depan</h2></div>
                <div class="p-3">
                    @forelse($mendatang as $a)
                        <div class="flex items-center gap-3 p-2">
                            @include('partials.datechip', ['tanggal' => $a->tanggal])
                            <div class="min-w-0">
                                <p class="font-bold text-slate-700 truncate">{{ $a->judul }}</p>
                                <p class="text-sm text-slate-500 truncate">{{ $a->subjectTeacher->label() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="empty"><i class="fa-regular fa-calendar"></i>Belum ada jadwal.</div>
                    @endforelse
                </div>
            </section>

            <a href="{{ route('guru.feedback') }}" class="card card-pad flex items-center gap-4 hover:border-brand-300">
                <i class="fa-solid fa-star text-soga-500 text-lg"></i>
                <span class="flex-1">
                    <span class="block stat-label">Skor evaluasi dari siswa</span>
                    <span class="block text-2xl font-extrabold text-slate-800">{{ $skorEvaluasi !== null ? str_replace('.', ',', $skorEvaluasi) : '-' }} <span class="text-sm font-semibold text-slate-400">/ 5</span></span>
                </span>
                <i class="fa-solid fa-chevron-right text-slate-300"></i>
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        const data = @json($grafikKelas);
        const kanvas = document.getElementById('grafik-kelas');
        if (!kanvas) return;
        const pilih = document.getElementById('pilih-kelas');
        const catatan = document.getElementById('kelas-catatan');
        let grafik;
        function tampil(kelas) {
            if (grafik) grafik.destroy();
            const d = data[kelas];
            grafik = Grafik.rataPenilaian(kanvas, d);
            const terendah = d.reduce((a, b) => (b.rata < a.rata ? b : a));
            catatan.textContent = d.length > 1
                ? 'Rata-rata terendah ada di ' + terendah.label + ' (' + Grafik.angka(terendah.rata) + ').'
                : '';
        }
        pilih.addEventListener('change', () => tampil(pilih.value));
        tampil(pilih.value);
    })();
</script>
@endsection