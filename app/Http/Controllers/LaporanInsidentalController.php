<?php

namespace App\Http\Controllers;

use App\Models\IncidentReport;
use App\Models\SubjectTeacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaporanInsidentalController extends Controller
{
    /**
     * Siswa: form pelaporan insidental (hanya untuk kejadian hari ini).
     */
    public function create()
    {
        $user = Auth::user();
        abort_unless($user->role === 'siswa', 403);

        $mapel = SubjectTeacher::where('kelas_id', $user->kelas_id)->with(['subject', 'teacher'])->get();
        $riwayat = IncidentReport::where('student_id', $user->id)
            ->with('subjectTeacher.subject')
            ->latest()
            ->take(10)
            ->get();

        return view('siswa.lapor', compact('mapel', 'riwayat'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->role === 'siswa', 403);

        $data = $request->validate([
            'subject_teacher_id' => 'required|exists:subject_teacher,id',
            'jenis' => 'required|in:'.implode(',', array_keys(IncidentReport::JENIS)),
            'keterangan' => 'nullable|string|max:500',
        ]);

        $st = SubjectTeacher::findOrFail($data['subject_teacher_id']);
        abort_unless($st->kelas_id === $user->kelas_id, 403);

        IncidentReport::create($data + [
            'student_id' => $user->id,
            'tanggal' => today(),
        ]);

        return redirect()->route('siswa.lapor')
            ->with('success', 'Laporan terkirim. Identitasmu tidak ditampilkan kepada guru.');
    }

    /**
     * Admin: daftar laporan insidental (identitas pelapor tidak ditampilkan).
     */
    public function index(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $laporan = IncidentReport::with(['subjectTeacher.subject', 'subjectTeacher.kelas', 'subjectTeacher.teacher'])
            ->latest('tanggal')
            ->latest()
            ->get();

        return view('admin.laporan', compact('laporan'));
    }
}
