<?php

namespace App\Http\Livewire\HazardHunt;

use App\Models\HazardQuestion;
use App\Models\HazardSession;
use App\Models\Participant;
use App\Models\ScoreAttempt;
use App\Services\ScoringService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class HazardPlayPage extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $questions = [];

    public int $currentIndex = 0;

    /** @var array<int, array<string, mixed>> */
    public ?array $answers = [];

    public int $score = 0;

    public int $startedAt;

    public int $elapsedSeconds = 0;

    public bool $finished = false;

    /** @var array<string, mixed>|null */
    public ?array $result = null;

    public string $scorecardId = '';

    public string $playerName = '';

    public ?int $sessionId = null;

    public string $error = '';

    public function mount(): void
    {
        $this->scorecardId = session('hazard_scorecard_id', '');
        $this->playerName = session('hazard_player_name', $this->scorecardId);

        if ($this->scorecardId === '' || $this->scorecardId === '0') {
            $this->redirect(route('hazard.start'));

            return;
        }

        $questions = HazardQuestion::inRandomOrder()->take(5)->get();
        $this->questions = $questions->toArray();
        if ($this->questions === []) {
            $this->error = 'No questions available. Please contact the event administrator.';
        }
        $this->startedAt = now()->timestamp;
    }

    public function answerQuestion(int $selectedIndex): void
    {
        if (isset($this->answers[$this->currentIndex])) {
            return;
        }

        $question = $this->questions[$this->currentIndex];
        $isCorrect = $selectedIndex === $question['answer'];
        if ($isCorrect) {
            $this->score++;
        }

        $this->answers[$this->currentIndex] = [
            'selectedIndex' => $selectedIndex,
            'isCorrect' => $isCorrect,
        ];
    }

    public function nextQuestion(): void
    {
        if ($this->currentIndex >= count($this->questions) - 1) {
            $this->finishGame();

            return;
        }
        $this->currentIndex++;
    }

    public function finishGame(): void
    {
        $this->finished = true;
        $this->elapsedSeconds = max(1, now()->timestamp - $this->startedAt);

        $result = DB::transaction(function () {
            $scoring = app(ScoringService::class);
            $result = $scoring->scoreHazard($this->score, $this->elapsedSeconds);

            $participant = Participant::firstOrCreate(
                ['scorecard_id' => $this->scorecardId],
                ['name' => $this->playerName]
            );

            $session = HazardSession::create([
                'participant_id' => $participant->id,
                'scorecard_id' => $this->scorecardId,
                'status' => 'completed',
                'correct_count' => $this->score,
                'elapsed_seconds' => $this->elapsedSeconds,
                'score' => $result['score'],
                'answers' => $this->answers,
                'questions' => $this->questions,
                'started_at' => now()->subSeconds($this->elapsedSeconds),
                'completed_at' => now(),
            ]);

            $this->sessionId = $session->id;

            ScoreAttempt::create([
                'participant_id' => $participant->id,
                'game_code' => 'hazard_hunt_ride',
                'raw_result' => $result['rawResult'],
                'calculated_score' => $result['score'],
                'source' => 'hazard_hunt',
                'status' => 'approved',
                'breakdown' => array_merge($result['breakdown'], [
                    'questionIds' => array_column($this->questions, 'scene_id'),
                    'answers' => $this->answers,
                    'elapsedSeconds' => $this->elapsedSeconds,
                ]),
            ]);

            return $result;
        });

        $this->result = $result;
    }

    public function playAgain(): void
    {
        session()->forget('hazard_scorecard_id');
        $this->redirect(route('hazard.start'));
    }

    public function retryChallenge(): void
    {
        $this->currentIndex = 0;
        $this->answers = [];
        $this->score = 0;
        $this->finished = false;
        $this->result = null;
        $this->startedAt = now()->timestamp;

        $questions = HazardQuestion::inRandomOrder()->take(5)->get();
        $this->questions = $questions->toArray();
    }

    public function render(): View
    {
        return view('livewire.hazard-hunt.play-page', [
            'currentQuestion' => $this->questions[$this->currentIndex] ?? null,
            'questionCount' => count($this->questions),
        ])->layout('components.layouts.app');
    }
}
