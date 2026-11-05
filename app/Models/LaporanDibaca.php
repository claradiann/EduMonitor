<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Catatan kapan seorang orang tua membuka laporan anaknya (status baca laporan). */
#[Fillable(['laporan_siswa_id', 'user_id', 'dibaca_at'])]
class LaporanDibaca extends Model
{
    protected $table = 'laporan_dibaca';

    public $timestamps = false;

    protected function casts(): array
    {
        return ['dibaca_at' => 'datetime'];
    }

    public function laporanSiswa(): BelongsTo
    {
        return $this->belongsTo(LaporanSiswa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}