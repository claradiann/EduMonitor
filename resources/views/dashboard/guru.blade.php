@extends('layouts.app')

@section('title', 'Dashboard Guru - EduMonitor')

@section('content')
<div class="space-y-8">
    
    <!-- Top Greeting Banner -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-[2rem] p-8 text-white relative overflow-hidden shadow-lg shadow-indigo-100">
        <div class="absolute right-0 top-0 w-80 h-full opacity-10 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-white via-indigo-200 to-indigo-900 pointer-events-none"></div>
        <span class="bg-white/20 text-white font-bold text-xs uppercase px-3 py-1.5 rounded-full tracking-wider inline-block mb-3">
            NIP: {{ Auth::user()->nip }}
        </span>
        <h2 class="text-4xl font-extrabold tracking-tight">Halo, {{ Auth::user()->name }}!</h2>
        <p class="text-indigo-100 text-sm mt-1 font-medium">Ulasan & Feedback Evaluasi Ketercapaian Pembelajaran Siswa</p>
    </div>

    <!-- Stats row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Rata-rata Skor Evaluasi -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between hover-lift">
            <div class="flex items-center gap-6">
                <!-- Circular average indicator -->
                <div class="relative w-20 h-20 flex items-center justify-center rounded-full border-4 border-emerald-500 font-extrabold text-3xl text-emerald-600">
                    {{ $averageRating }}
                </div>
                <div>
                    <h3 class="font-bold text-slate-700 text-lg">Skor Evaluasi Guru</h3>
                    <div class="flex items-center gap-1 mt-1 text-slate-400 text-xs font-semibold">
                        <span>Skala 1 - 5 dari Siswa</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Siswa Diajar -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between hover-lift">
            <div>
                <h3 class="font-bold text-slate-400 text-sm tracking-wider uppercase">Siswa Diajar</h3>
                <p class="text-5xl font-extrabold text-slate-700 mt-2">{{ $totalStudentsTaught }}</p>
            </div>
            <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-500">
                <i class="fa-solid fa-users text-2xl"></i>
            </div>
        </div>

        <!-- Total Umpan Balik -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between hover-lift">
            <div>
                <h3 class="font-bold text-slate-400 text-sm tracking-wider uppercase">Total Kuesioner Masuk</h3>
                <p class="text-5xl font-extrabold text-slate-700 mt-2">{{ $responses->count() }}</p>
            </div>
            <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-500">
                <i class="fa-solid fa-paper-plane text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Kelas & Mapel Diajar -->
    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
        <h3 class="text-base font-bold text-slate-600 uppercase tracking-wider mb-4">Kelas & Mata Pelajaran Diampu</h3>
        <div class="flex flex-wrap gap-3">
            @foreach($subjectTeachers as $st)
                <div class="px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500 font-bold">
                        {{ $st->kelas->nama_kelas }}
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-700 text-sm">{{ $st->subject->nama_mapel }}</h4>
                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">{{ $st->subject->kode_mapel }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Evaluation Breakdown and Comments -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" id="feedback-section">
        
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
