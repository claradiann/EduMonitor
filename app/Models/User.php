<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'username', 'role', 'nisn', 'nip', 'kelas_id', 'parent_of_id', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the class the student belongs to.
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * For parents: get the student they monitor.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'parent_of_id');
    }

    /**
     * For students: get the parent of this student.
     */
    public function parent()
    {
        return $this->hasOne(User::class, 'parent_of_id');
    }

    /**
     * For teachers: get the classes and subjects taught.
     */
    public function subjectTeachers()
    {
        return $this->hasMany(SubjectTeacher::class, 'teacher_id');
    }

    /**
     * For students: get grades.
     */
    public function grades()
    {
        return $this->hasMany(Grade::class, 'student_id');
    }

    /**
     * For students: get notes from teachers.
     */
    public function studentNotes()
    {
        return $this->hasMany(TeacherNote::class, 'student_id');
    }

    /**
     * For teachers: get notes written for students.
     */
    public function writtenNotes()
    {
        return $this->hasMany(TeacherNote::class, 'teacher_id');
    }

    /**
     * For students: get kuesioner evaluation responses.
     */
    public function evaluationResponses()
    {
        return $this->hasMany(EvaluationResponse::class, 'student_id');
    }
}
