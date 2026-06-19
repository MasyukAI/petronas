<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class HazardSession extends Model
{
    protected $fillable = [
        'participant_id',
        'scorecard_id',
        'status',
        'correct_count',
        'elapsed_seconds',
        'score',
        'answers',
        'questions',
        'started_at',
        'completed_at',
    ];

    #[Override]
    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'questions' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Participant, $this> */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
