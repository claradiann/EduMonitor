<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Laporan perkembangan belajar satu kelas-mapel yang dikirim guru ke orang tua. */
#[Fillable(['subject_teacher_id', 'semester', 'bab', 'judul', 'pesan', 'dikirim_oleh'])]
class LaporanPerkembangan extends Model
{
    protected $table = 'laporan_perkembangan';

    public function subjectTeacher(): BelongsTo
    {
        return $this->belongsTo(SubjectTeacher::class);
    }

    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikirim_oleh');
    }

    public function siswa(): HasMany
    {
        return $this->hasMany(LaporanSiswa::class);
    }

    /**
     * Ringkasan keterbacaan oleh orang tua: [sudah membaca, total orang tua].
     * Butuh relasi siswa.student.parents dan siswa.dibaca.
     */
    public function keterbacaan(): array
    {
        $total = 0;
        $baca = 0;
        foreach ($this->siswa as $ls) {
            $ortuIds = $ls->student?->parents->pluck('id') ?? collect();
            $total += $ortuIds->count();
            $baca += $ls->dibaca->whereIn('user_id', $ortuIds)->count();
        }

        return [$baca, $total];
    }
}