<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Kelas;
use App\Models\Notifikasi;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\SubjectTeacher;
use App\Models\UploadUnlock;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Admin: jadwal UTS/UAS dari kalender akademik, perubahan jadwal, susulan, dan buka akses unggah.
 */
class AdminPenilaianController extends Controller
{
    public function jadwal(Request $request)
    {
        $this->admin();
        $semester = Setting::semesterAktif();
        $jenis = $request->query('jenis', 'ujian');
        $kelasId = $request->query('kelas');

        $penilaian = Assessment::where('semester', $semester)
            ->when($jenis === 'ujian', fn ($q) => $q->whereIn('jenis', ['uts', 'uas']))
            ->when($jenis === 'harian', fn ($q) => $q->whereIn('jenis', Assessment::JENIS_HARIAN))
            ->when($kelasId, fn ($q) => $q->whereHas('subjectTeacher', fn ($s) => $s->where('kelas_id', $kelasId)))
            ->with(['subjectTeacher.subject', 'subjectTeacher.kelas', 'subjectTeacher.teacher', 'scores', 'makeups', 'unlocks'])
            ->orderBy('tanggal')
            ->get();

        $jumlahPerKelas = User::where('role', 'siswa')->selectRaw('kelas_id, count(*) as n')->groupBy('kelas_id')->pluck('n', 'kelas_id');

        $baris = $penilaian->map(fn ($a) => [
            'model' => $a,
            'status' => $a->statusKelas((int) ($jumlahPerKelas[$a->subjectTeacher->kelas_id] ?? 0)),
        ]);

        $kelas = Kelas::orderBy('nama_kelas')->get();
        $mapel = Subject::orderBy('nama_mapel')->get();

        return view('admin.jadwal', compact('baris', 'kelas', 'mapel', 'jenis', 'kelasId', 'semester'));
    }

    /** Buat jadwal UTS/UAS untuk banyak kelas sekaligus. */
    public function storeUjian(Request $request)
    {
        $this->admin();
        $data = $request->validate([
            'jenis' => 'required|in:uts,uas',
            'subject_id' => 'nullable|exists:subjects,id',
            'kelas_ids' => 'required|array|min:1',
            'kelas_ids.*' => 'exists:kelas,id',
            'tanggal' => 'required|date',
        ], ['kelas_ids.required' => 'Pilih minimal satu kelas.']);

        $semester = Setting::semesterAktif();
        $targets = SubjectTeacher::whereIn('kelas_id', $data['kelas_ids'])
            ->when($data['subject_id'] ?? null, fn ($q, $id) => $q->where('subject_id', $id))
            ->with(['subject', 'kelas'])
            ->get();

        $dibuat = 0;
        foreach ($targets as $st) {
            $ada = Assessment::where('subject_teacher_id', $st->id)->where('semester', $semester)->where('jenis', $data['jenis'])->exists();
            if ($ada) {
                continue;
            }
            $a = Assessment::create([
                'subject_teacher_id' => $st->id,
                'semester' => $semester,
                'jenis' => $data['jenis'],
                'judul' => strtoupper($data['jenis']) . ' ' . $st->subject->nama_mapel,
                'tanggal' => $data['tanggal'],
                'skor_maks' => 100,
                'dibuat_oleh' => Auth::id(),
            ]);
            Notifikasi::kirim(
                Notifikasi::penerimaKelas($st->kelas_id),
                'jadwal',
                $a->jenisLabel() . ' dijadwalkan',
                "{$a->judul} pada " . $a->tanggal->translatedFormat('l, d F Y') . '.',
                route('jadwal')
            );
            $dibuat++;
        }

        return back()->with('success', "{$dibuat} jadwal " . strtoupper($data['jenis']) . ' dibuat. Jadwal yang sudah ada tidak digandakan.');
    }

