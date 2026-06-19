<?php

namespace App\Http\Livewire\Public;

use App\Models\Participant;
use App\Models\ScoreAttempt;
use App\Services\ScoringService;
use Livewire\Component;

class LeaderboardPage extends Component
{
    public array $entries = [];

    public string $status = 'Loading scores...';

    public string $lastUpdated = '';

    public bool $showEntryForm = false;

    public string $scorecardId = '';

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $pin = '';

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

    public function mount(): void
    {
        $this->loadLeaderboard();
        if (request()->query('entry') === '1') {
            $this->showEntryForm = true;
        }
    }

    public function pollRefresh(): void
    {
        $this->loadLeaderboard();
    }

    public function loadLeaderboard(): void
    {
        $participants = Participant::with('scoreAttempts')->get();
        $gameCodes = ['hazard_hunt_ride', 'reaction_risk', 'quickfire_quiz', 'pipe_fit_challenge'];

        $entries = $participants->map(function ($participant) use ($gameCodes) {
            $scores = [];
            $attempts = [];

            foreach ($gameCodes as $code) {
                $best = $participant->scoreAttempts
                    ->where('game_code', $code)
                    ->where('status', 'approved')
                    ->sortByDesc('calculated_score')
                    ->first();

                $scores[] = $best ? $best->calculated_score : null;
                $attempts[] = $participant->scoreAttempts->where('game_code', $code)->count();
            }

            $completed = count(array_filter($scores, fn ($s) => $s !== null));
            $total = array_sum(array_filter($scores, fn ($s) => $s !== null));

            return [
                'scorecardId' => $participant->scorecard_id,
                'name' => $participant->name,
                'scores' => $scores,
                'attempts' => $attempts,
                'completedGames' => $completed,
                'total' => $total,
            ];
        });

        $this->entries = $entries->sortByDesc('total')->values()->toArray();
        $this->status = $this->entries ? 'Live scores loaded.' : 'No scores yet.';
        $this->lastUpdated = now()->format('H:i:s');
    }

    public function updated($field): void
    {
        if (in_array($field, ['hazardMark', 'hazardTime', 'hazardMark2', 'hazardTime2', 'reactionMark', 'reactionMark2', 'quickfireMark', 'quickfireMark2', 'pipeTime', 'pipeTime2'])) {
            $this->calculatePreview();
        }
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

        $this->saveNotice = '';
        $this->saveNoticeTone = 'pending';

        $participant = Participant::firstOrCreate(
            ['scorecard_id' => strtoupper($this->scorecardId)],
            ['name' => $this->name, 'phone' => $this->phone, 'email' => $this->email]
        );

        if ($participant->wasRecentlyCreated === false) {
            $participant->update(['name' => $this->name, 'phone' => $this->phone, 'email' => $this->email]);
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

        try {
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

            $this->resetEntryForm();
            $this->calculatePreview();
            $this->loadLeaderboard();
            $this->dispatch('score-saved');
        } catch (\Exception $e) {
            $this->saveNotice = 'Failed to save score. Please try again.';
            $this->saveNoticeTone = 'error';
        }
    }

    private function isCurrentLeader(string $scorecardId): bool
    {
        if (empty($this->entries)) {
            return false;
        }

        return ($this->entries[0]['scorecardId'] ?? '') === strtoupper($scorecardId);
    }

    public function dismissCelebration(): void
    {
        $this->showCelebration = false;
    }

    private function resetEntryForm(): void
    {
        $this->reset(['hazardMark', 'hazardTime', 'hazardMark2', 'hazardTime2', 'reactionMark', 'reactionMark2', 'quickfireMark', 'quickfireMark2', 'pipeTime', 'pipeTime2']);
        $this->previewScores = [null, null, null, null];
        $this->totalPreview = 0;
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
        return view('livewire.public.leaderboard-page')
            ->layout('components.layouts.app');
    }
}
