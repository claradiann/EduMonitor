<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kelas;
use App\Models\Subject;
use App\Models\SubjectTeacher;
use App\Models\Grade;
use App\Models\TeacherNote;
use App\Models\EvaluationIndicator;
use App\Models\EvaluationResponse;
use App\Models\EvaluationScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard matching the user's role. (Beranda)
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        switch ($user->role) {
            case 'admin':
                return $this->adminDashboard($user);
            case 'guru':
                return $this->guruDashboard($user);
            case 'siswa':
                return $this->siswaDashboard($user);
            case 'orang_tua':
                return $this->orangTuaDashboard($user);
            default:
                Auth::logout();
                return redirect()->route('login');
        }
    }

    /**
     * Guru: Halaman Feedback Evaluasi (halaman terpisah).
     */
    public function guruFeedback()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'guru') {
            abort(403);
        }

        $subjectTeachers = SubjectTeacher::where('teacher_id', $user->id)
            ->with(['subject', 'kelas'])
            ->get();

        $subjectTeacherIds = $subjectTeachers->pluck('id');

        $responses = EvaluationResponse::whereIn('subject_teacher_id', $subjectTeacherIds)
            ->with(['student.kelas'])
            ->orderBy('created_at', 'desc')
            ->get();

        $indicatorsBreakdown = EvaluationIndicator::all()->map(function ($indicator) use ($responses) {
            $responseIds = $responses->pluck('id');
            $avgScore = EvaluationScore::where('indicator_id', $indicator->id)
                ->whereIn('evaluation_response_id', $responseIds)
                ->avg('score');

            return [
                'name' => $indicator->indicator_name,
                'average' => $avgScore ? round($avgScore, 2) : 0,
            ];
        });

        return view('dashboard.guru-feedback', compact('responses', 'indicatorsBreakdown'));
    }

    /**
     * Admin: Manajemen User page (halaman terpisah).
     */
    public function index_users()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403);
        }

        $usersList = User::with('kelas')->orderBy('role')->orderBy('name')->get();
        $kelas = Kelas::all();

        return view('dashboard.admin-users', compact('usersList', 'kelas'));
    }

    /**
     * Admin: Rekap Evaluasi page (halaman terpisah).
     */
    public function recap()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403);
        }

        $teachersEvaluations = User::where('role', 'guru')
            ->with(['subjectTeachers.evaluationResponses.scores'])
            ->get()
            ->map(function ($teacher) {
                $totalScore = 0;
                $scoreCount = 0;
                $responsesCount = 0;

                foreach ($teacher->subjectTeachers as $st) {
                    $responsesCount += $st->evaluationResponses->count();
                    foreach ($st->evaluationResponses as $response) {
                        foreach ($response->scores as $score) {
                            $totalScore += $score->score;
                            $scoreCount++;
                        }
                    }
                }

                $average = $scoreCount > 0 ? round($totalScore / $scoreCount, 2) : 0;

                return [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'nip' => $teacher->nip ?? '-',
                    'average_rating' => $average,
                    'responses_count' => $responsesCount,
                ];
            })
            ->sortByDesc('average_rating')
            ->values();

        $indicators = EvaluationIndicator::all()->map(function ($indicator) {
            $avgScore = EvaluationScore::where('indicator_id', $indicator->id)->avg('score');
            return [
                'name' => $indicator->indicator_name,
                'average' => $avgScore ? round($avgScore, 2) : 0,
            ];
        });

        return view('dashboard.admin-recap', compact('teachersEvaluations', 'indicators'));
    }

    /**
     * Admin Dashboard logic. (Beranda - stats saja)
     */
    private function adminDashboard($user)
    {
        $stats = [
            'total_students' => User::where('role', 'siswa')->count(),
            'total_teachers' => User::where('role', 'guru')->count(),
            'total_parents' => User::where('role', 'orang_tua')->count(),
            'total_classes' => Kelas::count(),
            'total_subjects' => Subject::count(),
            'total_evaluations' => EvaluationResponse::count(),
        ];

        return view('dashboard.admin', compact('stats'));
    }

    /**
     * Guru Dashboard logic.
     */
    private function guruDashboard($user)
    {
        $subjectTeachers = SubjectTeacher::where('teacher_id', $user->id)
            ->with(['subject', 'kelas'])
            ->get();

        $subjectTeacherIds = $subjectTeachers->pluck('id');

        $responses = EvaluationResponse::whereIn('subject_teacher_id', $subjectTeacherIds)
            ->with(['student.kelas'])
            ->orderBy('created_at', 'desc')
            ->get();

        $allScores = EvaluationScore::whereIn('evaluation_response_id', $responses->pluck('id'))->pluck('score');
        $averageRating = $allScores->count() > 0 ? round($allScores->average(), 2) : 0;

        $indicatorsBreakdown = EvaluationIndicator::all()->map(function ($indicator) use ($responses) {
            $responseIds = $responses->pluck('id');
            $avgScore = EvaluationScore::where('indicator_id', $indicator->id)
                ->whereIn('evaluation_response_id', $responseIds)
                ->avg('score');

            return [
                'name' => $indicator->indicator_name,
                'average' => $avgScore ? round($avgScore, 2) : 0,
            ];
        });

        $totalStudentsTaught = User::where('role', 'siswa')
            ->whereIn('kelas_id', $subjectTeachers->pluck('kelas_id'))
            ->count();

        // Detail per kelas yang diampu: daftar siswa, rata-rata skor, progres pengisian kuesioner
        $classDetails = $subjectTeachers->map(function ($st) use ($responses) {
            $students = User::where('role', 'siswa')
                ->where('kelas_id', $st->kelas_id)
                ->orderBy('name')
                ->get();

            $stResponses = $responses->where('subject_teacher_id', $st->id);
            $stResponseIds = $stResponses->pluck('id');

            $stScores = EvaluationScore::whereIn('evaluation_response_id', $stResponseIds)->pluck('score');
            $classAverage = $stScores->count() > 0 ? round($stScores->average(), 2) : 0;

            $studentList = $students->map(function ($s) use ($stResponses) {
                return [
                    'name' => $s->name,
                    'nisn' => $s->nisn ?? '-',
                    'sudah_evaluasi' => $stResponses->where('student_id', $s->id)->isNotEmpty(),
                ];
            });

            return [
                'subject_teacher_id' => $st->id,
                'kelas' => $st->kelas->nama_kelas,
                'mapel' => $st->subject->nama_mapel,
                'kode_mapel' => $st->subject->kode_mapel,
                'total_siswa' => $students->count(),
                'sudah_mengisi' => $stResponses->count(),
                'rata_rata' => $classAverage,
                'siswa' => $studentList,
            ];
        });

        return view('dashboard.guru', compact('subjectTeachers', 'responses', 'averageRating', 'indicatorsBreakdown', 'totalStudentsTaught', 'classDetails'));
    }

    /**
     * Siswa Dashboard logic. (Beranda - ringkasan saja)
     */
    private function siswaDashboard($user)
    {
        $kelas = $user->kelas;

        $grades = Grade::where('student_id', $user->id)
            ->where('semester', 'Genap 2025/2026')
            ->with('subject')
            ->get();

        $avgGrade = $this->calculateAvgGrade($grades);

        $notes = TeacherNote::where('student_id', $user->id)
            ->with(['teacher', 'subject'])
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        $subjectTeachers = SubjectTeacher::where('kelas_id', $user->kelas_id)->get();

        $evaluatedCount = EvaluationResponse::where('student_id', $user->id)
            ->where('semester', 'Genap 2025/2026')
            ->count();

        return view('dashboard.siswa', compact('kelas', 'grades', 'avgGrade', 'notes', 'subjectTeachers', 'evaluatedCount'));
    }

    /**
     * Siswa: Halaman Nilai (halaman terpisah).
     */
    public function siswaNilai()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'siswa') {
            abort(403);
        }

        $kelas = $user->kelas;

        $grades = Grade::where('student_id', $user->id)
            ->where('semester', 'Genap 2025/2026')
            ->with('subject')
            ->get();

        $avgGrade = $this->calculateAvgGrade($grades);
        $allSemesters = $this->getAllSemestersAverage($user->id);

        $notes = TeacherNote::where('student_id', $user->id)
            ->with(['teacher', 'subject'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.siswa-nilai', compact('kelas', 'grades', 'avgGrade', 'allSemesters', 'notes'));
    }

    /**
     * Siswa: Halaman Evaluasi Pembelajaran (halaman terpisah).
     */
    public function siswaEvaluasi()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'siswa') {
            abort(403);
        }

        $subjectTeachers = SubjectTeacher::where('kelas_id', $user->kelas_id)
            ->with(['subject', 'teacher'])
            ->get()
            ->map(function ($st) use ($user) {
                $evaluated = EvaluationResponse::where('student_id', $user->id)
                    ->where('subject_teacher_id', $st->id)
                    ->where('semester', 'Genap 2025/2026')
                    ->exists();

                return [
                    'id' => $st->id,
                    'subject' => $st->subject->nama_mapel,
                    'teacher' => $st->teacher->name,
                    'evaluated' => $evaluated,
                ];
            });

        return view('dashboard.siswa-evaluasi', compact('subjectTeachers'));
    }

    /**
     * Siswa: Halaman Profile (halaman terpisah).
     */
    public function siswaProfile()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'siswa') {
            abort(403);
        }

        $kelas = $user->kelas;

        return view('dashboard.siswa-profile', compact('user', 'kelas'));
    }

    /**
     * Guru: Halaman Profile (halaman terpisah).
     */
    public function guruProfile()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'guru') {
            abort(403);
        }

        $subjectTeachers = SubjectTeacher::where('teacher_id', $user->id)
            ->with(['subject', 'kelas'])
            ->get();

        return view('dashboard.guru-profile', compact('user', 'subjectTeachers'));
    }

    /**
     * Admin: Halaman Profile (halaman terpisah).
     */
    public function adminProfile()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403);
        }

        return view('dashboard.admin-profile', compact('user'));
    }

   
    /**
     * Orang Tua: Halaman Nilai Siswa (halaman terpisah).
     */
    public function orangTuaNilai()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'orang_tua') {
            abort(403);
        }

        $student = $user->student;

        if (!$student) {
            return view('dashboard.ortu-nilai', ['student' => null]);
        }

        $kelas = $student->kelas;

        $grades = Grade::where('student_id', $student->id)
            ->where('semester', 'Genap 2025/2026')
            ->with('subject')
            ->get();

        $avgGrade = $this->calculateAvgGrade($grades);
        $allSemesters = $this->getAllSemestersAverage($student->id);

        $notes = TeacherNote::where('student_id', $student->id)
            ->with(['teacher', 'subject'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.ortu-nilai', compact('student', 'kelas', 'grades', 'avgGrade', 'allSemesters', 'notes'));
    }

    /**
     * Helper: hitung rata-rata nilai dari koleksi Grade.
     */
    private function calculateAvgGrade($grades)
    {
        if ($grades->count() === 0) {
            return 0;
        }

        $totalAvg = 0;
        foreach ($grades as $g) {
            $totalAvg += $g->average;
        }

        return round($totalAvg / $grades->count(), 1);
    }

    /**
     * Helper: rata-rata nilai per semester untuk seorang siswa.
     */
    private function getAllSemestersAverage($studentId)
    {
        $semestersData = Grade::where('student_id', $studentId)
            ->select('semester', DB::raw('SUM(nilai_tugas + nilai_uh + nilai_uts + nilai_uas) / (4 * COUNT(*)) as average'))
            ->groupBy('semester')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->semester => round($item->average, 1)];
            });

        return [
            'Gasal 2025/2026' => $semestersData->get('Gasal 2025/2026', 0.0),
            'Genap 2025/2026' => $semestersData->get('Genap 2025/2026', 0.0),
            'Gasal 2026/2027' => $semestersData->get('Gasal 2026/2027', 0.0),
            'Genap 2026/2027' => $semestersData->get('Genap 2026/2027', 0.0),
        ];
    }

    /**
     * Orang Tua Dashboard logic.
     */
    private function orangTuaDashboard($user)
    {
        $student = $user->student;

        if (!$student) {
            return view('dashboard.orangtua', ['student' => null]);
        }

        $kelas = $student->kelas;

        $grades = Grade::where('student_id', $student->id)
            ->where('semester', 'Genap 2025/2026')
            ->with('subject')
            ->get();

        $avgGrade = $this->calculateAvgGrade($grades);

        $notes = TeacherNote::where('student_id', $student->id)
            ->with(['teacher', 'subject'])
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return view('dashboard.orangtua', compact('student', 'kelas', 'grades', 'avgGrade', 'notes'));
    }
}