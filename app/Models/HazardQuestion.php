<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

class HazardQuestion extends Model
{
    protected $fillable = [
        'scene_id',
        'title',
        'image',
        'source',
        'question',
        'options',
        'answer',
        'explanation',
    ];

    #[Override]
    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }
}
