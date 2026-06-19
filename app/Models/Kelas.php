<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama_kelas'])]
class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    /**
     * Get the students in this class.
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'kelas_id')->where('role', 'siswa');
    }

    /**
     * Get the subject teachers for this class.
     */
    public function subjectTeachers(): HasMany
    {
        return $this->hasMany(SubjectTeacher::class, 'kelas_id');
    }
}
