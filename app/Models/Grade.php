<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'subject_id', 'nilai_tugas', 'nilai_uh', 'nilai_harian', 'nilai_uts', 'nilai_uas', 'semester'])]
class Grade extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'nilai_harian' => 'float',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Nilai harian (NH): rata-rata nilai bab, dengan nilai bab = rata-rata
     * seluruh tugas, kuis, dan UH (hasil konversi) dalam bab tersebut.
     * Data lama tanpa nilai_harian memakai rata-rata kolom tugas dan UH.
     */
    public function getHarianAttribute(): float
    {
        if ($this->nilai_harian !== null) {
            return round($this->nilai_harian, 2);
        }

        $parts = collect([$this->nilai_tugas, $this->nilai_uh])->filter(fn ($v) => $v > 0);

        return $parts->isEmpty() ? 0 : round($parts->avg(), 2);
    }

    /**
     * Nilai akhir: NA = (3 x NH + UTS + UAS) / 5, dibulatkan ke bilangan bulat terdekat
     * (desimal di bawah 0,5 ke bawah, 0,5 ke atas ke atas).
     * Selama UTS atau UAS belum ada, nilai akhir dihitung sementara dari komponen yang sudah ada.
     */
    public function getAverageAttribute(): int
    {
        $komponen = collect([
            ['nilai' => $this->harian, 'bobot' => 3],
            ['nilai' => $this->nilai_uts, 'bobot' => 1],
            ['nilai' => $this->nilai_uas, 'bobot' => 1],
        ])->filter(fn ($k) => $k['nilai'] > 0);

        $totalBobot = $komponen->sum('bobot');

        return $totalBobot > 0
            ? (int) round($komponen->sum(fn ($k) => $k['nilai'] * $k['bobot']) / $totalBobot, 0, PHP_ROUND_HALF_UP)
            : 0;
    }

    /** Apakah nilai akhir masih sementara (UTS atau UAS belum ada). */
    public function getSementaraAttribute(): bool
    {
        return $this->nilai_uts <= 0 || $this->nilai_uas <= 0;
    }
}