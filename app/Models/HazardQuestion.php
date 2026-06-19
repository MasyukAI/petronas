<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }
}
