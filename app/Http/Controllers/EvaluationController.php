<?php

namespace App\Http\Controllers;

use App\Models\EvaluationIndicator;
use App\Models\EvaluationResponse;
use App\Models\EvaluationScore;
use App\Models\SubjectTeacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    /**
     * Show the evaluation questionnaire form to the student.
     */
    public function showForm($subject_teacher_id)
    {
        $student = Auth::user();
        if ($student->role !== 'siswa') {
            abort(403, 'Hanya siswa yang dapat mengisi evaluasi.');
        }

        $subjectTeacher = SubjectTeacher::with(['subject', 'teacher', 'kelas'])
            ->findOrFail($subject_teacher_id);

        // Security check: ensure student is in the same class
        if ($subjectTeacher->kelas_id !== $student->kelas_id) {
            abort(403, 'Anda tidak terdaftar di kelas ini.');
        }

        // Check if student has already evaluated this teacher this semester
        $alreadyEvaluated = EvaluationResponse::where('student_id', $student->id)
            ->where('subject_teacher_id', $subject_teacher_id)
            ->where('semester', 'Genap 2025/2026')
            ->exists();

        if ($alreadyEvaluated) {
            return redirect()->route('dashboard')->with('error', 'Anda sudah mengisi kuesioner evaluasi untuk mata pelajaran ini.');
        }

        $indicators = EvaluationIndicator::all();

        return view('evaluation.fill', compact('subjectTeacher', 'indicators'));
    }

    /**
     * Store the evaluation response submitted by the student.
     */
    public function storeResponse(Request $request, $subject_teacher_id)
    {
        $student = Auth::user();
        if ($student->role !== 'siswa') {
            abort(403);
        }

        $subjectTeacher = SubjectTeacher::findOrFail($subject_teacher_id);
        if ($subjectTeacher->kelas_id !== $student->kelas_id) {
            abort(403);
        }

        // Check again
        $alreadyEvaluated = EvaluationResponse::where('student_id', $student->id)
            ->where('subject_teacher_id', $subject_teacher_id)
            ->where('semester', 'Genap 2025/2026')
            ->exists();

        if ($alreadyEvaluated) {
            return redirect()->route('dashboard')->with('error', 'Evaluasi sudah pernah diisi.');
        }

        // Validate that we have a rating (1-5) for each indicator
        $indicators = EvaluationIndicator::all();
        $rules = [
            'comments' => 'nullable|string|max:500',
        ];

        foreach ($indicators as $ind) {
            $rules['rating_' . $ind->id] = 'required|integer|between:1,5';
        }

        $request->validate($rules);

        // Save Evaluation Response
        $response = EvaluationResponse::create([
            'student_id' => $student->id,
            'subject_teacher_id' => $subject_teacher_id,
            'comments' => $request->comments,
            'semester' => 'Genap 2025/2026',
        ]);

        // Save Scores
        foreach ($indicators as $ind) {
            EvaluationScore::create([
                'evaluation_response_id' => $response->id,
                'indicator_id' => $ind->id,
                'score' => $request->input('rating_' . $ind->id),
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Kuesioner evaluasi berhasil dikirim. Terima kasih atas masukan Anda!');
    }
}
