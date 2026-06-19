<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'subject_id', 'nilai_tugas', 'nilai_uh', 'nilai_uts', 'nilai_uas', 'semester'])]
class Grade extends Model
{
    use HasFactory;

    /**
     * Get the student.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the subject.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Accessor for average score.
     */
    public function getAverageAttribute(): float
    {
        return round(($this->nilai_tugas + $this->nilai_uh + $this->nilai_uts + $this->nilai_uas) / 4, 1);
    }
}
