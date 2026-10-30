<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Siswa dan orang tua: jadwal penilaian beserta status otomatisnya.
 */
class PemantauanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless(in_array($user->role, ['orang_tua', 'siswa']), 403);

        $student = $user->siswaDipantau();
        if (!$student) {
            return view('pemantauan.index', ['student' => null]);
        }

        $semester = Setting::semesterAktif();
        $filter = $request->query('tampil', 'mendatang');

        $penilaian = Assessment::where('semester', $semester)
            ->whereHas('subjectTeacher', fn ($q) => $q->where('kelas_id', $student->kelas_id))
            ->with([
                'subjectTeacher.subject', 'subjectTeacher.teacher',
                'scores' => fn ($q) => $q->where('student_id', $student->id),
                'makeups' => fn ($q) => $q->where('student_id', $student->id),
                'scheduleChanges',
            ])
            ->get()
            ->map(fn ($a) => [
                'model' => $a,
                'tanggal' => $a->tanggalUntuk($student->id),
                'susulan' => $a->makeups->first(),
                'status' => $a->statusUntukSiswa($student->id),
                'nilai' => $a->scores->first()?->nilai_konversi,
                'diubah' => $a->scheduleChanges->first(),
            ])
            ->sortBy(fn ($p) => $p['tanggal']->timestamp)
            ->values();

        $ringkasan = $penilaian->countBy('status');

        $tampil = $filter === 'semua'
            ? $penilaian
            : $penilaian->filter(fn ($p) => in_array($p['status'], ['dijadwalkan', 'menunggu_nilai', 'melewati_batas']))->values();

        $grup = $tampil->groupBy(fn ($p) => $p['tanggal']->toDateString());

        return view('pemantauan.index', compact('student', 'semester', 'grup', 'ringkasan', 'filter'));
    }
}