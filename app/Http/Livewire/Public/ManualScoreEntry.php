<?php

namespace App\Http\Livewire\Public;

use App\Models\Participant;
use App\Models\ScoreAttempt;
use App\Services\ScoringService;
use Livewire\Component;

class ManualScoreEntry extends Component
{
    public string $scorecardId = '';

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $hazardMark = '';

    public string $hazardTime = '';

    public string $hazardMark2 = '';

    public string $hazardTime2 = '';

    public string $reactionMark = '';

    public string $reactionMark2 = '';

    public string $quickfireMark = '';

    public string $quickfireMark2 = '';

    public string $pipeTime = '';

    public string $pipeTime2 = '';

    public array $previewScores = [null, null, null, null];

    public int $totalPreview = 0;

    public string $statusMessage = '';

    public string $pin = '';

    public string $saveNotice = '';

    public string $saveNoticeTone = '';

    public bool $showCelebration = false;

    public string $celebrationName = '';

    public string $celebrationScore = '';

    protected function rules(): array
    {
        return [
            'scorecardId' => 'required|string|max:40',
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:160',
        ];
    }

    protected $messages = [
        'email.required' => 'Participant email is required so winners can be contacted.',
        'email.email' => 'Enter a valid email address.',
    ];

    public function updated($field): void
    {
        $this->calculatePreview();
    }

    public function calculatePreview(): void
    {
        $scoring = app(ScoringService::class);

        $scores = [];

        $hazardCorrect = $this->parseFraction($this->hazardMark ?: $this->hazardMark2, 5);
        $hazardSeconds = $this->parseSeconds($this->hazardTime ?: $this->hazardTime2);
        $scores[] = $hazardCorrect !== null ? $scoring->scoreHazard($hazardCorrect, $hazardSeconds)['score'] : null;

        $reaction = $this->parseFraction($this->reactionMark ?: $this->reactionMark2, 10);
        $scores[] = $reaction !== null ? $scoring->scoreReactionRisk($reaction) : null;

        $quick = $this->parseFraction($this->quickfireMark ?: $this->quickfireMark2, 10);
        $scores[] = $quick !== null ? $scoring->scoreQuickfire($quick) : null;

        $pipeSeconds = $this->parseSeconds($this->pipeTime ?: $this->pipeTime2);
        $pipeCompleted = $pipeSeconds !== null || ! empty($this->pipeTime) || ! empty($this->pipeTime2);
        $scores[] = $pipeCompleted ? $scoring->scorePipeFit(true, $pipeSeconds) : null;

        $this->previewScores = $scores;
        $this->totalPreview = (int) array_sum(array_filter($scores, fn ($s) => $s !== null));
    }

