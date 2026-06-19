<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class QuizPlayerNextReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $roundId,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('quiz-round.'.$this->roundId)];
    }

    public function broadcastAs(): string
    {
        return 'quiz.player.next-ready';
    }
}
