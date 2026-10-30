<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\Grade;
use App\Models\Notifikasi;
use Illuminate\Support\Collection;

class NilaiService
{
    /** Konversi skor ke skala 0-100: NK = (S / Smaks) x 100. */
    public static function konversi(float $skor, float $skorMaks): float
    {
        if ($skorMaks <= 0) {
            return 0;
        }

        return round(min($skor, $skorMaks) / $skorMaks * 100, 2);
    }

    /** Seluruh skor satu siswa pada satu mapel di semester tertentu. */
    private static function skorSiswa(int $studentId, int $subjectId, string $semester): Collection
    {
        return AssessmentScore::where('student_id', $studentId)
            ->whereHas('assessment', function ($q) use ($subjectId, $semester) {
                $q->where('semester', $semester)
                    ->whereHas('subjectTeacher', fn ($st) => $st->where('subject_id', $subjectId));
            })
            ->with('assessment')
            ->get();
    }

    /**
     * Nilai per bab: rata-rata seluruh tugas, kuis, dan UH dalam bab itu.
     * Bab yang belum punya penilaian tidak ikut (tidak dihitung 0).
     */
    public static function nilaiPerBab(Collection $scores): array
    {
        return $scores
            ->filter(fn ($s) => $s->assessment->isHarian() && $s->assessment->bab)
            ->groupBy(fn ($s) => $s->assessment->bab)
            ->map(fn ($g) => round($g->avg('nilai_konversi'), 2))
            ->sortKeys()
            ->all();
    }

    public static function rekapBab(int $studentId, int $subjectId, string $semester): array
    {
        return self::nilaiPerBab(self::skorSiswa($studentId, $subjectId, $semester));
    }

    /**
     * Hitung ulang rekap nilai satu siswa pada satu mapel.
     * NH = rata-rata nilai bab. UTS dan UAS dari skornya masing-masing.
     */
    public static function hitungUlang(int $studentId, int $subjectId, string $semester): Grade
    {
        $scores = self::skorSiswa($studentId, $subjectId, $semester);
        $perBab = self::nilaiPerBab($scores);

        $avg = fn ($jenis) => $scores->filter(fn ($s) => in_array($s->assessment->jenis, (array) $jenis))->avg('nilai_konversi');

        $grade = Grade::firstOrNew([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'semester' => $semester,
        ]);

        $grade->nilai_harian = count($perBab) ? round(array_sum($perBab) / count($perBab), 2) : null;
        // Kolom lama tetap diisi agar halaman lama tetap terbaca
        $grade->nilai_tugas = (int) round($avg(['tugas', 'kuis']) ?? 0);
        $grade->nilai_uh = (int) round($avg('uh') ?? 0);
        $grade->nilai_uts = (int) round($avg('uts') ?? 0);
        $grade->nilai_uas = (int) round($avg('uas') ?? 0);
        $grade->save();

        return $grade;
    }

    /**
     * Simpan skor satu siswa. Nilai yang masuk setelah batas (lewat akses yang dibuka admin)
     * ditandai terlambat beserta lama keterlambatannya.
     */
    public static function simpanSkor(Assessment $assessment, int $studentId, float $skor): AssessmentScore
    {
        $batas = $assessment->batasUnggah($studentId);
        $terlambat = now()->gt($batas);

        $score = AssessmentScore::firstOrNew(['assessment_id' => $assessment->id, 'student_id' => $studentId]);
        $baru = !$score->exists;

        $score->fill([
            'skor' => $skor,
            'nilai_konversi' => self::konversi($skor, $assessment->skor_maks),
        ]);
        if ($terlambat) {
            $score->terlambat = true;
            $score->terlambat_menit = (int) $batas->diffInMinutes(now());
        }
        $score->save();

        self::hitungUlang($studentId, $assessment->subjectTeacher->subject_id, $assessment->semester);

        if ($baru) {
            Notifikasi::kirim(
                Notifikasi::penerimaSiswa([$studentId]),
                'nilai',
                'Nilai sudah tersedia',
                "Nilai {$assessment->judul} ({$assessment->subjectTeacher->subject->nama_mapel}) sudah bisa dilihat.",
                route('nilai')
            );
        }

        return $score;
    }
}