    public function save(): void
    {
        $this->validate();
        $scoring = app(ScoringService::class);

        $participant = Participant::firstOrCreate(
            ['scorecard_id' => strtoupper($this->scorecardId)],
            [
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
            ]
        );

        if ($participant->wasRecentlyCreated === false) {
            $participant->update([
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
            ]);
        }

        $attempts = [];

        $hazardCorrect = $this->parseFraction($this->hazardMark, 5);
        $hazardSeconds = $this->parseSeconds($this->hazardTime);
        if ($hazardCorrect !== null) {
            $result = $scoring->scoreHazard($hazardCorrect, $hazardSeconds);
            $attempts[] = ['game_code' => 'hazard_hunt_ride', 'raw_result' => $result['rawResult'], 'score' => $result['score'], 'breakdown' => $result['breakdown']];
        }

        $hazardCorrect2 = $this->parseFraction($this->hazardMark2, 5);
        $hazardSeconds2 = $this->parseSeconds($this->hazardTime2);
        if ($hazardCorrect2 !== null) {
            $result = $scoring->scoreHazard($hazardCorrect2, $hazardSeconds2);
            $attempts[] = ['game_code' => 'hazard_hunt_ride', 'raw_result' => $result['rawResult'], 'score' => $result['score'], 'breakdown' => $result['breakdown']];
        }

        $reaction = $this->parseFraction($this->reactionMark, 10);
        if ($reaction !== null) {
            $score = $scoring->scoreReactionRisk($reaction);
            $attempts[] = ['game_code' => 'reaction_risk', 'raw_result' => "{$reaction}/10 sticks", 'score' => $score, 'breakdown' => []];
        }

        $reaction2 = $this->parseFraction($this->reactionMark2, 10);
        if ($reaction2 !== null) {
            $score = $scoring->scoreReactionRisk($reaction2);
            $attempts[] = ['game_code' => 'reaction_risk', 'raw_result' => "{$reaction2}/10 sticks", 'score' => $score, 'breakdown' => []];
        }

        $quick = $this->parseFraction($this->quickfireMark, 10);
        if ($quick !== null) {
            $score = $scoring->scoreQuickfire($quick);
            $attempts[] = ['game_code' => 'quickfire_quiz', 'raw_result' => "{$quick}/10", 'score' => $score, 'breakdown' => []];
        }

        $quick2 = $this->parseFraction($this->quickfireMark2, 10);
        if ($quick2 !== null) {
            $score = $scoring->scoreQuickfire($quick2);
            $attempts[] = ['game_code' => 'quickfire_quiz', 'raw_result' => "{$quick2}/10", 'score' => $score, 'breakdown' => []];
        }

        $pipeSeconds = $this->parseSeconds($this->pipeTime);
        $pipeFailed = $this->isPipeFitFail($this->pipeTime);
        if (! $pipeFailed && ($pipeSeconds !== null || ! empty($this->pipeTime))) {
            $completed = ! preg_match('/fail|incomplete|incorrect/i', $this->pipeTime);
            $score = $scoring->scorePipeFit($completed, $pipeSeconds);
            $rawResult = $completed ? 'pass + '.($pipeSeconds ?? '0').' sec' : 'fail';
            $attempts[] = ['game_code' => 'pipe_fit_challenge', 'raw_result' => $rawResult, 'score' => $score, 'breakdown' => []];
        }

        $pipeSeconds2 = $this->parseSeconds($this->pipeTime2);
        $pipeFailed2 = $this->isPipeFitFail($this->pipeTime2);
        if (! $pipeFailed2 && ($pipeSeconds2 !== null || ! empty($this->pipeTime2))) {
            $completed2 = ! preg_match('/fail|incomplete|incorrect/i', $this->pipeTime2);
            $score = $scoring->scorePipeFit($completed2, $pipeSeconds2);
            $rawResult = $completed2 ? 'pass + '.($pipeSeconds2 ?? '0').' sec' : 'fail';
            $attempts[] = ['game_code' => 'pipe_fit_challenge', 'raw_result' => $rawResult, 'score' => $score, 'breakdown' => []];
        }

        foreach ($attempts as $att) {
            ScoreAttempt::create([
                'participant_id' => $participant->id,
                'game_code' => $att['game_code'],
                'raw_result' => $att['raw_result'],
                'calculated_score' => $att['score'],
                'source' => 'manual',
                'status' => 'approved',
                'breakdown' => $att['breakdown'],
            ]);
        }

        $completedGamesCount = count(array_unique(array_column($attempts, 'game_code')));
        $this->saveNotice = "{$this->name} saved. Total {$this->totalPreview}/400. {$completedGamesCount}/4 games recorded.";
        $this->saveNoticeTone = 'success';

        $this->statusMessage = 'Score saved! '.count($attempts).' attempt(s) recorded.';

        if ($this->isCurrentLeader($this->scorecardId)) {
            $this->celebrationName = $this->name;
            $this->celebrationScore = "Total {$this->totalPreview}/400";
            $this->showCelebration = true;
        }

        $this->reset(['hazardMark', 'hazardTime', 'hazardMark2', 'hazardTime2', 'reactionMark', 'reactionMark2', 'quickfireMark', 'quickfireMark2', 'pipeTime', 'pipeTime2']);
        $this->calculatePreview();
        $this->dispatch('score-saved');
    }

    private function isCurrentLeader(string $scorecardId): bool
    {
        $leaderboard = app(LeaderboardPage::class);
        $leaderboard->loadLeaderboard();
        $entries = $leaderboard->entries;
        if (empty($entries)) {
            return false;
        }

        return ($entries[0]['scorecardId'] ?? '') === strtoupper($scorecardId);
    }

    public function dismissCelebration(): void
    {
        $this->showCelebration = false;
    }

    private function parseFraction(?string $raw, int $fallbackMax): ?int
    {
        if (empty($raw)) {
            return null;
        }
        if (preg_match('/(\d{1,3})\s*\/\s*(\d{1,3})/', $raw, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/\b(\d{1,3})\b/', $raw, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function isPipeFitFail(?string $raw): bool
    {
        if (empty($raw)) {
            return false;
        }

        return (bool) preg_match('/\b(fail|incomplete|incorrect)\b/i', $raw);
    }

    private function parseSeconds(?string $raw): ?int
    {
        if (empty($raw)) {
            return null;
        }
        if (preg_match('/(\d{1,4})\s*(?:sec|second|seconds|s)\b/i', $raw, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/\+?\s*(\d{1,4})\b/', $raw, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    public function render()
    {
        return view('livewire.public.manual-score-entry')
            ->layout('components.layouts.app');
    }
}
