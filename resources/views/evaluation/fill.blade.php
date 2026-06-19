@extends('layouts.app')

@section('title', 'Kuesioner Evaluasi - EduMonitor')

@section('styles')
<style>
    /* Premium Star selection overrides */
    .star-btn {
        transition: transform 0.15s ease;
    }
    .star-btn:hover {
        transform: scale(1.2);
    }
    .star-active {
        color: #fbbf24 !important; /* amber-400 */
    }
</style>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-8">
    
    <!-- Header Back navigation -->
    <div class="flex items-center gap-4">
        <a href="{{ route('dashboard') }}" class="w-10 h-10 bg-white hover:bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center text-slate-600 transition-all hover-lift">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800">Isi Kuesioner Evaluasi</h2>
            <p class="text-slate-400 text-sm font-semibold">Kembali ke Beranda</p>
        </div>
    </div>

    <!-- Teacher/Mapel Banner Info -->
    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center gap-6">
        <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-500 font-bold text-2xl">
            <i class="fa-solid fa-chalkboard-user"></i>
        </div>
        <div>
            <span class="text-[10px] font-extrabold tracking-wider uppercase text-indigo-500 bg-indigo-50 py-1 px-2.5 rounded-lg">Evaluasi Mapel</span>
            <h3 class="text-xl font-extrabold text-slate-700 mt-1.5">{{ $subjectTeacher->subject->nama_mapel }}</h3>
            <p class="text-slate-400 text-sm font-medium mt-0.5">Guru Pengampu: <strong class="text-slate-600">{{ $subjectTeacher->teacher->name }}</strong> (Kelas {{ $subjectTeacher->kelas->nama_kelas }})</p>
        </div>
    </div>

    <!-- Questionnaire Card Form -->
    <div class="bg-white rounded-[2rem] p-8 border border-slate-100 shadow-sm">
        <div class="border-b border-slate-100 pb-6 mb-8">
            <h4 class="text-lg font-bold text-slate-700">Instrumen Evaluasi Standar Sekolah</h4>
            <p class="text-xs text-slate-400 mt-1 font-semibold">Berikan penilaian Anda secara objektif berdasarkan skala 1 (Sangat Kurang) sampai 5 (Sangat Baik) pada setiap indikator di bawah.</p>
        </div>

        <form action="{{ route('evaluation.submit', $subjectTeacher->id) }}" method="POST" class="space-y-8">
            @csrf

            <!-- Indikator loop -->
            @foreach($indicators as $index => $ind)
                <div class="space-y-3">
                    <div class="flex justify-between items-start gap-4">
                        <div>
                            <h5 class="font-bold text-slate-700 text-sm">{{ $index + 1 }}. {{ $ind->indicator_name }}</h5>
                            <p class="text-xs text-slate-400 font-medium mt-1 leading-relaxed">{{ $ind->description }}</p>
                        </div>
                    </div>

                    <!-- Star Ratings selection -->
                    <div class="flex items-center gap-2 pt-1">
                        <!-- Hidden radio inputs -->
                        @for($score = 1; $score <= 5; $score++)
                            <input type="radio" 
                                   name="rating_{{ $ind->id }}" 
                                   value="{{ $score }}" 
                                   id="rating_{{ $ind->id }}_{{ $score }}" 
                                   required 
                                   class="hidden">
                        @endfor

                        <!-- Stars indicators -->
                        <div class="flex items-center gap-2">
                            @for($score = 1; $score <= 5; $score++)
                                <button type="button" 
                                        onclick="setRating({{ $ind->id }}, {{ $score }})" 
                                        id="star_{{ $ind->id }}_{{ $score }}"
                                        class="star-btn text-2xl text-slate-200 cursor-pointer focus:outline-none">
                                    <i class="fa-solid fa-star"></i>
                                </button>
                            @endfor
                        </div>

                        <!-- Rating helper status -->
                        <span id="rating_label_{{ $ind->id }}" class="text-xs font-bold text-slate-400 ml-4 italic uppercase tracking-wider">Pilih Skor</span>
                    </div>
                </div>
                
                @if(!$loop->last)
                    <hr class="border-slate-100">
                @endif
            @endforeach

            <hr class="border-slate-100">

            <!-- Comments / Saran tambahan -->
            <div class="space-y-2">
                <label for="comments" class="text-sm font-bold text-slate-700 flex items-center gap-2">
                    <i class="fa-regular fa-comment text-indigo-500 text-base"></i>
                    <span>Catatan & Masukan Tambahan (Ulasan Anonim)</span>
                </label>
                <p class="text-xs text-slate-400 font-medium leading-relaxed">Berikan saran atau kritik membangun untuk meningkatkan cara mengajar. Komentar Anda akan disampaikan tanpa menyebutkan nama Anda.</p>
                <textarea name="comments" 
                          id="comments" 
                          rows="4" 
                          placeholder="Tuliskan komentar Anda di sini..."
                          class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 text-sm transition-all"></textarea>
            </div>

            <!-- Submit buttons -->
            <div class="pt-4 flex items-center gap-4">
                <button type="submit" 
                        id="btn-submit-evaluation"
                        class="px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl transition-all shadow-lg shadow-indigo-100 flex items-center gap-2 hover-lift">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>KIRIM EVALUASI GURU</span>
                </button>
                <a href="{{ route('dashboard') }}" class="px-6 py-4 bg-slate-100 hover:bg-slate-200 text-slate-500 font-bold rounded-2xl transition-all text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const scoreLabels = {
        1: 'Sangat Kurang',
        2: 'Kurang',
        3: 'Cukup',
        4: 'Baik',
        5: 'Sangat Baik'
    };

    function setRating(indicatorId, score) {
        // Update input radio
        document.getElementById(`rating_${indicatorId}_${score}`).checked = true;

        // Update stars visuals
        for (let i = 1; i <= 5; i++) {
            const star = document.getElementById(`star_${indicatorId}_${i}`);
            if (i <= score) {
                star.classList.add('star-active');
                star.classList.remove('text-slate-200');
            } else {
                star.classList.remove('star-active');
                star.classList.add('text-slate-200');
            }
        }

        // Update rating text label
        const label = document.getElementById(`rating_label_${indicatorId}`);
        label.innerText = scoreLabels[score];
        label.className = "text-xs font-bold text-amber-500 ml-4 uppercase tracking-wider";
    }
</script>
@endsection
