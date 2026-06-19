<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['indicator_name', 'description'])]
class EvaluationIndicator extends Model
{
    use HasFactory;

    /**
     * Get the scores for this indicator.
     */
    public function scores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class, 'indicator_id');
    }
}
