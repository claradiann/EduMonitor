<?php

namespace App\Http\Controllers;

use App\Models\LaporanDibaca;
use App\Models\LaporanPerkembangan;
use App\Models\LaporanSiswa;
use App\Models\Notifikasi;
use App\Models\Setting;
use App\Models\SubjectTeacher;
use App\Models\User;
use App\Services\LaporanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Laporan perkembangan belajar dari guru ke orang tua (satu arah), dengan status baca per orang tua.
 */
class LaporanController extends Controller
{
    // =================================================================
    // GURU
    // =================================================================

    /** Guru: daftar kelas-mapel dan laporan yang sudah dikirim. */
    public function guruIndex()
    {
        $user = $this->guru();

        $kelasMapel = SubjectTeacher::where('teacher_id', $user->id)->with(['subject', 'kelas'])->get();

        $laporan = LaporanPerkembangan::whereIn('subject_teacher_id', $kelasMapel->pluck('id'))
            ->with(['subjectTeacher.subject', 'subjectTeacher.kelas', 'siswa.student.parents', 'siswa.dibaca'])
            ->latest()->get();

        return view('guru.laporan-index', compact('kelasMapel', 'laporan'));
    }

    /** Guru: susun laporan untuk satu kelas-mapel, per bab atau ringkasan semester. */
    public function create(Request $request, SubjectTeacher $subjectTeacher)
    {
        $this->pastikanPemilik($subjectTeacher);
        $subjectTeacher->load(['subject', 'kelas']);
        $semester = Setting::semesterAktif();

        $babTersedia = LaporanService::babTersedia($subjectTeacher, $semester);
        $cakupan = $request->query('cakupan', $babTersedia->last() ?? 'semester');
        $bab = $cakupan === 'semester' ? null : (int) $cakupan;
        if ($bab !== null && !$babTersedia->contains($bab)) {
            $bab = null;
            $cakupan = 'semester';
        }

        $isi = LaporanService::susunIsi($subjectTeacher, $bab, $semester);
        $siswa = $subjectTeacher->students()->load('parents');

        return view('guru.laporan-buat', [
            'st' => $subjectTeacher,
            'babTersedia' => $babTersedia,
            'cakupan' => $cakupan,
            'judul' => LaporanService::judulBawaan($subjectTeacher, $bab),
            'isi' => $isi,
            'siswa' => $siswa,
        ]);
    }

    /** Guru: kirim laporan. Isi nilai disalin saat itu, lalu orang tua mendapat notifikasi. */
    public function store(Request $request, SubjectTeacher $subjectTeacher)
    {
        $this->pastikanPemilik($subjectTeacher);
        $subjectTeacher->load(['subject', 'kelas']);
        $semester = Setting::semesterAktif();

        $data = $request->validate([
            'cakupan' => ['required', 'string'],
            'judul' => ['required', 'string', 'max:150'],
            'pesan' => ['nullable', 'string', 'max:1000'],
            'catatan' => ['array'],
            'catatan.*' => ['nullable', 'string', 'max:500'],
        ]);

        $bab = $data['cakupan'] === 'semester' ? null : (int) $data['cakupan'];
        if ($bab !== null && !LaporanService::babTersedia($subjectTeacher, $semester)->contains($bab)) {
            return back()->with('error', 'Bab yang dipilih belum memiliki nilai.');
        }

        $isi = LaporanService::susunIsi($subjectTeacher, $bab, $semester);
        $siswa = $subjectTeacher->students()->load('parents');

        $laporan = DB::transaction(function () use ($subjectTeacher, $semester, $bab, $data, $isi, $siswa) {
            $laporan = LaporanPerkembangan::create([
                'subject_teacher_id' => $subjectTeacher->id,
                'semester' => $semester,
                'bab' => $bab,
                'judul' => $data['judul'],
                'pesan' => $data['pesan'] ?? null,
                'dikirim_oleh' => Auth::id(),
            ]);

            foreach ($siswa as $s) {
                $ls = LaporanSiswa::create([
                    'laporan_perkembangan_id' => $laporan->id,
                    'student_id' => $s->id,
                    'isi' => $isi[$s->id],
                    'catatan' => trim($data['catatan'][$s->id] ?? '') ?: null,
                ]);

                if ($s->parents->isNotEmpty()) {
                    Notifikasi::kirim(
                        $s->parents,
                        'laporan',
                        'Laporan perkembangan belajar',
                        Auth::user()->name . ' mengirim laporan ' . $data['judul'] . ' untuk ' . strtok($s->name, ' ') . '.',
                        route('laporan.show', $ls)
                    );
                }
            }

            return $laporan;
        });

        return redirect()->route('guru.laporan.show', $laporan)->with('success', 'Laporan terkirim ke orang tua.');
    }

