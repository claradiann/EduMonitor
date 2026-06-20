@extends('layouts.app')

@section('title', 'Rekap Evaluasi - EduMonitor')

@section('content')
<div class="space-y-8">

    <!-- Top Heading -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-[2rem] p-8 text-white relative overflow-hidden shadow-lg shadow-indigo-100">
        <div class="absolute right-0 top-0 w-80 h-full opacity-10 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-white via-indigo-200 to-indigo-900 pointer-events-none"></div>
        <h2 class="text-4xl font-extrabold tracking-tight">Rekap Evaluasi</h2>
        <p class="text-indigo-100 text-sm mt-1 font-medium">Pantau kualitas pengajaran melalui rekap kuesioner evaluasi siswa.</p>
    </div>

    <div id="recap-section" class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Rata-rata Skor per Indikator Sekolah -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm lg:col-span-5">
            <h3 class="text-base font-bold text-slate-600 uppercase tracking-wider mb-6">Poin Rata-rata Sekolah Per Indikator</h3>
            <div class="space-y-6">
                @foreach($indicators as $ind)
                    <div>
                        <div class="flex justify-between items-center mb-1 text-xs font-bold text-slate-500">
                            <span>{{ $ind['name'] }}</span>
                            <span class="text-indigo-600 font-extrabold">{{ $ind['average'] }}</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-indigo-600 h-full rounded-full transition-all" style="width: {{ ($ind['average'] / 5) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Rankings/Peringkat Guru -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm lg:col-span-7">
            <h3 class="text-base font-bold text-slate-600 uppercase tracking-wider mb-6">Peringkat & Rekap Nilai Evaluasi Guru</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs font-bold text-slate-400 uppercase">
                            <th class="py-3 px-2">Guru</th>
                            <th class="py-3 px-2 text-center">Rata-rata Skor</th>
                            <th class="py-3 px-2 text-center">Kuesioner Masuk</th>
                            <th class="py-3 px-2 text-right">Predikat</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm font-medium text-slate-600">
                        @forelse($teachersEvaluations as $t)
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-2">
                                    <div class="font-bold text-slate-700">{{ $t['name'] }}</div>
                                    <div class="text-xs text-slate-400 font-semibold">{{ $t['nip'] }}</div>
                                </td>
                                <td class="py-4 px-2 text-center font-bold text-indigo-600">
                                    <div class="flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-star text-amber-400 text-xs"></i>
                                        <span>{{ $t['average_rating'] }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-2 text-center text-slate-500">
                                    {{ $t['responses_count'] }} ulasan
                                </td>
                                <td class="py-4 px-2 text-right">
                                    @if($t['average_rating'] >= 4.5)
                                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-full text-xs font-bold">Sangat Baik</span>
                                    @elseif($t['average_rating'] >= 4.0)
                                        <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-full text-xs font-bold">Baik</span>
                                    @elseif($t['average_rating'] > 0)
                                        <span class="px-2.5 py-1 bg-amber-50 text-amber-600 rounded-full text-xs font-bold">Cukup</span>
                                    @else
                                        <span class="px-2.5 py-1 bg-slate-100 text-slate-400 rounded-full text-xs font-semibold">Belum Dinilai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-6 text-slate-400 font-medium">Data evaluasi guru belum tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection