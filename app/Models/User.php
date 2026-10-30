<?php

namespace App\Models;

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

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'report_read_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /** Untuk orang tua: seluruh anak yang dipantau (bisa lebih dari satu, misalnya kakak-adik). */
    public function children()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id')->orderBy('name');
    }

    /** Untuk siswa: orang tua/wali. */
    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id');
    }

    /** Untuk orang tua: anak yang sedang dipilih di tampilan (disimpan di sesi). */
    public function anakAktif(): ?User
    {
        $children = $this->children;
        if ($children->isEmpty()) {
            return null;
        }

        return $children->firstWhere('id', session('anak_id')) ?? $children->first();
    }

    /** Siswa yang datanya sedang dilihat: diri sendiri untuk siswa, anak aktif untuk orang tua. */
    public function siswaDipantau(): ?User
    {
        return match ($this->role) {
            'siswa' => $this,
            'orang_tua' => $this->anakAktif(),
            default => null,
        };
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class)->latest();
    }

    public function subjectTeachers()
    {
        return $this->hasMany(SubjectTeacher::class, 'teacher_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'student_id');
    }

    public function studentNotes()
    {
        return $this->hasMany(TeacherNote::class, 'student_id');
    }

    public function writtenNotes()
    {
        return $this->hasMany(TeacherNote::class, 'teacher_id');
    }

    public function evaluationResponses()
    {
        return $this->hasMany(EvaluationResponse::class, 'student_id');
    }
}