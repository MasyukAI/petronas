<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class QuizPhaseChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $roundId,
        public string $phase,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('quiz-round.'.$this->roundId)];
    }

    public function broadcastAs(): string
    {
        return 'quiz.phase.changed';
    }
}
