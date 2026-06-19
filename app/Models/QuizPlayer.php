<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

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

    #[Override]
    protected function casts(): array
    {
        return [
            'ready' => 'boolean',
            'next_ready' => 'boolean',
            'answers' => 'array',
            'last_heartbeat' => 'datetime',
        ];
    }

    /** @return BelongsTo<QuizRound, $this> */
    public function round(): BelongsTo
    {
        return $this->belongsTo(QuizRound::class, 'quiz_round_id');
    }

    /** @return BelongsTo<Participant, $this> */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
