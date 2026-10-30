<?php

namespace App\Http\Controllers;

use App\Models\EkskulAnggota;
use App\Models\Grade;
use App\Models\Setting;
use App\Models\SubjectTeacher;
use App\Models\TeacherNote;
use App\Services\NilaiService;
use Illuminate\Support\Facades\Auth;

/**
 * Halaman nilai untuk siswa dan orang tua: nilai per bab, UTS, UAS, nilai akhir, dan ekstrakurikuler.
 */
class NilaiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless(in_array($user->role, ['siswa', 'orang_tua']), 403);

        $student = $user->siswaDipantau();
        if (!$student) {
            return view('nilai.index', ['student' => null]);
        }

        // Status baca: orang tua sudah membuka laporan nilai
        if ($user->role === 'orang_tua') {
            $user->forceFill(['report_read_at' => now()])->save();
        }

        $semester = Setting::semesterAktif();
        $grades = Grade::where('student_id', $student->id)->where('semester', $semester)->get()->keyBy('subject_id');

        $mapel = SubjectTeacher::where('kelas_id', $student->kelas_id)
            ->with(['subject', 'teacher'])
            ->get()
            ->sortBy('subject.nama_mapel')
            ->map(fn ($st) => [
                'st' => $st,
                'grade' => $grades->get($st->subject_id),
                'bab' => NilaiService::rekapBab($student->id, $st->subject_id, $semester),
            ])
            ->values();

        $nilaiAkhir = $mapel->map(fn ($m) => $m['grade']?->average)->filter();

        $ekskul = EkskulAnggota::where('student_id', $student->id)->where('semester', $semester)
            ->with('ekstrakurikuler')->get();

        $catatan = TeacherNote::where('student_id', $student->id)->with(['teacher', 'subject'])->latest()->take(10)->get();

        $riwayat = Grade::where('student_id', $student->id)->get()->groupBy('semester')
            ->map(fn ($g) => round($g->avg(fn ($x) => $x->average), 1));

        return view('nilai.index', [
            'student' => $student,
            'semester' => $semester,
            'mapel' => $mapel,
            'rataNilai' => $nilaiAkhir->count() ? round($nilaiAkhir->avg(), 1) : null,
            'ekskul' => $ekskul,
            'catatan' => $catatan,
            'riwayat' => $riwayat,
        ]);
    }
}