    /** Geser banyak jadwal sekaligus, misalnya karena bentrok undangan dinas. */
    public function geser(Request $request)
    {
        $this->admin();
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:assessments,id',
            'tanggal' => 'required|date',
            'alasan' => 'required|string|max:200',
        ], ['ids.required' => 'Centang minimal satu penilaian yang akan digeser.']);

        $digeser = 0;
        $dilewati = 0;
        foreach (Assessment::whereIn('id', $data['ids'])->with('subjectTeacher.subject')->get() as $a) {
            if ($a->scores()->exists()) {
                $dilewati++;
                continue;
            }
            PenilaianController::geserTanggal($a, Carbon::parse($data['tanggal']), $data['alasan']);
            $digeser++;
        }

        $pesan = "{$digeser} jadwal digeser dan notifikasi dikirim.";
        if ($dilewati) {
            $pesan .= " {$dilewati} jadwal dilewati karena nilainya sudah masuk.";
        }

        return back()->with('success', $pesan);
    }

    /** Detail satu penilaian: riwayat, susulan, dan akses. */
    public function show(Assessment $assessment)
    {
        $this->admin();
        $assessment->load(['subjectTeacher.subject', 'subjectTeacher.kelas', 'subjectTeacher.teacher', 'scores', 'makeups.student', 'unlocks.pengaju', 'scheduleChanges.pengubah']);
        $siswa = $assessment->subjectTeacher->students();
        $status = $assessment->statusKelas($siswa->count());

        return view('admin.jadwal-detail', compact('assessment', 'siswa', 'status'));
    }

    public function ubahTanggal(Request $request, Assessment $assessment)
    {
        $this->admin();
        $data = $request->validate([
            'tanggal' => 'required|date',
            'alasan' => 'required|string|max:200',
        ]);

        if ($assessment->scores()->exists()) {
            return back()->with('error', 'Tanggal tidak bisa diubah karena nilai sudah masuk. Gunakan susulan untuk siswa yang belum ikut.');
        }

        PenilaianController::geserTanggal($assessment, Carbon::parse($data['tanggal']), $data['alasan']);

        return back()->with('success', 'Jadwal diubah dan notifikasi dikirim.');
    }

    /** Admin menetapkan susulan untuk beberapa siswa sekaligus (misalnya peserta lomba). */
    public function susulan(Request $request, Assessment $assessment)
    {
        $this->admin();
        $data = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
            'tanggal' => 'required|date|after:' . $assessment->tanggal->toDateString(),
            'alasan' => 'required|string|max:200',
        ], [
            'student_ids.required' => 'Pilih minimal satu siswa.',
            'tanggal.after' => 'Tanggal susulan harus setelah tanggal penilaian.',
        ]);

        foreach ($data['student_ids'] as $id) {
            PenilaianController::tetapkanSusulan($assessment, (int) $id, Carbon::parse($data['tanggal']), $data['alasan']);
        }

        return back()->with('success', count($data['student_ids']) . ' siswa dijadwalkan susulan.');
    }

    // ------------- Buka akses unggah -------------

    public function akses()
    {
        $this->admin();
        $with = ['assessment.subjectTeacher.subject', 'assessment.subjectTeacher.kelas', 'pengaju', 'pemutus'];

        $menunggu = UploadUnlock::where('status', 'menunggu')->with($with)->oldest()->get();
        $riwayat = UploadUnlock::where('status', '!=', 'menunggu')->with($with)->latest('updated_at')->limit(30)->get();
        $durasi = Setting::durasiBukaAksesJam();

        return view('admin.akses', compact('menunggu', 'riwayat', 'durasi'));
    }

    public function setujui(UploadUnlock $unlock)
    {
        $this->admin();
        abort_unless($unlock->status === 'menunggu', 422);

        $this->buka($unlock);

        return back()->with('success', 'Akses dibuka selama ' . Setting::durasiBukaAksesJam() . ' jam.');
    }

    public function tolak(UploadUnlock $unlock)
    {
        $this->admin();
        abort_unless($unlock->status === 'menunggu', 422);

        $unlock->update(['status' => 'ditolak', 'diputuskan_oleh' => Auth::id()]);
        $a = $unlock->assessment;
        Notifikasi::kirim(
            [$a->subjectTeacher->teacher_id],
            'akses',
            'Pengajuan buka akses ditolak',
            "Pengajuan untuk {$a->judul} ({$a->subjectTeacher->label()}) ditolak admin.",
            route('guru.penilaian.input', $a)
        );

        return back()->with('success', 'Pengajuan ditolak.');
    }

    /** Admin membuka akses langsung tanpa pengajuan guru. */
    public function bukaLangsung(Request $request, Assessment $assessment)
    {
        $this->admin();
        $data = $request->validate(['alasan' => 'required|string|max:300']);

        $this->buka(UploadUnlock::create([
            'assessment_id' => $assessment->id,
            'alasan' => $data['alasan'],
        ]));

        return back()->with('success', 'Akses dibuka selama ' . Setting::durasiBukaAksesJam() . ' jam.');
    }

    private function buka(UploadUnlock $unlock): void
    {
        $unlock->update([
            'status' => 'dibuka',
            'diputuskan_oleh' => Auth::id(),
            'dibuka_at' => now(),
            'berakhir_at' => now()->addHours(Setting::durasiBukaAksesJam()),
        ]);

        $a = $unlock->assessment;
        Notifikasi::kirim(
            [$a->subjectTeacher->teacher_id],
            'akses',
            'Akses unggah dibuka',
            "Nilai {$a->judul} ({$a->subjectTeacher->label()}) bisa dimasukkan sampai " . $unlock->berakhir_at->translatedFormat('d F Y, H:i') . '. Nilai yang masuk tetap tercatat terlambat.',
            route('guru.penilaian.input', $a)
        );
    }

    private function admin(): void
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
    }
}