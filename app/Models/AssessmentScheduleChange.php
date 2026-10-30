<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['assessment_id', 'tanggal_lama', 'tanggal_baru', 'alasan', 'diubah_oleh'])]
class AssessmentScheduleChange extends Model
{
    protected function casts(): array
    {
        return ['tanggal_lama' => 'date', 'tanggal_baru' => 'date'];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diubah_oleh');
    }
}