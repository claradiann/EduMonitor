<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['evaluation_response_id', 'indicator_id', 'score'])]
class EvaluationScore extends Model
{
    use HasFactory;

    /**
     * Get the evaluation response.
     */
    public function response(): BelongsTo
    {
        return $this->belongsTo(EvaluationResponse::class, 'evaluation_response_id');
    }

    /**
     * Get the indicator.
     */
    public function indicator(): BelongsTo
    {
        return $this->belongsTo(EvaluationIndicator::class, 'indicator_id');
    }
}
