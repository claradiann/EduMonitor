<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'subject_teacher_id', 'tanggal', 'jenis', 'keterangan'])]
class IncidentReport extends Model
{
    public const JENIS = [
        'jam_kosong' => 'Jam kelas kosong tanpa keterangan',
        'terlambat' => 'Guru datang terlambat',
        'lainnya' => 'Lainnya',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function subjectTeacher(): BelongsTo
    {
        return $this->belongsTo(SubjectTeacher::class, 'subject_teacher_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function jenisLabel(): string
    {
        return self::JENIS[$this->jenis] ?? $this->jenis;
    }
}
