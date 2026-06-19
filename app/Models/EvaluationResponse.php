<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['student_id', 'subject_teacher_id', 'comments', 'semester'])]
class EvaluationResponse extends Model
{
    use HasFactory;

    /**
     * Get the student who gave the response.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the subject-teacher combination being evaluated.
     */
    public function subjectTeacher(): BelongsTo
    {
        return $this->belongsTo(SubjectTeacher::class, 'subject_teacher_id');
    }

    /**
     * Get the individual scores for this evaluation response.
     */
    public function scores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class, 'evaluation_response_id');
    }
}
