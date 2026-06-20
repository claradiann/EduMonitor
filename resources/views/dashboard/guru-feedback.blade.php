@extends('layouts.app')

@section('title', 'Feedback Evaluasi - EduMonitor')

@section('content')
<div class="space-y-8">

    <!-- Page Header -->
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Feedback Evaluasi</h2>
        <p class="text-slate-400 text-sm mt-1">Ulasan & penilaian dari siswa terhadap mata pelajaran yang Anda ampu.</p>
    </div>

    <!-- Evaluation Breakdown and Comments -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Breakdown per Indikator -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm lg:col-span-6">
            <h3 class="text-base font-bold text-slate-600 uppercase tracking-wider mb-6">Poin Penilaian Per Indikator</h3>

            <div class="space-y-6">
                @foreach($indicatorsBreakdown as $ind)
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-bold text-slate-600">{{ $ind['name'] }}</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm font-extrabold text-slate-800">{{ $ind['average'] }}</span>
                                <div class="flex text-amber-400 text-xs">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa-{{ $i <= round($ind['average']) ? 'solid' : 'regular' }} fa-star"></i>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <!-- Progress bar -->
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-indigo-600 h-full rounded-full transition-all" style="width: {{ ($ind['average'] / 5) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Ulasan / Komentar Tertulis (Anonim) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm lg:col-span-6 flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-600 uppercase tracking-wider mb-6">Ulasan Tertulis dari Siswa (Anonim)</h3>

                <div class="space-y-4 max-h-[380px] overflow-y-auto pr-1">
                    @forelse($responses as $resp)
                        @if($resp->comments)
                            <div class="p-4 bg-indigo-50/40 border border-indigo-100/30 rounded-2xl">
                                <div class="flex justify-between items-center text-xs font-semibold text-indigo-500 mb-2">
                                    <span>Siswa Kelas {{ $resp->student->kelas->nama_kelas }}</span>
                                    <span>{{ $resp->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-slate-600 italic">"{{ $resp->comments }}"</p>
                            </div>
                        @endif
                    @empty
                        <div class="text-center py-12 text-slate-400">
                            <i class="fa-regular fa-comment-dots text-3xl mb-3 block"></i>
                            <p class="text-sm font-medium">Belum ada ulasan tertulis dari siswa.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection