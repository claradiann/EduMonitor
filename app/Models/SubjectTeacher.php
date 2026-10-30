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

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function evaluationResponses(): HasMany
    {
        return $this->hasMany(EvaluationResponse::class, 'subject_teacher_id');
    }

    /** Penilaian (tugas, kuis, UH, UTS, UAS) untuk kelas-mapel ini. */
    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'subject_teacher_id')->orderBy('tanggal');
    }

    /** Label singkat, misalnya "Matematika · VII-A". */
    public function label(): string
    {
        return $this->subject->nama_mapel . ' · ' . $this->kelas->nama_kelas;
    }

    public function incidentReports(): HasMany
    {
        return $this->hasMany(IncidentReport::class, 'subject_teacher_id');
    }

    /** Siswa di kelas ini. */
    public function students()
    {
        return User::where('role', 'siswa')->where('kelas_id', $this->kelas_id)->orderBy('name')->get();
    }
}