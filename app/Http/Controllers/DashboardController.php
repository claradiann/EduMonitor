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
     * Display the dashboard matching the user's role.
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
     * Admin Dashboard logic.
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

        // Overall Teacher evaluation rankings
        // Get all teachers and their average scores across all responses
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

        // Evaluation responses summary per indicator
        $indicators = EvaluationIndicator::all()->map(function ($indicator) {
            $avgScore = EvaluationScore::where('indicator_id', $indicator->id)->avg('score');
            return [
                'name' => $indicator->indicator_name,
                'average' => $avgScore ? round($avgScore, 2) : 0,
            ];
        });

        // Get user lists for management
        $usersList = User::with(['kelas', 'student'])->get();
        $kelas = Kelas::all();

        return view('dashboard.admin', compact('stats', 'teachersEvaluations', 'indicators', 'usersList', 'kelas'));
    }

    /**
     * Guru Dashboard logic.
     */
    private function guruDashboard($user)
    {
        // Get subjects taught by this teacher
        $subjectTeachers = SubjectTeacher::where('teacher_id', $user->id)
            ->with(['subject', 'kelas'])
            ->get();

        $subjectTeacherIds = $subjectTeachers->pluck('id');

        // Evaluation responses for this teacher
        $responses = EvaluationResponse::whereIn('subject_teacher_id', $subjectTeacherIds)
            ->with(['student.kelas'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Overall average rating for this teacher
        $allScores = EvaluationScore::whereIn('evaluation_response_id', $responses->pluck('id'))->pluck('score');
        $averageRating = $allScores->count() > 0 ? round($allScores->average(), 2) : 0;

        // Breakdown per indicator
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

        // Simple class stats
        $totalStudentsTaught = User::where('role', 'siswa')
            ->whereIn('kelas_id', $subjectTeachers->pluck('kelas_id'))
            ->count();

        return view('dashboard.guru', compact('subjectTeachers', 'responses', 'averageRating', 'indicatorsBreakdown', 'totalStudentsTaught'));
    }

    /**
     * Siswa Dashboard logic.
     */
    private function siswaDashboard($user)
    {
        // Class
        $kelas = $user->kelas;

        // Current semester grades (Genap 2025/2026)
        $grades = Grade::where('student_id', $user->id)
            ->where('semester', 'Genap 2025/2026')
            ->with('subject')
            ->get();

        // Calculate average grade
        $avgGrade = 0;
        if ($grades->count() > 0) {
            $totalAvg = 0;
            foreach ($grades as $g) {
                $totalAvg += $g->average;
            }
            $avgGrade = round($totalAvg / $grades->count(), 1);
        }

        // Compare with past semesters (for chart)
        $semestersData = Grade::where('student_id', $user->id)
            ->select('semester', DB::raw('SUM(nilai_tugas + nilai_uh + nilai_uts + nilai_uas) / (4 * COUNT(*)) as average'))
            ->groupBy('semester')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->semester => round($item->average, 1)];
            });

        // Ensure current active semesters are included
        $allSemesters = [
            'Gasal 2025/2026' => $semestersData->get('Gasal 2025/2026', 0.0),
            'Genap 2025/2026' => $semestersData->get('Genap 2025/2026', 0.0),
            'Gasal 2026/2027' => $semestersData->get('Gasal 2026/2027', 0.0),
            'Genap 2026/2027' => $semestersData->get('Genap 2026/2027', 0.0),
        ];

        // Teacher notes
        $notes = TeacherNote::where('student_id', $user->id)
            ->with(['teacher', 'subject'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Subject teacher mappings in this student's class (to fill evaluations)
        $subjectTeachers = SubjectTeacher::where('kelas_id', $user->kelas_id)
            ->with(['subject', 'teacher'])
            ->get()
            ->map(function ($st) use ($user) {
                // Check if already evaluated this semester
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

        return view('dashboard.siswa', compact('kelas', 'grades', 'avgGrade', 'allSemesters', 'notes', 'subjectTeachers'));
    }

    /**
     * Orang Tua Dashboard logic.
     */
    private function orangTuaDashboard($user)
    {
        // Get child
        $student = $user->student;

        if (!$student) {
            return view('dashboard.orangtua', ['student' => null]);
        }

        // Fetch details of the student
        $kelas = $student->kelas;

        $grades = Grade::where('student_id', $student->id)
            ->where('semester', 'Genap 2025/2026')
            ->with('subject')
            ->get();

        $avgGrade = 0;
        if ($grades->count() > 0) {
            $totalAvg = 0;
            foreach ($grades as $g) {
                $totalAvg += $g->average;
            }
            $avgGrade = round($totalAvg / $grades->count(), 1);
        }

        $semestersData = Grade::where('student_id', $student->id)
            ->select('semester', DB::raw('SUM(nilai_tugas + nilai_uh + nilai_uts + nilai_uas) / (4 * COUNT(*)) as average'))
            ->groupBy('semester')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->semester => round($item->average, 1)];
            });

        $allSemesters = [
            'Gasal 2025/2026' => $semestersData->get('Gasal 2025/2026', 0.0),
            'Genap 2025/2026' => $semestersData->get('Genap 2025/2026', 0.0),
            'Gasal 2026/2027' => $semestersData->get('Gasal 2026/2027', 0.0),
            'Genap 2026/2027' => $semestersData->get('Genap 2026/2027', 0.0),
        ];

        $notes = TeacherNote::where('student_id', $student->id)
            ->with(['teacher', 'subject'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.orangtua', compact('student', 'kelas', 'grades', 'avgGrade', 'allSemesters', 'notes'));
    }
}
