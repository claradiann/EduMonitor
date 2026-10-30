@extends('layouts.app')

@section('title', $ekskul->nama . ' - EduMonitor')

@php($isAdmin = Auth::user()->role === 'admin')

@section('content')
<div class="space-y-6">
    <a href="{{ $isAdmin ? route('admin.ekskul') : route('guru.ekskul') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-brand-700"><i class="fa-solid fa-arrow-left"></i>Kembali</a>

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="page-title">{{ $ekskul->nama }}</h1>
            <p class="page-sub">Pembina: {{ $ekskul->pembina->name ?? 'belum ditetapkan' }} &middot; {{ $anggota->count() }} anggota &middot; Semester {{ $semester }}</p>
        </div>
        <a href="{{ route('ekskul.ekspor', $ekskul) }}" class="btn btn-soft"><i class="fa-solid fa-file-export"></i>Ekspor nilai</a>
    </div>

    @if($batas)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <i class="fa-solid fa-clock mr-1"></i>Batas pengumpulan nilai akhir: <b>{{ $batas->translatedFormat('l, d F Y') }}</b>.
        </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6 items-start">
        <form method="POST" action="{{ route('ekskul.nilai', $ekskul) }}" class="card overflow-hidden {{ $isAdmin ? 'lg:col-span-2' : 'lg:col-span-3' }}">
            @csrf
            <div class="px-5 py-4 border-b border-[color:var(--line)]">
                <h2 class="card-title">Nilai anggota</h2>
                <p class="card-sub">A = Sangat Baik, B = Baik, C = Cukup, D = Kurang.</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($anggota as $a)
                    <div class="p-4 grid md:grid-cols-[1fr_120px_2fr_auto] gap-3 items-start">
                        <div>
                            <p class="font-bold text-slate-800">{{ $a->student->name }}</p>
                            <p class="text-xs text-slate-400">Kelas {{ $a->student->kelas->nama_kelas ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="sr-only" for="pred-{{ $a->id }}">Predikat</label>
                            <select id="pred-{{ $a->id }}" name="predikat[{{ $a->id }}]" class="input">
                                <option value="">Predikat</option>
                                @foreach(\App\Models\Ekstrakurikuler::PREDIKAT as $k => $v)
                                    <option value="{{ $k }}" @selected($a->predikat === $k)>{{ $k }} ({{ $v }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="sr-only" for="desk-{{ $a->id }}">Deskripsi</label>
                            <textarea id="desk-{{ $a->id }}" name="deskripsi[{{ $a->id }}]" rows="2" maxlength="500" class="input" placeholder="Deskripsi singkat perkembangan siswa">{{ $a->deskripsi }}</textarea>
                        </div>
                        @if($isAdmin)
                            <button form="hapus-{{ $a->id }}" class="btn btn-danger btn-sm" aria-label="Hapus anggota"><i class="fa-solid fa-xmark"></i></button>
                        @endif
                    </div>
                @empty
                    <div class="empty"><i class="fa-solid fa-user-plus"></i>Belum ada anggota.</div>
                @endforelse
            </div>
            @if($anggota->isNotEmpty())
                <div class="px-5 py-4 border-t border-[color:var(--line)] flex justify-end">
                    <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i>Simpan nilai</button>
                </div>
            @endif
        </form>

        @if($isAdmin)
            @foreach($anggota as $a)
                <form id="hapus-{{ $a->id }}" method="POST" action="{{ route('admin.ekskul.anggota.hapus', [$ekskul, $a]) }}" class="hidden">@csrf @method('DELETE')</form>
            @endforeach

            <section class="card card-pad">
                <h2 class="card-title">Tambah anggota</h2>
                <p class="card-sub mb-3">Centang siswa yang mengikuti ekstrakurikuler ini.</p>
                <form method="POST" action="{{ route('admin.ekskul.anggota', $ekskul) }}" class="space-y-3">
                    @csrf
                    <label for="cari-siswa" class="sr-only">Cari siswa</label>
                    <input id="cari-siswa" class="input" placeholder="Cari nama siswa" oninput="filterSiswa(this.value)">
                    <div class="max-h-80 overflow-y-auto border border-[color:var(--line)] rounded-xl divide-y divide-slate-100">
                        @forelse($calon as $s)
                            <label class="calon flex items-center gap-3 px-3 py-2 text-sm hover:bg-slate-50" data-nama="{{ strtolower($s->name) }}">
                                <input type="checkbox" name="student_ids[]" value="{{ $s->id }}">
                                <span class="flex-1">{{ $s->name }}</span>
                                <span class="text-xs text-slate-400">{{ $s->kelas->nama_kelas ?? '-' }}</span>
                            </label>
                        @empty
                            <p class="p-3 text-sm text-slate-400">Semua siswa sudah menjadi anggota.</p>
                        @endforelse
                    </div>
                    <button class="btn btn-primary w-full"><i class="fa-solid fa-user-plus"></i>Tambahkan</button>
                </form>
            </section>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    function filterSiswa(q) {
        q = q.toLowerCase();
        document.querySelectorAll('.calon').forEach(el => el.classList.toggle('hidden', !el.dataset.nama.includes(q)));
    }
</script>
@endsection