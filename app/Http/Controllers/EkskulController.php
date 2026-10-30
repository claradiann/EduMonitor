<?php

namespace App\Http\Controllers;

use App\Models\EkskulAnggota;
use App\Models\Ekstrakurikuler;
use App\Models\Kelas;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Ekstrakurikuler: admin mengatur daftar dan anggota, pembina (atau admin)
 * mengisi predikat A-D dan deskripsi sekali di akhir semester.
 */
class EkskulController extends Controller
{
    // ------------- Admin -------------

    public function index()
    {
        $this->admin();
        $semester = Setting::semesterAktif();

        $ekskul = Ekstrakurikuler::with(['pembina', 'anggota' => fn ($q) => $q->where('semester', $semester)])
            ->orderBy('nama')->get();
        $guru = User::where('role', 'guru')->orderBy('name')->get();

        return view('admin.ekskul', compact('ekskul', 'guru', 'semester'));
    }

    public function store(Request $request)
    {
        $this->admin();
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'pembina_id' => 'nullable|exists:users,id',
        ]);
        Ekstrakurikuler::create($data);

        return back()->with('success', 'Ekstrakurikuler ditambahkan.');
    }

    public function update(Request $request, Ekstrakurikuler $ekskul)
    {
        $this->admin();
        $ekskul->update($request->validate([
            'nama' => 'required|string|max:100',
            'pembina_id' => 'nullable|exists:users,id',
        ]));

        return back()->with('success', 'Ekstrakurikuler diperbarui.');
    }

    public function destroy(Ekstrakurikuler $ekskul)
    {
        $this->admin();
        $ekskul->delete();

        return back()->with('success', 'Ekstrakurikuler dihapus.');
    }

    /** Halaman anggota + nilai satu ekskul (admin dan pembina). */
    public function show(Ekstrakurikuler $ekskul)
    {
        $this->bolehKelola($ekskul);
        $semester = Setting::semesterAktif();

        $anggota = $ekskul->anggota()->where('semester', $semester)->with('student.kelas')->get()
            ->sortBy(fn ($a) => $a->student->kelas?->nama_kelas . $a->student->name)->values();

        $kelas = Kelas::orderBy('nama_kelas')->get();
        $calon = User::where('role', 'siswa')->whereNotIn('id', $anggota->pluck('student_id'))->with('kelas')->orderBy('name')->get();
        $batas = Setting::batasNilaiAkhir();

        return view('ekskul.show', compact('ekskul', 'anggota', 'calon', 'kelas', 'semester', 'batas'));
    }

    public function tambahAnggota(Request $request, Ekstrakurikuler $ekskul)
    {
        $this->admin();
        $data = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
        ], ['student_ids.required' => 'Pilih minimal satu siswa.']);

        $semester = Setting::semesterAktif();
        foreach ($data['student_ids'] as $id) {
            EkskulAnggota::firstOrCreate(['ekstrakurikuler_id' => $ekskul->id, 'student_id' => $id, 'semester' => $semester]);
        }

        return back()->with('success', count($data['student_ids']) . ' siswa ditambahkan sebagai anggota.');
    }

    public function hapusAnggota(Ekstrakurikuler $ekskul, EkskulAnggota $anggota)
    {
        $this->admin();
        abort_unless($anggota->ekstrakurikuler_id === $ekskul->id, 404);
        $anggota->delete();

        return back()->with('success', 'Anggota dihapus.');
    }

    public function simpanNilai(Request $request, Ekstrakurikuler $ekskul)
    {
        $this->bolehKelola($ekskul);

        $batas = Setting::batasNilaiAkhir();
        if ($batas && now()->gt($batas) && Auth::user()->role !== 'admin') {
            return back()->with('error', 'Batas pengumpulan nilai akhir sudah lewat. Hubungi admin.');
        }

        $request->validate([
            'predikat' => 'array',
            'predikat.*' => 'nullable|in:' . implode(',', array_keys(Ekstrakurikuler::PREDIKAT)),
            'deskripsi' => 'array',
            'deskripsi.*' => 'nullable|string|max:500',
        ]);

        $anggota = $ekskul->anggota()->where('semester', Setting::semesterAktif())->get()->keyBy('id');
        foreach ($request->input('predikat', []) as $id => $predikat) {
            if ($a = $anggota->get($id)) {
                $a->update(['predikat' => $predikat ?: null, 'deskripsi' => $request->input("deskripsi.$id")]);
            }
        }

        return back()->with('success', 'Nilai ekstrakurikuler disimpan.');
    }

    public function ekspor(Ekstrakurikuler $ekskul)
    {
        $this->bolehKelola($ekskul);
        $semester = Setting::semesterAktif();

        $rows = [
            ['Ekstrakurikuler', $ekskul->nama],
            ['Semester', $semester],
            [],
            ['No', 'NISN', 'Nama Siswa', 'Kelas', 'Predikat', 'Deskripsi'],
        ];
        $anggota = $ekskul->anggota()->where('semester', $semester)->with('student.kelas')->get()->sortBy('student.name')->values();
        foreach ($anggota as $i => $a) {
            $rows[] = [$i + 1, $a->student->nisn, $a->student->name, $a->student->kelas?->nama_kelas, $a->predikat, $a->deskripsi];
        }

        return PenilaianController::unduhCsv($rows, 'ekskul-' . str($ekskul->nama)->slug() . '.csv');
    }

    // ------------- Guru pembina -------------

    public function pembina()
    {
        $user = Auth::user();
        abort_unless($user->role === 'guru', 403);
        $semester = Setting::semesterAktif();

        $ekskul = Ekstrakurikuler::where('pembina_id', $user->id)
            ->withCount(['anggota as jumlah' => fn ($q) => $q->where('semester', $semester)])
            ->withCount(['anggota as terisi' => fn ($q) => $q->where('semester', $semester)->whereNotNull('predikat')])
            ->orderBy('nama')->get();

        return view('ekskul.pembina', compact('ekskul', 'semester'));
    }

    private function admin(): void
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
    }

    private function bolehKelola(Ekstrakurikuler $ekskul): void
    {
        $u = Auth::user();
        abort_unless($u && ($u->role === 'admin' || ($u->role === 'guru' && $ekskul->pembina_id === $u->id)), 403);
    }
}