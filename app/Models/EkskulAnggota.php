<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Keanggotaan siswa di satu ekstrakurikuler per semester, beserta nilainya. */
#[Fillable(['ekstrakurikuler_id', 'student_id', 'semester', 'predikat', 'deskripsi'])]
class EkskulAnggota extends Model
{
    protected $table = 'ekskul_anggota';

    public function ekstrakurikuler(): BelongsTo
    {
        return $this->belongsTo(Ekstrakurikuler::class, 'ekstrakurikuler_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}