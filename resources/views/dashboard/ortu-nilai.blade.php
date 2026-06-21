@extends('layouts.app')

@section('title', 'Nilai Siswa - EduMonitor')

@section('content')
<div class="space-y-8">

    @if(!$student)
        <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm text-center">
            <div class="w-16 h-16 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-circle-exclamation text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800">Siswa Tidak Ditemukan</h3>
            <p class="text-slate-400 mt-2">Akun orang tua ini belum terhubung dengan data siswa mana pun.</p>
        </div>
    @else
    <!-- Header -->
    <div>
        <h2 class="text-3xl font-extrabold tracking-tight text-slate-800">Nilai {{ $student->name }}</h2>
        <p class="text-slate-400 text-sm mt-1 font-medium">{{ $kelas->nama_kelas ?? '-' }} &middot; Semester Genap 2025/2026</p>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Rata-rata Nilai -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center gap-6">
            <div class="relative w-20 h-20 flex items-center justify-center rounded-full border-4 border-indigo-500 font-extrabold text-3xl text-indigo-600">
                {{ $avgGrade }}
            </div>
            <div>
                <h3 class="font-bold text-slate-700 text-lg">Rata-rata Nilai</h3>
                <div class="flex items-center gap-1.5 mt-1">
                    <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-600 text-xs font-bold px-2.5 py-1 rounded-full">
                        <i class="fa-solid fa-arrow-trend-up"></i>
                        + 3 poin
                    </span>
                    <span class="text-xs text-slate-400 font-semibold">dari semester lalu</span>
                </div>
            </div>
        </div>

        <!-- Mapel Count -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-400 text-sm tracking-wider uppercase">Mapel Diambil</h3>
                <p class="text-5xl font-extrabold text-slate-700 mt-2">{{ $grades->count() }}</p>
            </div>
            <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-500">
                <i class="fa-solid fa-book-open text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Bar Chart: Nilai per Mapel -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm lg:col-span-8">
            <h3 class="text-base font-bold text-slate-600 uppercase tracking-wider mb-6">Perkembangan Nilai Semester Genap 2025/2026</h3>
            <div class="h-64 relative">
                <canvas id="chartNilaiMapel"></canvas>
            </div>
        </div>

        <!-- Horizontal Bar Chart: Per Semester -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm lg:col-span-4">
            <h3 class="text-base font-bold text-slate-600 uppercase tracking-wider mb-6">Perkembangan Nilai Per Semester</h3>
            <div class="space-y-4">
                @foreach($allSemesters as $sem => $val)
                    <div>
                        <div class="flex justify-between text-xs font-bold text-slate-500 mb-1">
                            <span>{{ strtoupper($sem) }}</span>
                            <span class="text-indigo-600">{{ $val > 0 ? $val : '0.0' }}</span>
                        </div>
                        <div class="w-full bg-slate-100 h-6 rounded-full overflow-hidden relative">
                            <div class="bg-indigo-600 h-full rounded-full transition-all duration-500" style="width: {{ $val > 0 ? $val : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Detail Nilai per Mapel -->
    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
        <h3 class="text-base font-bold text-slate-600 uppercase tracking-wider mb-4">Detail Nilai per Mapel</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 pr-4 font-bold">Mapel</th>
                        <th class="py-3 pr-4 font-bold">Tugas</th>
                        <th class="py-3 pr-4 font-bold">UH</th>
                        <th class="py-3 pr-4 font-bold">UTS</th>
                        <th class="py-3 pr-4 font-bold">UAS</th>
                        <th class="py-3 pr-4 font-bold">Rata-rata</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($grades as $g)
                        <tr class="border-b border-slate-50 last:border-0">
                            <td class="py-3 pr-4 font-bold text-slate-700">{{ $g->subject->nama_mapel ?? $g->subject->kode_mapel }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $g->nilai_tugas }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $g->nilai_uh }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $g->nilai_uts }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $g->nilai_uas }}</td>
                            <td class="py-3 pr-4 font-bold text-indigo-600">{{ $g->average }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400 font-medium">Belum ada data nilai semester ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Catatan Guru -->
    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
        <h3 class="text-base font-bold text-slate-600 uppercase tracking-wider mb-4">
            <i class="fa-regular fa-comment-dots mr-2 text-indigo-500"></i>Catatan Guru
        </h3>
        <div class="space-y-3">
            @forelse($notes as $note)
                <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-50/20 text-slate-700 text-sm leading-relaxed">
                    <span class="font-bold text-indigo-600">{{ $note->teacher->name }} - {{ $note->subject->kode_mapel }}</span>:
                    <p class="mt-1 text-slate-600">{{ $note->catatan }}</p>
                </div>
            @empty
                <p class="text-slate-400 text-sm font-medium">Belum ada catatan dari guru.</p>
            @endforelse
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
@if($student)
<script>
    const chartNilaiMapelData = {
        labels: {!! json_encode($grades->map(fn($g) => $g->subject->kode_mapel)) !!},
        data: {!! json_encode($grades->map(fn($g) => $g->average)) !!}
    };
</script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@endif
@endsection