<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'pembina_id'])]
class Ekstrakurikuler extends Model
{
    protected $table = 'ekstrakurikuler';

    public const PREDIKAT = [
        'A' => 'Sangat Baik',
        'B' => 'Baik',
        'C' => 'Cukup',
        'D' => 'Kurang',
    ];

    public function pembina(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembina_id');
    }

    public function anggota(): HasMany
    {
        return $this->hasMany(EkskulAnggota::class, 'ekstrakurikuler_id');
    }
}