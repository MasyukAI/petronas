<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoreAttempt extends Model
{
    protected $fillable = [
        'participant_id',
        'game_code',
        'raw_result',
        'calculated_score',
        'source',
        'status',
        'breakdown',
    ];

    protected function casts(): array
    {
        return [
            'breakdown' => 'array',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
