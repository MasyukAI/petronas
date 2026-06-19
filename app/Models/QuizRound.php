<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

class QuizRound extends Model
{
    protected $fillable = [
        'code',
        'status',
        'phase_name',
        'current_question',
        'questions',
        'question_count',
        'timings',
        'expires_at',
    ];

    #[Override]
    protected function casts(): array
    {
        return [
            'questions' => 'array',
            'timings' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    /** @return HasMany<QuizPlayer, $this> */
    public function players(): HasMany
    {
        return $this->hasMany(QuizPlayer::class);
    }
}
