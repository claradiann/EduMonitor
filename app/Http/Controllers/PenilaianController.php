<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentMakeup;
use App\Models\AssessmentScheduleChange;
use App\Models\Grade;
use App\Models\Notifikasi;
use App\Models\Setting;
use App\Models\SubjectTeacher;
use App\Models\UploadUnlock;
use App\Models\User;
use App\Services\NilaiService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Guru: jadwal penilaian harian, input nilai, susulan, pengajuan buka akses, rekap per bab, ekspor.
 */
class PenilaianController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->guru();
        $semester = Setting::semesterAktif();

        $kelasMapel = SubjectTeacher::where('teacher_id', $user->id)
            ->with(['subject', 'kelas', 'assessments' => fn ($q) => $q->where('semester', $semester)
                ->with(['scores', 'makeups', 'unlocks'])])
            ->get()
            ->map(function ($st) {
                $jumlahSiswa = User::where('role', 'siswa')->where('kelas_id', $st->kelas_id)->count();

                $penilaian = $st->assessments->map(fn ($a) => [
                    'model' => $a,
                    'status' => $a->statusKelas($jumlahSiswa),
                    'dinilai' => $a->scores->count(),
                ]);

                return [
                    'st' => $st,
                    'jumlah_siswa' => $jumlahSiswa,
                    'penilaian' => $penilaian,
                    'terlambat' => $penilaian->where('status', 'melewati_batas')->count(),
                    'menunggu' => $penilaian->where('status', 'menunggu_nilai')->count(),
                ];
            });

        $aktif = $kelasMapel->firstWhere('st.id', (int) $request->query('kelas')) ?? $kelasMapel->first();
        $batasHari = Setting::batasUnggahHari();

        return view('guru.penilaian', compact('kelasMapel', 'aktif', 'semester', 'batasHari'));
    }

    /** Guru membuat jadwal penilaian harian. Siswa dan orang tua langsung mendapat notifikasi. */
    public function store(Request $request, SubjectTeacher $subjectTeacher)
    {
        $this->pastikanPemilikKelas($subjectTeacher);

        $data = $request->validate([
            'jenis' => 'required|in:' . implode(',', Assessment::JENIS_HARIAN),
            'bab' => 'required|integer|min:1|max:' . Assessment::MAKS_BAB,
            'judul' => 'required|string|max:100',
            'tanggal' => 'required|date|after_or_equal:today',
            'skor_maks' => 'required|numeric|min:1|max:1000',
        ], [
            'tanggal.after_or_equal' => 'Tanggal penilaian tidak boleh sebelum hari ini.',
        ]);

        $a = Assessment::create($data + [
            'subject_teacher_id' => $subjectTeacher->id,
            'semester' => Setting::semesterAktif(),
            'dibuat_oleh' => Auth::id(),
        ]);

        Notifikasi::kirim(
            Notifikasi::penerimaKelas($subjectTeacher->kelas_id),
            'jadwal',
            'Penilaian baru dijadwalkan',
            "{$a->judul} ({$subjectTeacher->subject->nama_mapel}, Bab {$a->bab}) pada " . $a->tanggal->translatedFormat('l, d F Y') . '.',
            route('jadwal')
        );

        return redirect()->route('guru.penilaian', ['kelas' => $subjectTeacher->id])
            ->with('success', 'Penilaian dijadwalkan. Siswa dan orang tua sudah mendapat notifikasi.');
    }

    /** Guru mengubah tanggal: hanya sampai akhir hari H dan selama belum ada nilai. */
    public function ubahTanggal(Request $request, Assessment $assessment)
    {
        $this->pastikanPemilik($assessment);

        if (!$assessment->bolehDiubahGuru()) {
            return back()->with('error', 'Tanggal tidak bisa diubah lagi. Hubungi admin bila jadwal perlu digeser.');
        }

        $data = $request->validate([
            'tanggal' => 'required|date|after_or_equal:today',
            'alasan' => 'required|string|max:200',
        ]);

        self::geserTanggal($assessment, Carbon::parse($data['tanggal']), $data['alasan']);

        return back()->with('success', 'Tanggal diubah dan notifikasi dikirim ke siswa dan orang tua.');
    }

    public function destroy(Assessment $assessment)
    {
        $this->pastikanPemilik($assessment);
        abort_unless($assessment->isHarian() && $assessment->scores()->doesntExist(), 403);

        $st = $assessment->subjectTeacher;
        Notifikasi::kirim(
            Notifikasi::penerimaKelas($st->kelas_id),
            'perubahan',
            'Penilaian dibatalkan',
            "{$assessment->judul} ({$st->subject->nama_mapel}) pada " . $assessment->tanggal->translatedFormat('d F Y') . ' dibatalkan.',
            route('jadwal')
        );
        $assessment->delete();

        return redirect()->route('guru.penilaian', ['kelas' => $st->id])->with('success', 'Penilaian dibatalkan.');
    }

    /** Form input nilai satu penilaian. */
    public function input(Assessment $assessment)
    {
        $this->pastikanPemilik($assessment);

        $assessment->load(['subjectTeacher.subject', 'subjectTeacher.kelas', 'scores', 'makeups', 'unlocks', 'scheduleChanges.pengubah']);
        $siswa = $assessment->subjectTeacher->students();
        $skor = $assessment->scores->keyBy('student_id');
        $susulan = $assessment->makeups->keyBy('student_id');

        $baris = $siswa->map(fn ($s) => [
            'siswa' => $s,
            'skor' => $skor->get($s->id),
            'susulan' => $susulan->get($s->id),
            'bisa' => $assessment->bisaInput($s->id),
            'status' => $assessment->statusUntukSiswa($s->id),
        ]);

        $status = $assessment->statusKelas($siswa->count());
        $akses = $assessment->aksesAktif();
        $pengajuan = $assessment->pengajuanMenunggu();

        return view('guru.penilaian-input', compact('assessment', 'baris', 'status', 'akses', 'pengajuan'));
    }

    public function simpan(Request $request, Assessment $assessment)
    {
        $this->pastikanPemilik($assessment);

        $request->validate([
            'skor' => 'array',
            'skor.*' => 'nullable|numeric|min:0|max:' . $assessment->skor_maks,
        ], [
            'skor.*.max' => 'Skor tidak boleh melebihi skor maksimal ' . rtrim(rtrim(number_format($assessment->skor_maks, 2, ',', ''), '0'), ',') . '.',
        ]);

        $assessment->load(['subjectTeacher.subject', 'makeups', 'unlocks']);
        $tersimpan = 0;
        $terkunci = 0;

        foreach ($request->input('skor', []) as $studentId => $nilai) {
            if ($nilai === null || $nilai === '') {
                continue;
            }
            if (!$assessment->bisaInput((int) $studentId)) {
                $terkunci++;
                continue;
            }
            NilaiService::simpanSkor($assessment, (int) $studentId, (float) $nilai);
            $tersimpan++;
        }

        $pesan = "{$tersimpan} nilai tersimpan.";
        if ($terkunci) {
            $pesan .= " {$terkunci} nilai tidak disimpan karena masa input sudah ditutup.";
        }

        return redirect()->route('guru.penilaian.input', $assessment)->with($terkunci ? 'error' : 'success', $pesan);
    }

    /** Tetapkan tanggal susulan untuk siswa tertentu. */
    public function susulan(Request $request, Assessment $assessment)
    {
        $this->pastikanPemilik($assessment);

        $data = $request->validate([
            'student_id' => 'required|exists:users,id',
            'tanggal' => 'required|date|after:' . $assessment->tanggal->toDateString(),
            'alasan' => 'required|string|max:200',
        ], [
            'tanggal.after' => 'Tanggal susulan harus setelah tanggal penilaian.',
        ]);

        self::tetapkanSusulan($assessment, (int) $data['student_id'], Carbon::parse($data['tanggal']), $data['alasan']);

        return back()->with('success', 'Tanggal susulan disimpan dan notifikasi dikirim.');
    }

    public function hapusSusulan(Assessment $assessment, AssessmentMakeup $makeup)
    {
        $this->pastikanPemilik($assessment);
        abort_unless($makeup->assessment_id === $assessment->id, 404);
        $makeup->delete();

        return back()->with('success', 'Susulan dihapus.');
    }

    /** Guru mengajukan pembukaan akses unggah ke admin. */
    public function ajukanAkses(Request $request, Assessment $assessment)
    {
        $this->pastikanPemilik($assessment);
        $data = $request->validate(['alasan' => 'required|string|max:300']);

        if ($assessment->pengajuanMenunggu()) {
            return back()->with('error', 'Pengajuan sebelumnya masih menunggu persetujuan admin.');
        }

        UploadUnlock::create([
            'assessment_id' => $assessment->id,
            'diajukan_oleh' => Auth::id(),
            'alasan' => $data['alasan'],
        ]);

        $st = $assessment->subjectTeacher;
        Notifikasi::kirim(
            User::where('role', 'admin')->pluck('id'),
            'akses',
            'Pengajuan buka akses unggah',
            Auth::user()->name . " mengajukan buka akses untuk {$assessment->judul} ({$st->label()}).",
            route('admin.akses')
        );

        return back()->with('success', 'Pengajuan dikirim ke admin.');
    }

    /** Rekap rata-rata nilai setiap siswa per bab untuk satu kelas-mapel. */
    public function rekapBab(SubjectTeacher $subjectTeacher)
    {
        $this->pastikanPemilikKelas($subjectTeacher);
        $semester = Setting::semesterAktif();
        $subjectTeacher->load(['subject', 'kelas']);

        $grades = Grade::where('subject_id', $subjectTeacher->subject_id)->where('semester', $semester)->get()->keyBy('student_id');

        $baris = $subjectTeacher->students()->map(fn ($s) => [
            'siswa' => $s,
            'bab' => NilaiService::rekapBab($s->id, $subjectTeacher->subject_id, $semester),
            'grade' => $grades->get($s->id),
        ]);

        $babDipakai = $baris->flatMap(fn ($b) => array_keys($b['bab']))->unique()->sort()->values();

        $rataBab = $babDipakai->mapWithKeys(fn ($bab) => [
            $bab => round($baris->map(fn ($b) => $b['bab'][$bab] ?? null)->filter(fn ($v) => $v !== null)->avg(), 1),
        ]);

        return view('guru.rekap-bab', compact('subjectTeacher', 'baris', 'babDipakai', 'rataBab', 'semester'));
    }

    /** Ekspor nilai akhir satu kelas-mapel (sementara CSV, menyesuaikan template e-rapor setelah diterima). */
    public function ekspor(SubjectTeacher $subjectTeacher)
    {
        $this->pastikanPemilikKelas($subjectTeacher);
        $semester = Setting::semesterAktif();
        $subjectTeacher->load(['subject', 'kelas']);

        $grades = Grade::where('subject_id', $subjectTeacher->subject_id)->where('semester', $semester)->get()->keyBy('student_id');

        $rows = [
            ['Mata Pelajaran', $subjectTeacher->subject->nama_mapel],
            ['Kelas', $subjectTeacher->kelas->nama_kelas],
            ['Semester', $semester],
            [],
            ['No', 'NISN', 'Nama Siswa', 'Nilai Harian', 'UTS', 'UAS', 'Nilai Akhir'],
        ];

        foreach ($subjectTeacher->students()->values() as $i => $s) {
            $g = $grades->get($s->id);
            $rows[] = [
                $i + 1, $s->nisn, $s->name,
                $g ? number_format($g->harian, 2, ',', '') : '',
                $g?->nilai_uts ?: '', $g?->nilai_uas ?: '', $g?->average ?: '',
            ];
        }

        $nama = 'nilai-' . str($subjectTeacher->subject->nama_mapel . '-' . $subjectTeacher->kelas->nama_kelas)->slug() . '.csv';

        return self::unduhCsv($rows, $nama);
    }

    // ------------- Dipakai bersama oleh guru dan admin -------------

    public static function geserTanggal(Assessment $assessment, Carbon $baru, string $alasan): void
    {
        $lama = $assessment->tanggal->copy();
        if ($lama->isSameDay($baru)) {
            return;
        }

        AssessmentScheduleChange::create([
            'assessment_id' => $assessment->id,
            'tanggal_lama' => $lama,
            'tanggal_baru' => $baru,
            'alasan' => $alasan,
            'diubah_oleh' => Auth::id(),
        ]);

        $assessment->update(['tanggal' => $baru, 'pengingat_terkirim_at' => null]);

        $st = $assessment->subjectTeacher;
        $arah = $baru->gt($lama) ? 'diundur' : 'dimajukan';
        Notifikasi::kirim(
            Notifikasi::penerimaKelas($st->kelas_id),
            'perubahan',
            'Jadwal penilaian ' . $arah,
            "{$assessment->judul} ({$st->subject->nama_mapel}) {$arah} dari " . $lama->translatedFormat('d F') . ' ke ' . $baru->translatedFormat('l, d F Y') . ". Alasan: {$alasan}",
            route('jadwal')
        );
    }

    public static function tetapkanSusulan(Assessment $assessment, int $studentId, Carbon $tanggal, string $alasan): void
    {
        $siswa = User::where('role', 'siswa')->where('kelas_id', $assessment->subjectTeacher->kelas_id)->findOrFail($studentId);

        AssessmentMakeup::updateOrCreate(
            ['assessment_id' => $assessment->id, 'student_id' => $siswa->id],
            ['tanggal' => $tanggal, 'alasan' => $alasan, 'dibuat_oleh' => Auth::id()]
        );

        Notifikasi::kirim(
            Notifikasi::penerimaSiswa([$siswa->id]),
            'susulan',
            'Jadwal susulan',
            "{$siswa->name} mengikuti susulan {$assessment->judul} ({$assessment->subjectTeacher->subject->nama_mapel}) pada " . $tanggal->translatedFormat('l, d F Y') . '.',
            route('jadwal')
        );
    }

    public static function unduhCsv(array $rows, string $nama)
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($out, $row, ';');
            }
            fclose($out);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function guru(): User
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'guru', 403);

        return $user;
    }

    private function pastikanPemilik(Assessment $assessment): void
    {
        $this->pastikanPemilikKelas($assessment->subjectTeacher);
    }

    private function pastikanPemilikKelas(SubjectTeacher $st): void
    {
        abort_unless(Auth::user()?->role === 'guru' && $st->teacher_id === Auth::id(), 403);
    }
}