@extends('layouts.app')

@section('title', 'Evaluasi - EduMonitor')

@section('content')
<div class="space-y-8">

    <!-- Header -->
    <div>
        <h2 class="text-3xl font-extrabold tracking-tight text-slate-800">Evaluasi Pembelajaran</h2>
        <p class="text-slate-400 text-sm mt-1 font-medium">Kuesioner evaluasi guru pengampu mata pelajaranmu</p>
    </div>

    <!-- Evaluasi Pembelajaran Fitur -->
    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
        <h3 class="text-base font-bold text-slate-600 uppercase tracking-wider mb-4">
            <i class="fa-solid fa-pen-to-square mr-2 text-indigo-500"></i>Daftar Mata Pelajaran
        </h3>
        <p class="text-xs text-slate-400 mb-6 font-medium">
            Pilih mata pelajaran di bawah untuk mengevaluasi guru pengampu menggunakan instrumen standar sekolah.
        </p>

        <div class="space-y-3">
            @forelse($subjectTeachers as $st)
                <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-100 rounded-2xl hover:border-slate-200 transition-colors">
                    <div>
                        <h4 class="font-bold text-slate-700 text-sm">{{ $st['subject'] }}</h4>
                        <p class="text-xs text-slate-400 font-medium">{{ $st['teacher'] }}</p>
                    </div>
                    <div>
                        @if($st['evaluated'])
                            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-600 text-sm font-bold px-4 py-2 rounded-xl border border-emerald-100">
                                <i class="fa-solid fa-circle-check"></i> Sudah Dievaluasi
                            </span>
                        @else
                            <a href="{{ route('evaluation.fill', $st['id']) }}" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2 rounded-xl transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5">
                                Isi Evaluasi <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-slate-400 text-sm font-medium">Belum ada mata pelajaran untuk dievaluasi.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection