<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['teacher_id', 'subject_id', 'kelas_id'])]
class SubjectTeacher extends Model
{
    use HasFactory;

    protected $table = 'subject_teacher';

    /**
     * Get the teacher.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the subject.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Get the class.
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Get the evaluation responses for this subject-teacher mapping.
     */
    public function evaluationResponses(): HasMany
    {
        return $this->hasMany(EvaluationResponse::class, 'subject_teacher_id');
    }
}
