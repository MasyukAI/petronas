<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizPlayer extends Model
{
    protected $fillable = [
        'quiz_round_id',
        'participant_id',
        'scorecard_id',
        'name',
        'ready',
        'next_ready',
        'answers',
        'score',
        'correct_count',
        'answered_count',
        'player_token',
        'last_heartbeat',
    ];

    protected function casts(): array
    {
        return [
            'ready' => 'boolean',
            'next_ready' => 'boolean',
            'answers' => 'array',
            'last_heartbeat' => 'datetime',
        ];
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(QuizRound::class, 'quiz_round_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
