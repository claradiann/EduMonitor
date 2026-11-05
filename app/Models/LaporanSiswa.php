<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Isi laporan untuk satu siswa: salinan nilai saat dikirim dan catatan guru. */
#[Fillable(['laporan_perkembangan_id', 'student_id', 'isi', 'catatan'])]
class LaporanSiswa extends Model
{
    protected $table = 'laporan_siswa';

    protected function casts(): array
    {
        return ['isi' => 'array'];
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanPerkembangan::class, 'laporan_perkembangan_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function dibaca(): HasMany
    {
        return $this->hasMany(LaporanDibaca::class);
    }

    public function dibacaOleh(int $userId): ?LaporanDibaca
    {
        return $this->dibaca->firstWhere('user_id', $userId);
    }
}