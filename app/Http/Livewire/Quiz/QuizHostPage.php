<?php

namespace App\Http\Livewire\Quiz;

use App\Events\QuizPhaseChanged;
use App\Events\QuizRoundReset;
use App\Events\QuizRoundStarted;
use App\Models\Participant;
use App\Models\QuizPlayer;
use App\Models\QuizRound;
use App\Models\ScoreAttempt;
use App\Services\ScoringService;
use Database\Seeders\QuizQuestionBankSeeder;
use Livewire\Attributes\On;
use Livewire\Component;

class QuizHostPage extends Component
{
    public ?QuizRound $round = null;

    public string $joinUrl = '';

    public string $statusMessage = '';

    public function mount(): void
    {
        $this->createRound();
    }

    public function createRound(): void
    {
        $code = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));

        $questions = QuizQuestionBankSeeder::getRandomQuestions(10);

        $this->round = QuizRound::create([
            'code' => $code,
            'status' => 'waiting',
            'phase_name' => 'lobby',
            'current_question' => null,
            'questions' => $questions,
            'question_count' => count($questions),
            'timings' => [
                'countdownMs' => 3000,
            ],
            'expires_at' => now()->addHour(),
        ]);

        $this->joinUrl = url('/quickfire/join/'.$code);
        $this->statusMessage = 'Round created. Share the code: '.$code;
    }

    public function startRound(): void
    {
        if (! $this->round) {
            return;
        }

        $this->round->update([
            'status' => 'countdown',
            'phase_name' => 'countdown',
            'current_question' => 0,
        ]);

        $this->round->refresh();
        QuizRoundStarted::dispatch($this->round->id);
    }

    #[On('quiz-poll')]
    public function pollTick(): void
    {
        if (! $this->round) {
            return;
        }
        $this->round->refresh();

        if ($this->round->phase_name === 'countdown') {
            $this->round->update([
                'status' => 'question',
                'phase_name' => 'question',
                'current_question' => 0,
            ]);
            $this->round->refresh();

            return;
        }

        if ($this->round->phase_name === 'question') {
            $this->checkAllAnswered();

            return;
        }

        if ($this->round->phase_name === 'review') {
            $this->checkAllNextReady();

            return;
        }
    }

    private function checkAllAnswered(): void
    {
        $activePlayers = $this->activePlayers();
        if ($activePlayers->isEmpty()) {
            return;
        }

        $allAnswered = $activePlayers->every(fn ($p) => ($p->answered_count ?? 0) > ($this->round->current_question ?? -1)
        );

        if ($allAnswered) {
            $affected = QuizRound::where('id', $this->round->id)
                ->where('phase_name', 'question')
                ->update(['status' => 'review', 'phase_name' => 'review']);

            if ($affected > 0) {
                $this->round->refresh();
                QuizPhaseChanged::dispatch($this->round->id, 'review');
            }
        }
    }

    private function checkAllNextReady(): void
    {
        $activePlayers = $this->activePlayers();
        if ($activePlayers->isEmpty()) {
            return;
        }

        $allReady = $activePlayers->every(fn ($p) => $p->next_ready);

        if ($allReady) {
            $next = ($this->round->current_question ?? 0) + 1;

            if ($next >= $this->round->question_count) {
                $affected = QuizRound::where('id', $this->round->id)
                    ->where('phase_name', 'review')
                    ->update(['status' => 'result', 'phase_name' => 'result']);

                if ($affected > 0) {
                    $this->round->refresh();
                    $this->saveQuizResults();
                    $this->statusMessage = 'Quiz complete. Scores saved to leaderboard.';
                    QuizPhaseChanged::dispatch($this->round->id, 'result');
                }
            } else {
                $affected = QuizRound::where('id', $this->round->id)
                    ->where('phase_name', 'review')
                    ->update([
                        'status' => 'question',
                        'phase_name' => 'question',
                        'current_question' => $next,
                    ]);

                if ($affected > 0) {
                    QuizPlayer::where('quiz_round_id', $this->round->id)
                        ->update(['next_ready' => false]);
                    $this->round->refresh();
                    QuizPhaseChanged::dispatch($this->round->id, 'question');
                }
            }
        }
    }

    private function activePlayers()
    {
        return $this->round->players->filter(fn ($p) => is_null($p->last_heartbeat) || $p->last_heartbeat->gt(now()->subSeconds(10))
        );
    }

    public function advanceToNext(): void
    {
        if (! $this->round) {
            return;
        }
        if (! in_array($this->round->phase_name, ['review', 'question'])) {
            return;
        }

        $next = ($this->round->current_question ?? 0) + 1;

        if ($next >= $this->round->question_count) {
            $this->round->update([
                'status' => 'result',
                'phase_name' => 'result',
            ]);

            $this->saveQuizResults();
            $this->statusMessage = 'Quiz complete. Scores saved to leaderboard.';
        } else {
            $this->round->update([
                'status' => 'question',
                'phase_name' => 'question',
                'current_question' => $next,
            ]);
            QuizPlayer::where('quiz_round_id', $this->round->id)
                ->update(['next_ready' => false]);
        }

        $this->round->refresh();
        QuizPhaseChanged::dispatch($this->round->id, $this->round->phase_name);
    }

    private function saveQuizResults(): void
    {
        if (! $this->round) {
            return;
        }

        $scoringService = app(ScoringService::class);

        foreach ($this->round->players as $player) {
            $correctCount = (int) $player->correct_count;
            $result = $scoringService->scoreQuickfire($correctCount, $this->round->question_count);

            $participant = Participant::firstOrCreate(
                ['scorecard_id' => $player->scorecard_id],
                ['name' => $player->name]
            );

            ScoreAttempt::create([
                'participant_id' => $participant->id,
                'game_code' => 'quickfire_quiz',
                'raw_result' => "{$correctCount}/{$this->round->question_count}",
                'calculated_score' => $result,
                'source' => 'quickfire',
                'status' => 'approved',
                'breakdown' => [
                    'correct' => $correctCount,
                    'total' => $this->round->question_count,
                    'player_answers' => $player->answers,
                ],
            ]);
        }
    }

    public function resetRound(): void
    {
        if ($this->round) {
            $roundId = $this->round->id;
            $this->round->players()->delete();
            $this->round->delete();
            QuizRoundReset::dispatch($roundId);
        }
        $this->round = null;
        $this->joinUrl = '';
        $this->statusMessage = 'Round reset.';
    }

    public function render()
    {
        $players = $this->round ? $this->round->players()->get() : collect();
        $playerCount = $players->count();

        return view('livewire.quiz.host-page', [
            'players' => $players,
            'playerCount' => $playerCount,
            'questions' => $this->round?->questions ?? [],
        ])->layout('components.layouts.app');
    }
}
