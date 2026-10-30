<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Tanggal susulan untuk siswa tertentu pada satu penilaian. */
#[Fillable(['assessment_id', 'student_id', 'tanggal', 'alasan', 'dibuat_oleh'])]
class AssessmentMakeup extends Model
{
    protected function casts(): array
    {
        return ['tanggal' => 'date'];
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