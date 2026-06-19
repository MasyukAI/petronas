<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    protected $fillable = [
        'scorecard_id',
        'name',
        'phone',
        'email',
    ];

    /** @return HasMany<ScoreAttempt, $this> */
    public function scoreAttempts(): HasMany
    {
        return $this->hasMany(ScoreAttempt::class);
    }

    /** @return HasMany<HazardSession, $this> */
    public function hazardSessions(): HasMany
    {
        return $this->hasMany(HazardSession::class);
    }
}
