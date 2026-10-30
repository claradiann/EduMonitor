<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable(['subject_teacher_id', 'semester', 'jenis', 'bab', 'judul', 'tanggal', 'skor_maks', 'dibuat_oleh', 'pengingat_terkirim_at'])]
class Assessment extends Model
{
    public const JENIS = [
        'tugas' => 'Tugas',
        'kuis' => 'Kuis',
        'uh' => 'Ulangan Harian',
        'uts' => 'UTS',
        'uas' => 'UAS',
    ];

    /** Jenis penilaian harian yang dijadwalkan guru dan dikelompokkan per bab. */
    public const JENIS_HARIAN = ['tugas', 'kuis', 'uh'];

    /** Kuota bab per mata pelajaran. */
    public const MAKS_BAB = 5;

    public const STATUS = [
        'dijadwalkan' => ['label' => 'Dijadwalkan', 'class' => 'badge-sky', 'icon' => 'fa-calendar'],
        'menunggu_nilai' => ['label' => 'Menunggu Nilai', 'class' => 'badge-amber', 'icon' => 'fa-hourglass-half'],
        'melewati_batas' => ['label' => 'Melewati Batas Unggah', 'class' => 'badge-rose', 'icon' => 'fa-circle-exclamation'],
        'nilai_tersedia' => ['label' => 'Nilai Tersedia', 'class' => 'badge-green', 'icon' => 'fa-circle-check'],
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'skor_maks' => 'float',
            'bab' => 'integer',
            'pengingat_terkirim_at' => 'datetime',
        ];
    }

    public function subjectTeacher(): BelongsTo
    {
        return $this->belongsTo(SubjectTeacher::class, 'subject_teacher_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(AssessmentScore::class);
    }

    public function makeups(): HasMany
    {
        return $this->hasMany(AssessmentMakeup::class);
    }

    public function scheduleChanges(): HasMany
    {
        return $this->hasMany(AssessmentScheduleChange::class)->latest();
    }

    public function unlocks(): HasMany
    {
        return $this->hasMany(UploadUnlock::class)->latest();
    }

    public function jenisLabel(): string
    {
        return self::JENIS[$this->jenis] ?? ucfirst($this->jenis);
    }

    public function isHarian(): bool
    {
        return in_array($this->jenis, self::JENIS_HARIAN);
    }

    /** Label singkat, misalnya "Bab 2 · Kuis". */
    public function labelJenis(): string
    {
        return $this->isHarian() && $this->bab ? "Bab {$this->bab} · {$this->jenisLabel()}" : $this->jenisLabel();
    }

    /** Tanggal yang berlaku untuk seorang siswa: tanggal susulan bila ada. */
    public function tanggalUntuk(?int $studentId = null): Carbon
    {
        if ($studentId) {
            $susulan = $this->relationLoaded('makeups')
                ? $this->makeups->firstWhere('student_id', $studentId)
                : $this->makeups()->where('student_id', $studentId)->first();
            if ($susulan) {
                return $susulan->tanggal->copy();
            }
        }

        return $this->tanggal->copy();
    }

    /** Batas unggah = akhir hari ke-7 setelah tanggal pelaksanaan (atau tanggal susulan). */
    public function batasUnggah(?int $studentId = null): Carbon
    {
        return $this->tanggalUntuk($studentId)->addDays(Setting::batasUnggahHari())->endOfDay();
    }

    /** Akses unggah yang sedang dibuka admin, bila ada. */
    public function aksesAktif(): ?UploadUnlock
    {
        $unlocks = $this->relationLoaded('unlocks') ? $this->unlocks : $this->unlocks()->get();

        return $unlocks->first(fn ($u) => $u->aktif());
    }

    public function pengajuanMenunggu(): ?UploadUnlock
    {
        $unlocks = $this->relationLoaded('unlocks') ? $this->unlocks : $this->unlocks()->get();

        return $unlocks->firstWhere('status', 'menunggu');
    }

    /**
     * Apakah nilai siswa ini boleh dimasukkan atau diubah sekarang.
     * Terbuka sejak tanggal pelaksanaan sampai batas unggah, atau selama akses dibuka admin.
     */
    public function bisaInput(?int $studentId = null): bool
    {
        $now = now();
        if ($now->lt($this->tanggalUntuk($studentId)->startOfDay())) {
            return false;
        }

        return $now->lte($this->batasUnggah($studentId)) || $this->aksesAktif() !== null;
    }

    /** Status otomatis berdasarkan tanggal dan ada tidaknya nilai. */
    public function statusBerdasarkan(bool $nilaiAda, ?int $studentId = null): string
    {
        if ($nilaiAda) {
            return 'nilai_tersedia';
        }
        if (today()->lt($this->tanggalUntuk($studentId))) {
            return 'dijadwalkan';
        }
        if (now()->gt($this->batasUnggah($studentId))) {
            return 'melewati_batas';
        }

        return 'menunggu_nilai';
    }

    /** Status untuk satu siswa (halaman siswa dan orang tua). */
    public function statusUntukSiswa(int $studentId): string
    {
        $ada = $this->relationLoaded('scores')
            ? $this->scores->contains('student_id', $studentId)
            : $this->scores()->where('student_id', $studentId)->exists();

        return $this->statusBerdasarkan($ada, $studentId);
    }

    /**
     * Status untuk satu kelas. Siswa susulan tidak ikut dihitung,
     * sehingga status kelas tidak tertahan oleh satu atau dua siswa.
     */
    public function statusKelas(int $jumlahSiswa): string
    {
        $susulanIds = ($this->relationLoaded('makeups') ? $this->makeups : $this->makeups()->get())->pluck('student_id');
        $scores = $this->relationLoaded('scores') ? $this->scores : $this->scores()->get();

        $wajib = $jumlahSiswa - $susulanIds->count();
        $dinilai = $scores->whereNotIn('student_id', $susulanIds)->count();

        return $this->statusBerdasarkan($wajib > 0 && $dinilai >= $wajib);
    }

    /** Guru hanya boleh mengubah tanggal sampai akhir hari H dan selama belum ada nilai. */
    public function bolehDiubahGuru(): bool
    {
        $adaNilai = $this->relationLoaded('scores') ? $this->scores->isNotEmpty() : $this->scores()->exists();

        return $this->isHarian() && !$adaNilai && today()->lte($this->tanggal);
    }

    /**
     * Keterlambatan untuk rekap admin: masih melewati batas, atau ada nilai yang masuk setelah batas.
     * Mengembalikan lama keterlambatan dalam menit, atau null bila tepat waktu.
     */
    public function keterlambatanMenit(int $jumlahSiswa): ?int
    {
        $scores = $this->relationLoaded('scores') ? $this->scores : $this->scores()->get();
        $tercatat = $scores->where('terlambat', true)->max('terlambat_menit');

        if ($this->statusKelas($jumlahSiswa) === 'melewati_batas') {
            $berjalan = (int) $this->batasUnggah()->diffInMinutes(now());

            return max($berjalan, (int) $tercatat);
        }

        return $tercatat ? (int) $tercatat : null;
    }
}