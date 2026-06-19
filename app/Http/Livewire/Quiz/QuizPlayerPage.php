<?php

namespace App\Http\Livewire\Quiz;

use App\Events\QuizPlayerAnswered;
use App\Events\QuizPlayerNextReady;
use App\Events\QuizRoundStarted;
use App\Models\QuizPlayer;
use App\Models\QuizRound;
use Livewire\Attributes\On;
use Livewire\Component;

class QuizPlayerPage extends Component
{
    public ?QuizRound $round = null;

    public string $token = '';

    public ?QuizPlayer $player = null;

    public string $statusMessage = '';

    public function mount(): void
    {
        $this->token = session('quiz_player_token', '');
        if (! $this->token) {
            return;
        }

        $this->player = QuizPlayer::where('player_token', $this->token)->first();
        $this->round = $this->player?->round;
    }

    #[On('quiz-poll')]
    public function pollTick(): void
    {
        if ($this->round) {
            try {
                $this->round->refresh();
            } catch (\Exception $e) {
                $this->round = null;
            }
        }
        if ($this->player) {
            try {
                $this->player->update(['last_heartbeat' => now()]);
                $this->player->refresh();
            } catch (\Exception $e) {
                $this->player = null;
            }
        }
    }

    public function markReady(): void
    {
        if (! $this->player) {
            return;
        }
        $this->player->update(['ready' => true]);
        $this->player->refresh();
        if ($this->round) {
            QuizRoundStarted::dispatch($this->round->id);
        }
    }

    public function submitAnswer(int $questionIndex, int $answerIndex): void
    {
        if (! $this->round || ! $this->player) {
            return;
        }
        if ($this->round->phase_name !== 'question') {
            return;
        }
        if ($this->round->current_question !== $questionIndex) {
            return;
        }

        $answers = $this->player->answers ?? [];
        if (isset($answers[(string) $questionIndex])) {
            return;
        }

        $questions = $this->round->questions;
        $question = $questions[$questionIndex] ?? null;
        if (! $question) {
            return;
        }

        $correct = $answerIndex === $question['answerIndex'];
        $answers[(string) $questionIndex] = [
            'answerIndex' => $answerIndex,
            'correct' => $correct,
            'points' => $correct ? 1 : 0,
        ];

        $correctCount = count(array_filter($answers, fn ($a) => $a['correct']));
        $score = (int) round(($correctCount / max(1, $this->round->question_count)) * 100);
        $answeredCount = count($answers);

        $this->player->update([
            'answers' => $answers,
            'score' => $score,
            'correct_count' => $correctCount,
            'answered_count' => $answeredCount,
        ]);

        $this->player->refresh();
        if ($this->round) {
            QuizPlayerAnswered::dispatch($this->round->id);
        }
    }

    public function markNextReady(): void
    {
        if (! $this->player) {
            return;
        }
        $this->player->update(['next_ready' => true]);
        $this->player->refresh();
        if ($this->round) {
            QuizPlayerNextReady::dispatch($this->round->id);
        }
    }

    public function render()
    {
        $phase = [
            'name' => $this->round?->phase_name ?? 'lobby',
            'questionIndex' => $this->round?->current_question,
        ];

        return view('livewire.quiz.player-page', [
            'phase' => $phase,
            'currentQuestion' => $this->round?->questions[$this->round?->current_question] ?? null,
            'ownAnswer' => $this->player?->answers[(string) ($this->round?->current_question)] ?? null,
            'players' => $this->round?->players ?? collect(),
        ])->layout('components.layouts.app');
    }
}
