<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\EvaluationIndicator;
use App\Models\EvaluationResponse;
use App\Models\EvaluationScore;
use App\Models\Grade;
use App\Models\IncidentReport;
use App\Models\Kelas;
use App\Models\LaporanPerkembangan;
use App\Models\LaporanSiswa;
use App\Models\Setting;
use App\Models\SubjectTeacher;
use App\Models\UploadUnlock;
use App\Models\User;
use App\Services\PengingatService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Beranda sesuai peran pengguna.
     */
    public function index()
    {
        $user = Auth::user();

        return match ($user->role) {
            'admin' => $this->adminDashboard(),
            'guru' => $this->guruDashboard($user),
            'siswa', 'orang_tua' => $this->pantauDashboard($user),
            default => tap(redirect()->route('login'), fn () => Auth::logout()),
        };
    }

    // =================================================================
    // ADMIN
    // =================================================================

    private function adminDashboard()
    {
        $semester = Setting::semesterAktif();
        $jumlahPerKelas = $this->jumlahSiswaPerKelas();

        $penilaian = Assessment::where('semester', $semester)
            ->with(['subjectTeacher.subject', 'subjectTeacher.kelas', 'subjectTeacher.teacher', 'scores', 'makeups'])
            ->get();

        $terlambat = $penilaian->filter(fn ($a) => $a->statusKelas($jumlahPerKelas[$a->subjectTeacher->kelas_id] ?? 0) === 'melewati_batas');

        $stats = [
            'siswa' => User::where('role', 'siswa')->count(),
            'guru' => User::where('role', 'guru')->count(),
            'kelas' => Kelas::count(),
            'penilaian' => $penilaian->count(),
            'terlambat' => $terlambat->count(),
            'pengajuan' => UploadUnlock::where('status', 'menunggu')->count(),
            'laporan_minggu_ini' => IncidentReport::where('tanggal', '>=', today()->subDays(7))->count(),
            'evaluasi' => EvaluationResponse::where('semester', $semester)->count(),
        ];

        $ujianMendatang = $penilaian->whereIn('jenis', ['uts', 'uas'])
            ->filter(fn ($a) => $a->tanggal->gte(today()))
            ->groupBy(fn ($a) => $a->jenis . '|' . $a->tanggal->toDateString())
            ->map(fn ($g) => ['contoh' => $g->first(), 'jumlah' => $g->count()])
            ->sortBy(fn ($g) => $g['contoh']->tanggal)
            ->take(4)
            ->values();

        $pengajuan = UploadUnlock::where('status', 'menunggu')
            ->with(['assessment.subjectTeacher.subject', 'assessment.subjectTeacher.kelas', 'pengaju'])
            ->oldest()->take(5)->get();

        $terlambat = $terlambat->sortBy('tanggal')->take(6)->values();

        // Grafik: status seluruh penilaian dan ketepatan unggah nilai per guru
        $statusSekolah = $penilaian->map(fn ($a) => $a->statusKelas($jumlahPerKelas[$a->subjectTeacher->kelas_id] ?? 0))->countBy();
        $ketepatan = $penilaian->groupBy(fn ($a) => $a->subjectTeacher->teacher->name)
            ->map(function ($daftar, $guru) use ($jumlahPerKelas) {
                $hasil = ['guru' => $guru, 'tepat' => 0, 'terlambat' => 0, 'berjalan' => 0];
                foreach ($daftar as $a) {
                    $n = $jumlahPerKelas[$a->subjectTeacher->kelas_id] ?? 0;
                    $status = $a->statusKelas($n);
                    if ($status === 'melewati_batas' || $a->keterlambatanMenit($n) > 0) {
                        $hasil['terlambat']++;
                    } elseif ($status === 'nilai_tersedia') {
                        $hasil['tepat']++;
                    } elseif ($status === 'menunggu_nilai') {
                        $hasil['berjalan']++;
                    }
                }
                return $hasil;
            })
            ->filter(fn ($g) => $g['tepat'] + $g['terlambat'] + $g['berjalan'] > 0)
            ->sortByDesc('terlambat')->values();

        return view('dashboard.admin', compact('stats', 'ujianMendatang', 'pengajuan', 'terlambat', 'semester', 'statusSekolah', 'ketepatan'));
    }

    /**
     * Admin: rekap kinerja guru. Skor evaluasi hanya dari penilaian siswa.
     * Keterlambatan unggah nilai dicatat terpisah sebagai bahan penilaian dari sisi manajemen.
     */
    public function recap()
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
        $semester = Setting::semesterAktif();
        $jumlahPerKelas = $this->jumlahSiswaPerKelas();

        $guru = User::where('role', 'guru')
            ->with(['subjectTeachers.subject', 'subjectTeachers.kelas', 'subjectTeachers.evaluationResponses.scores'])
            ->orderBy('name')
            ->get()
            ->map(function ($teacher) use ($semester, $jumlahPerKelas) {
                $stIds = $teacher->subjectTeachers->pluck('id');

                $skor = $teacher->subjectTeachers->flatMap(fn ($st) => $st->evaluationResponses->flatMap->scores)->pluck('score');

                $penilaian = Assessment::whereIn('subject_teacher_id', $stIds)->where('semester', $semester)
                    ->with(['scores', 'makeups', 'subjectTeacher'])->get();

                $keterlambatan = $penilaian
                    ->map(fn ($a) => $a->keterlambatanMenit($jumlahPerKelas[$a->subjectTeacher->kelas_id] ?? 0))
                    ->filter();

                $kelas = $teacher->subjectTeachers->map(function ($st) use ($semester) {
                    $siswa = User::where('role', 'siswa')->where('kelas_id', $st->kelas_id)->pluck('id');
                    $nilai = Grade::whereIn('student_id', $siswa)->where('subject_id', $st->subject_id)
                        ->where('semester', $semester)->get()->map(fn ($g) => $g->average)->filter();

                    return ['label' => $st->label(), 'rata' => $nilai->count() ? round($nilai->avg(), 1) : null];
                });

                return [
                    'name' => $teacher->name,
                    'nip' => $teacher->nip ?? '-',
                    'skor' => $skor->count() ? round($skor->avg(), 2) : null,
                    'responden' => $teacher->subjectTeachers->sum(fn ($st) => $st->evaluationResponses->count()),
                    'penilaian' => $penilaian->count(),
                    'terlambat' => $keterlambatan->count(),
                    'lama_terlambat' => $keterlambatan->sum(),
                    'buka_akses' => UploadUnlock::whereIn('assessment_id', $penilaian->pluck('id'))->where('status', 'dibuka')->count(),
                    'laporan' => IncidentReport::whereIn('subject_teacher_id', $stIds)->count(),
                    'kelas' => $kelas,
                ];
            });

        $indicators = EvaluationIndicator::all()->map(fn ($i) => [
            'name' => $i->indicator_name,
            'average' => round((float) EvaluationScore::where('indicator_id', $i->id)->avg('score'), 2),
        ]);

        return view('dashboard.admin-recap', compact('guru', 'indicators', 'semester'));
    }

    // =================================================================
    // GURU
    // =================================================================

    private function guruDashboard(User $user)
    {
        PengingatService::untukGuru($user);
        $semester = Setting::semesterAktif();

        $subjectTeachers = SubjectTeacher::where('teacher_id', $user->id)
            ->with(['subject', 'kelas', 'assessments' => fn ($q) => $q->where('semester', $semester)->with(['scores', 'makeups', 'unlocks'])])
            ->get();

        $kelas = $subjectTeachers->map(function ($st) {
        $jumlahSiswa = User::where('role', 'siswa')->where('kelas_id', $st->kelas_id)->count();
        $status = $st->assessments->map(fn ($a) => $a->statusKelas($jumlahSiswa))->countBy();

        // Status baca laporan terakhir yang dikirim ke orang tua
        $laporan = LaporanPerkembangan::where('subject_teacher_id', $st->id)
            ->with(['siswa.student.parents', 'siswa.dibaca'])->latest()->first();
        [$ortuBaca, $ortuTotal] = $laporan ? $laporan->keterbacaan() : [0, 0];

        return [
            'st' => $st,
            'siswa' => $jumlahSiswa,
            'status' => $status,
            'laporan' => $laporan,
            'ortu_total' => $ortuTotal,
            'ortu_baca' => $ortuBaca,
            // Grafik: rata-rata nilai kelas untuk tiap penilaian yang sudah dinilai
            'grafik' => $st->assessments->filter(fn ($a) => $a->scores->isNotEmpty())->sortBy('tanggal')
                ->map(fn ($a) => ['label' => $a->judul, 'rata' => round($a->scores->avg('nilai_konversi'), 1)])->values(),
            ];
        });

        $semua = $subjectTeachers->flatMap(fn ($st) => $st->assessments->each(fn ($a) => $a->setRelation('subjectTeacher', $st)));
        $jumlahPerKelas = $this->jumlahSiswaPerKelas();

        $perluInput = $semua->filter(fn ($a) => in_array($a->statusKelas($jumlahPerKelas[$a->subjectTeacher->kelas_id] ?? 0), ['menunggu_nilai', 'melewati_batas']))
            ->sortBy('tanggal')->values();

        $mendatang = $semua->filter(fn ($a) => $a->tanggal->between(today(), today()->addDays(14)))
            ->sortBy('tanggal')->take(6)->values();

        $skor = EvaluationScore::whereIn('evaluation_response_id',
            EvaluationResponse::whereIn('subject_teacher_id', $subjectTeachers->pluck('id'))->pluck('id'))->avg('score');

        return view('dashboard.guru', [
            'kelas' => $kelas,
            'perluInput' => $perluInput,
            'mendatang' => $mendatang,
            'skorEvaluasi' => $skor ? round($skor, 2) : null,
            'jumlahPerKelas' => $jumlahPerKelas,
            'semester' => $semester,
        ]);
    }

    public function guruFeedback()
    {
        $user = Auth::user();
        abort_unless($user?->role === 'guru', 403);

        $subjectTeachers = SubjectTeacher::where('teacher_id', $user->id)->with(['subject', 'kelas'])->get();

        $responses = EvaluationResponse::whereIn('subject_teacher_id', $subjectTeachers->pluck('id'))
            ->with(['student.kelas'])
            ->orderBy('created_at', 'desc')
            ->get();

        $indicatorsBreakdown = EvaluationIndicator::all()->map(fn ($indicator) => [
            'name' => $indicator->indicator_name,
            'average' => round((float) EvaluationScore::where('indicator_id', $indicator->id)
                ->whereIn('evaluation_response_id', $responses->pluck('id'))->avg('score'), 2),
        ]);

        return view('dashboard.guru-feedback', compact('responses', 'indicatorsBreakdown'));
    }

    // =================================================================
    // SISWA & ORANG TUA
    // =================================================================

    private function pantauDashboard(User $user)
    {
        PengingatService::untukSiswa($user);
        $student = $user->siswaDipantau();
        if (!$student) {
            return view('dashboard.pantau', ['student' => null]);
                        'laporanGuru' => LaporanSiswa::where('student_id', $student->id)
                ->with(['laporan.pengirim', 'dibaca'])->latest()->take(3)->get(),
        }

        $semester = Setting::semesterAktif();

        $grades = Grade::where('student_id', $student->id)->where('semester', $semester)->with('subject')->get();
        $nilaiAkhir = $grades->map(fn ($g) => $g->average)->filter();

        $penilaian = Assessment::where('semester', $semester)
            ->whereHas('subjectTeacher', fn ($q) => $q->where('kelas_id', $student->kelas_id))
            ->with(['subjectTeacher.subject', 'scores' => fn ($q) => $q->where('student_id', $student->id),
                'makeups' => fn ($q) => $q->where('student_id', $student->id)])
            ->get()
            ->map(fn ($a) => ['model' => $a, 'tanggal' => $a->tanggalUntuk($student->id), 'status' => $a->statusUntukSiswa($student->id)]);

        $mendatang = $penilaian->filter(fn ($p) => $p['status'] === 'dijadwalkan')
            ->sortBy(fn ($p) => $p['tanggal']->timestamp)->take(5)->values();

        $nilaiTerbaru = AssessmentScore::where('student_id', $student->id)
            ->whereHas('assessment', fn ($q) => $q->where('semester', $semester))
            ->with('assessment.subjectTeacher.subject')
            ->latest()->take(5)->get();

        // Grafik perkembangan: nilai anak dibandingkan rata-rata kelas, per mata pelajaran
        $dinilai = $penilaian->filter(fn ($p) => $p['model']->scores->isNotEmpty());
        $rataKelas = AssessmentScore::whereIn('assessment_id', $dinilai->pluck('model.id'))
            ->selectRaw('assessment_id, avg(nilai_konversi) as rata')->groupBy('assessment_id')
            ->pluck('rata', 'assessment_id');
        $tren = $dinilai->sortBy(fn ($p) => $p['model']->tanggal->timestamp)
            ->groupBy(fn ($p) => $p['model']->subjectTeacher->subject->nama_mapel)
            ->map(fn ($g) => $g->map(fn ($p) => [
                'label' => $p['model']->judul,
                'tanggal' => $p['model']->tanggal->translatedFormat('d M'),
                'nilai' => round($p['model']->scores->first()->nilai_konversi, 1),
                'kelas' => round($rataKelas[$p['model']->id] ?? 0, 1),
            ])->values());

        $perMapel = $grades->filter(fn ($g) => $g->average)->sortByDesc(fn ($g) => $g->average)
            ->map(fn ($g) => ['mapel' => $g->subject->nama_mapel ?? '-', 'nilai' => $g->average, 'sementara' => $g->sementara])->values();

        $evaluasiBelum = $user->role === 'siswa'
            ? SubjectTeacher::where('kelas_id', $student->kelas_id)->count()
                - EvaluationResponse::where('student_id', $student->id)->where('semester', $semester)->count()
            : 0;

        return view('dashboard.pantau', [
            'student' => $student,
            'semester' => $semester,
            'rataNilai' => $nilaiAkhir->count() ? round($nilaiAkhir->avg(), 1) : null,
            'jumlahMapel' => $grades->count(),
            'ringkasan' => $penilaian->countBy('status'),
            'mendatang' => $mendatang,
            'nilaiTerbaru' => $nilaiTerbaru,
            'evaluasiBelum' => max(0, $evaluasiBelum),
            'notifikasi' => $user->notifikasi()->take(4)->get(),
            'tren' => $tren,
            'perMapel' => $perMapel,
        ]);
    }

    public function siswaEvaluasi()
    {
        $user = Auth::user();
        abort_unless($user?->role === 'siswa', 403);
        $semester = Setting::semesterAktif();

        $subjectTeachers = SubjectTeacher::where('kelas_id', $user->kelas_id)
            ->with(['subject', 'teacher'])
            ->get()
            ->map(fn ($st) => [
                'id' => $st->id,
                'subject' => $st->subject->nama_mapel,
                'teacher' => $st->teacher->name,
                'evaluated' => EvaluationResponse::where('student_id', $user->id)
                    ->where('subject_teacher_id', $st->id)->where('semester', $semester)->exists(),
            ]);

        return view('dashboard.siswa-evaluasi', compact('subjectTeachers'));
    }

    // =================================================================
    // PROFIL
    // =================================================================

    public function siswaProfile()
    {
        $user = Auth::user();
        abort_unless($user?->role === 'siswa', 403);

        return view('dashboard.siswa-profile', ['user' => $user, 'kelas' => $user->kelas]);
    }

    public function guruProfile()
    {
        $user = Auth::user();
        abort_unless($user?->role === 'guru', 403);

        $subjectTeachers = SubjectTeacher::where('teacher_id', $user->id)->with(['subject', 'kelas'])->get();

        return view('dashboard.guru-profile', compact('user', 'subjectTeachers'));
    }

    public function adminProfile()
    {
        $user = Auth::user();
        abort_unless($user?->role === 'admin', 403);

        return view('dashboard.admin-profile', compact('user'));
    }

    public function orangTuaProfile()
    {
        $user = Auth::user();
        abort_unless($user?->role === 'orang_tua', 403);

        $student = $user->anakAktif();

        return view('dashboard.ortu-profile', ['user' => $user, 'student' => $student, 'kelas' => $student?->kelas]);
    }

    private function jumlahSiswaPerKelas()
    {
        return User::where('role', 'siswa')->selectRaw('kelas_id, count(*) as n')->groupBy('kelas_id')->pluck('n', 'kelas_id');
    }
}