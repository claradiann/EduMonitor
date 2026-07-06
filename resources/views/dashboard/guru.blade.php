@extends('layouts.app')

@section('title', 'Dashboard Guru - EduMonitor')

@section('content')
<div class="space-y-8">
    
    <!-- Greeting -->
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Halo, {{ Auth::user()->name }} 👋</h2>
        <p class="text-slate-400 text-sm mt-1">NIP: {{ Auth::user()->nip }} &mdash; Ulasan & Feedback Evaluasi Ketercapaian Pembelajaran Siswa</p>
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
        <p class="text-xs text-slate-400 mb-4">Klik salah satu kelas untuk lihat detail siswa dan rekap evaluasi.</p>
        <div class="flex flex-wrap gap-3">
            @foreach($classDetails as $index => $cd)
                <button type="button"
                        onclick="openClassDetail({{ $index }})"
                        class="px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl flex items-center gap-3 hover:bg-indigo-50 hover:border-indigo-200 transition-all hover-lift cursor-pointer text-left">
                    <div class="w-10 h-10 shrink-0 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500">
                        <i class="fa-solid fa-chalkboard text-base"></i>
                    </div>
                    <div>
                        <span class="inline-block text-[10px] font-bold text-indigo-500 uppercase tracking-wider mb-0.5">{{ $cd['kelas'] }}</span>
                        <h4 class="font-bold text-slate-700 text-sm leading-tight">{{ $cd['mapel'] }}</h4>
                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">{{ $cd['kode_mapel'] }}</p>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-xs ml-2"></i>
                </button>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal Detail Kelas -->
<div id="class-detail-overlay" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 hidden" onclick="closeClassDetail()"></div>
<div id="class-detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 pointer-events-none">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg max-h-[85vh] overflow-y-auto pointer-events-auto">
        <div class="p-6 border-b border-slate-100 flex items-start justify-between">
            <div>
                <h3 id="cd-title" class="text-xl font-bold text-slate-800"></h3>
                <p id="cd-subtitle" class="text-slate-400 text-sm mt-1"></p>
            </div>
            <button type="button" onclick="closeClassDetail()" class="w-9 h-9 rounded-xl bg-slate-50 hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-all">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-6 grid grid-cols-3 gap-3">
            <div class="bg-slate-50 rounded-2xl p-4 text-center">
                <p id="cd-total-siswa" class="text-2xl font-extrabold text-slate-800"></p>
                <p class="text-[10px] text-slate-400 font-semibold uppercase mt-1">Total Siswa</p>
            </div>
            <div class="bg-slate-50 rounded-2xl p-4 text-center">
                <p id="cd-sudah-isi" class="text-2xl font-extrabold text-slate-800"></p>
                <p class="text-[10px] text-slate-400 font-semibold uppercase mt-1">Sudah Isi</p>
            </div>
            <div class="bg-emerald-50 rounded-2xl p-4 text-center">
                <p id="cd-rata-rata" class="text-2xl font-extrabold text-emerald-600"></p>
                <p class="text-[10px] text-slate-400 font-semibold uppercase mt-1">Rata-rata</p>
            </div>
        </div>

        <div class="px-6 pb-6">
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Daftar Siswa</h4>
            <div id="cd-student-list" class="space-y-2"></div>
        </div>
    </div>
</div>

