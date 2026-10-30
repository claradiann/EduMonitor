<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['assessment_id', 'student_id', 'skor', 'nilai_konversi', 'terlambat', 'terlambat_menit'])]
class AssessmentScore extends Model
{
    protected function casts(): array
    {
        return [
            'skor' => 'float',
            'nilai_konversi' => 'float',
            'terlambat' => 'boolean',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}