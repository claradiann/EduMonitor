<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Grade;
use App\Models\Setting;
use App\Models\SubjectTeacher;
use Illuminate\Support\Collection;

/**
 * Menyusun isi laporan perkembangan belajar: nilai siswa dan rata-rata kelas sebagai pembanding.
 */
class LaporanService
{
    /** Bab yang sudah memiliki nilai harian di kelas-mapel ini. */
    public static function babTersedia(SubjectTeacher $st, string $semester): Collection
    {
        return Assessment::where('subject_teacher_id', $st->id)->where('semester', $semester)
            ->whereIn('jenis', Assessment::JENIS_HARIAN)->whereNotNull('bab')
            ->whereHas('scores')
            ->distinct()->orderBy('bab')->pluck('bab')->map(fn ($b) => (int) $b)->values();
    }

    public static function judulBawaan(SubjectTeacher $st, ?int $bab): string
    {
        return $st->subject->nama_mapel . ($bab ? " Bab {$bab}" : ' semester ' . Setting::semesterAktif());
    }

    /**
     * Isi laporan per siswa, dengan rata-rata kelas sebagai pembanding.
     * Bentuk: ['utama' => [label, nilai, kelas, sementara], 'baris' => [[label, keterangan, nilai, kelas], ...]]
     *
     * @return array<int, array> [student_id => isi]
     */
    public static function susunIsi(SubjectTeacher $st, ?int $bab, string $semester): array
    {
        $siswa = $st->students();
        $rata = fn ($nilai) => ($c = collect($nilai)->filter(fn ($v) => $v !== null))->isEmpty() ? null : round($c->avg(), 1);
        $hasil = [];

        if ($bab !== null) {
            // Laporan satu bab: setiap tugas, kuis, dan ulangan di bab itu
            $penilaian = Assessment::where('subject_teacher_id', $st->id)->where('semester', $semester)
                ->whereIn('jenis', Assessment::JENIS_HARIAN)->where('bab', $bab)
                ->whereHas('scores')->with('scores')->orderBy('tanggal')->get();

            $nilaiBab = [];
            foreach ($siswa as $s) {
                $skor = $penilaian->map(fn ($a) => $a->scores->firstWhere('student_id', $s->id)?->nilai_konversi);
                $nilaiBab[$s->id] = $rata($skor);
            }
            $rataBab = $rata($nilaiBab);

            foreach ($siswa as $s) {
                $hasil[$s->id] = [
                    'utama' => ['label' => "Nilai Bab {$bab}", 'nilai' => $nilaiBab[$s->id], 'kelas' => $rataBab, 'sementara' => false],
                    'baris' => $penilaian->map(fn ($a) => [
                        'label' => $a->judul,
                        'keterangan' => $a->jenisLabel() . ' · ' . $a->tanggal->translatedFormat('d M'),
                        'nilai' => ($v = $a->scores->firstWhere('student_id', $s->id)?->nilai_konversi) !== null ? round($v, 1) : null,
                        'kelas' => round($a->scores->avg('nilai_konversi'), 1),
                    ])->values()->all(),
                ];
            }

            return $hasil;
        }

        // Ringkasan semester: nilai per bab, UTS, UAS, dan nilai akhir
        $perBab = $siswa->mapWithKeys(fn ($s) => [$s->id => NilaiService::rekapBab($s->id, $st->subject_id, $semester)]);
        $daftarBab = $perBab->flatMap(fn ($b) => array_keys($b))->unique()->sort()->values();
        $grades = Grade::where('subject_id', $st->subject_id)->where('semester', $semester)
            ->whereIn('student_id', $siswa->pluck('id'))->get()->keyBy('student_id');
        $ujian = Assessment::where('subject_teacher_id', $st->id)->where('semester', $semester)
            ->whereIn('jenis', ['uts', 'uas'])->whereHas('scores')->with('scores')->orderBy('tanggal')->get();

        $rataBab = $daftarBab->mapWithKeys(fn ($b) => [$b => $rata($perBab->map(fn ($x) => $x[$b] ?? null))]);
        $rataAkhir = $rata($siswa->map(fn ($s) => ($g = $grades->get($s->id)) && $g->average ? $g->average : null));

        foreach ($siswa as $s) {
            $g = $grades->get($s->id);
            $baris = $daftarBab->map(fn ($b) => [
                'label' => "Bab {$b}",
                'keterangan' => 'Rata-rata tugas, kuis, dan ulangan',
                'nilai' => isset($perBab[$s->id][$b]) ? round($perBab[$s->id][$b], 1) : null,
                'kelas' => $rataBab[$b],
            ])->values()->all();
            foreach ($ujian as $u) {
                $baris[] = [
                    'label' => $u->jenisLabel(),
                    'keterangan' => $u->tanggal->translatedFormat('d M'),
                    'nilai' => ($v = $u->scores->firstWhere('student_id', $s->id)?->nilai_konversi) !== null ? round($v, 1) : null,
                    'kelas' => round($u->scores->avg('nilai_konversi'), 1),
                ];
            }

            $hasil[$s->id] = [
                'utama' => [
                    'label' => 'Nilai akhir',
                    'nilai' => $g && $g->average ? $g->average : null,
                    'kelas' => $rataAkhir,
                    'sementara' => $g ? $g->sementara : true,
                ],
                'baris' => $baris,
            ];
        }

        return $hasil;
    }
}