<script>
    const classDetails = @json($classDetails);

    function openClassDetail(index) {
        const cd = classDetails[index];

        document.getElementById('cd-title').textContent = cd.kelas + ' — ' + cd.mapel;
        document.getElementById('cd-subtitle').textContent = 'Kode Mapel: ' + cd.kode_mapel;
        document.getElementById('cd-total-siswa').textContent = cd.total_siswa;
        document.getElementById('cd-sudah-isi').textContent = cd.sudah_mengisi;
        document.getElementById('cd-rata-rata').textContent = cd.rata_rata > 0 ? cd.rata_rata : '-';

        const listEl = document.getElementById('cd-student-list');
        listEl.innerHTML = '';

        if (cd.siswa.length === 0) {
            listEl.innerHTML = '<p class="text-sm text-slate-400 text-center py-6">Belum ada siswa di kelas ini.</p>';
        } else {
            cd.siswa.forEach(function (s) {
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100';

                const badge = s.sudah_evaluasi
                    ? '<span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-600">Sudah Isi</span>'
                    : '<span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-full bg-amber-100 text-amber-600">Belum Isi</span>';

                const btnInput = `<button onclick="openInputNilaiModal(${s.id}, ${cd.subject_id}, '${s.name}')" class="text-[10px] font-bold uppercase px-3 py-1.5 rounded-full bg-indigo-100 text-indigo-600 hover:bg-indigo-200 transition-colors">Beri Nilai</button>`;

                row.innerHTML =
                    '<div>' +
                        '<p class="text-sm font-bold text-slate-700">' + s.name + '</p>' +
                        '<p class="text-[11px] text-slate-400">NISN: ' + s.nisn + '</p>' +
                    '</div>' +
                    '<div class="flex items-center gap-2">' + badge + btnInput + '</div>';

                listEl.appendChild(row);
            });
        }

        document.getElementById('class-detail-overlay').classList.remove('hidden');
        document.getElementById('class-detail-modal').classList.remove('hidden');
        document.getElementById('class-detail-modal').classList.add('flex');
    }

    function closeClassDetail() {
        document.getElementById('class-detail-overlay').classList.add('hidden');
        document.getElementById('class-detail-modal').classList.add('hidden');
        document.getElementById('class-detail-modal').classList.remove('flex');
    }

    // Input Nilai Logic
    function openInputNilaiModal(studentId, subjectId, studentName) {
        document.getElementById('in-student-id').value = studentId;
        document.getElementById('in-subject-id').value = subjectId;
        document.getElementById('in-student-name').textContent = studentName;
        
        document.getElementById('input-nilai-overlay').classList.remove('hidden');
        document.getElementById('input-nilai-modal').classList.remove('hidden');
        document.getElementById('input-nilai-modal').classList.add('flex');
    }

    function closeInputNilaiModal() {
        document.getElementById('input-nilai-overlay').classList.add('hidden');
        document.getElementById('input-nilai-modal').classList.add('hidden');
        document.getElementById('input-nilai-modal').classList.remove('flex');
        document.getElementById('form-input-nilai').reset();
    }

    document.getElementById('form-input-nilai').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btnSubmit = document.getElementById('btn-submit-nilai');
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

        const formData = new FormData(this);
        formData.append('_token', '{{ csrf_token() }}');

        fetch("{{ route('guru.input-nilai') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(function(response) {
            return response.text().then(function(text) {
                try {
                    return { ok: response.ok, data: JSON.parse(text) };
                } catch(e) {
                    return { ok: false, data: { success: false, message: 'Server error: ' + text.substring(0, 200) } };
                }
            });
        })
        .then(function(result) {
            if (result.data.success) {
                alert('✅ ' + result.data.message);
                closeInputNilaiModal();
            } else {
                alert('❌ ' + (result.data.message || 'Gagal menyimpan data'));
            }
        })
        .catch(function(error) {
            console.error('Fetch error:', error);
            alert('❌ Terjadi kesalahan jaringan: ' + error.message);
        })
        .finally(function() {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = 'Simpan Nilai';
        });
    });
</script>

<!-- Modal Input Nilai -->
<div id="input-nilai-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] hidden" onclick="closeInputNilaiModal()"></div>
<div id="input-nilai-modal" class="fixed inset-0 z-[70] hidden items-center justify-center p-4 pointer-events-none">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md pointer-events-auto overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-indigo-50/50">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Input Nilai & Komentar</h3>
                <p id="in-student-name" class="text-sm text-indigo-600 font-semibold mt-0.5"></p>
            </div>
            <button type="button" onclick="closeInputNilaiModal()" class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-all">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <form id="form-input-nilai" class="p-6 space-y-4">
            <input type="hidden" name="student_id" id="in-student-id">
            <input type="hidden" name="subject_id" id="in-subject-id">
            
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jenis Nilai</label>
                <select name="jenis_nilai" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                    <option value="nilai_tugas">Nilai Tugas / Kuis</option>
                    <option value="nilai_uh">Nilai Ulangan Harian</option>
                    <option value="nilai_uts">Nilai UTS</option>
                    <option value="nilai_uas">Nilai UAS</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Skor (0-100)</label>
                <input type="number" name="skor" min="0" max="100" required placeholder="Contoh: 85" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Komentar Khusus Siswa</label>
                <textarea name="komentar" rows="3" placeholder="Berikan komentar, masukan, atau apresiasi untuk siswa ini..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all resize-none"></textarea>
            </div>

            <div class="pt-2">
                <button type="submit" id="btn-submit-nilai" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl transition-all shadow-md shadow-indigo-200 flex items-center justify-center gap-2">
                    Simpan Nilai
                </button>
            </div>
        </form>
    </div>
</div>
@endsection