    /** Guru (pemilik) dan admin: isi laporan dan siapa saja yang sudah membaca. */
    public function show(LaporanPerkembangan $laporan)
    {
        $user = Auth::user();
        $laporan->load(['subjectTeacher.subject', 'subjectTeacher.kelas', 'subjectTeacher.teacher', 'pengirim',
            'siswa' => fn ($q) => $q->with(['student.parents', 'dibaca'])]);
        abort_unless($user->role === 'admin' || ($user->role === 'guru' && $laporan->subjectTeacher->teacher_id === $user->id), 403);

        $laporan->setRelation('siswa', $laporan->siswa->sortBy(fn ($ls) => $ls->student->name)->values());

        return view('guru.laporan-detail', compact('laporan'));
    }

    // =================================================================
    // ADMIN
    // =================================================================

    /** Admin: semua laporan yang dikirim guru beserta keterbacaannya. */
    public function adminIndex()
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $laporan = LaporanPerkembangan::where('semester', Setting::semesterAktif())
            ->with(['subjectTeacher.subject', 'subjectTeacher.kelas', 'subjectTeacher.teacher', 'siswa.student.parents', 'siswa.dibaca'])
            ->latest()->get();

        return view('admin.laporan-perkembangan', compact('laporan'));
    }

    // =================================================================
    // SISWA & ORANG TUA
    // =================================================================

    /** Siswa dan orang tua: daftar laporan dari guru. */
    public function index()
    {
        $user = Auth::user();
        abort_unless(in_array($user->role, ['siswa', 'orang_tua']), 403);

        $student = $user->siswaDipantau();
        $laporan = $student
            ? LaporanSiswa::where('student_id', $student->id)
                ->with(['laporan.subjectTeacher.subject', 'laporan.pengirim', 'dibaca'])
                ->latest()->get()
            : collect();

        return view('laporan.index', compact('student', 'laporan'));
    }

    /** Siswa dan orang tua: buka satu laporan. Untuk orang tua, waktu baca dicatat. */
    public function showSiswa(LaporanSiswa $laporanSiswa)
    {
        $user = Auth::user();
        $boleh = match ($user->role) {
            'siswa' => $laporanSiswa->student_id === $user->id,
            'orang_tua' => $user->children->contains('id', $laporanSiswa->student_id),
            default => false,
        };
        abort_unless($boleh, 403);

        if ($user->role === 'orang_tua') {
            LaporanDibaca::firstOrCreate(
                ['laporan_siswa_id' => $laporanSiswa->id, 'user_id' => $user->id],
                ['dibaca_at' => now()]
            );
            // Tampilkan anak pemilik laporan ini sebagai anak aktif
            session(['anak_id' => $laporanSiswa->student_id]);
        }

        $laporanSiswa->load(['laporan.subjectTeacher.subject', 'laporan.subjectTeacher.kelas', 'laporan.pengirim', 'student']);

        return view('laporan.show', ['ls' => $laporanSiswa]);
    }

    private function guru(): User
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'guru', 403);

        return $user;
    }

    private function pastikanPemilik(SubjectTeacher $st): void
    {
        abort_unless(Auth::user()?->role === 'guru' && $st->teacher_id === Auth::id(), 403);
    }
}