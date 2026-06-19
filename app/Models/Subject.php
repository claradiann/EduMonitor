<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama_mapel', 'kode_mapel'])]
class Subject extends Model
{
    use HasFactory;

    /**
     * Get the subject teachers.
     */
    public function subjectTeachers(): HasMany
    {
        return $this->hasMany(SubjectTeacher::class, 'subject_id');
    }

    /**
     * Get the grades for this subject.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'subject_id');
    }

    /**
     * Get the teacher notes for this subject.
     */
    public function teacherNotes(): HasMany
    {
        return $this->hasMany(TeacherNote::class, 'subject_id');
    }
}
