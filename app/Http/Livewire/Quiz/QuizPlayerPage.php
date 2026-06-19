<?php

namespace App\Http\Livewire\Quiz;

use App\Events\QuizPlayerAnswered;
use App\Events\QuizPlayerNextReady;
use App\Models\QuizPlayer;
use App\Models\QuizRound;
use Carbon\Carbon;
use Exception;
use Illuminate\View\View;
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
        if ($this->token === '' || $this->token === '0') {
            return;
        }

        $this->player = QuizPlayer::where('player_token', $this->token)->first();
        $this->round = $this->player instanceof QuizPlayer ? $this->player->round : null;
    }

    #[On('quiz-poll')]
    public function pollTick(): void
    {
        if ($this->round instanceof QuizRound) {
            try {
                $this->round->refresh();
            } catch (Exception) {
                $this->round = null;
            }
        }
        if ($this->player instanceof QuizPlayer) {
            try {
                /** @var Carbon|null $heartbeat */
                $heartbeat = $this->player->last_heartbeat;
                if (is_null($heartbeat) || $heartbeat->lte(now()->subSeconds(5))) {
                    $this->player->update(['last_heartbeat' => now()]);
                }
                $this->player->refresh();
            } catch (Exception) {
                // transient DB error — keep player reference
            }
        }
    }

    public function markReady(): void
    {
        if (! $this->player instanceof QuizPlayer) {
            return;
        }
        $this->player->update(['ready' => true]);
        $this->player->refresh();
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

        /** @var array<int|string, mixed> $answers */
        $answers = $this->player->answers;
        if (isset($answers[(string) $questionIndex])) {
            return;
        }

        /** @var array<int, array{answerIndex: int}> $questions */
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

        $correctCount = count(array_filter($answers, fn (array $a) => $a['correct']));
        $score = (int) round(($correctCount / max(1, $this->round->question_count)) * 100);
        $answeredCount = count($answers);

        $this->player->update([
            'answers' => $answers,
            'score' => $score,
            'correct_count' => $correctCount,
            'answered_count' => $answeredCount,
        ]);

        $this->player->refresh();
        QuizPlayerAnswered::dispatch($this->round->id);
    }

    public function markNextReady(): void
    {
        if (! $this->player instanceof QuizPlayer) {
            return;
        }
        $this->player->update(['next_ready' => true]);
        $this->player->refresh();
        if ($this->round instanceof QuizRound) {
            QuizPlayerNextReady::dispatch($this->round->id);
        }
    }

    public function render(): View
    {
        $phase = [
            'name' => $this->round?->phase_name ?? 'lobby',
            'questionIndex' => $this->round?->current_question,
        ];

        $ownAnswer = null;
        if ($this->round && $this->player instanceof QuizPlayer) {
            /** @var array<int|string, mixed> $playerAnswers */
            $playerAnswers = $this->player->answers;
            $ownAnswer = $playerAnswers[(string) $this->round->current_question] ?? null;
        }

        return view('livewire.quiz.player-page', [
            'phase' => $phase,
            'currentQuestion' => $this->round?->questions[$this->round?->current_question] ?? null,
            'ownAnswer' => $ownAnswer,
            'players' => $this->round?->players ?? collect(),
        ])->layout('components.layouts.app');
    }
}
