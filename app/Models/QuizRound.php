<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected function casts(): array
    {
        return [
            'questions' => 'array',
            'timings' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    public function players(): HasMany
    {
        return $this->hasMany(QuizPlayer::class);
    }
}
