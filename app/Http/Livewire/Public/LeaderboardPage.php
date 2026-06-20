<?php

namespace App\Http\Livewire\Public;

use App\Ai\Agents\ScorecardOcrAgent;
use App\Models\Participant;
use App\Models\ScoreAttempt;
use App\Services\ScoringService;
use Exception;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Files;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

use function Laravel\Ai\agent;

class LeaderboardPage extends Component
{
    use WithFileUploads;

    /** @var array<int, array<string, mixed>> */
    public array $entries = [];

    public string $status = 'Loading scores...';

    public string $lastUpdated = '';

    public bool $showEntryForm = false;

    public string $scorecardId = '';

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $pin = '';

    /** @var TemporaryUploadedFile|null */
    public $ocrImage = null;

    public string $ocrStatus = '';

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

    /** @var array<int, int|null> */
    public array $previewScores = [null, null, null, null];

    public int $totalPreview = 0;

    public string $statusMessage = '';

    public string $saveNotice = '';

    public string $saveNoticeTone = '';

    public bool $showCelebration = false;

    public string $celebrationName = '';

    public string $celebrationScore = '';

    /** @return array<string, string> */
    protected function rules(): array
    {
        return [
            'scorecardId' => 'required|string|max:40',
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:160',
        ];
    }

    /** @var array<string, string> */
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

        $entries = $participants->map(function (Participant $participant) use ($gameCodes): array {
            $scores = [];
            $attempts = [];

            foreach ($gameCodes as $code) {
                $best = $participant->scoreAttempts
                    ->where('game_code', $code)
                    ->where('status', 'approved')
                    ->sortByDesc('calculated_score')
                    ->first();

                $scores[] = $best instanceof ScoreAttempt ? $best->calculated_score : null;
                $attempts[] = $participant->scoreAttempts->where('game_code', $code)->count();
            }

            $completed = count(array_filter($scores, fn ($s): bool => $s !== null));
            $total = array_sum(array_filter($scores, fn ($s): bool => $s !== null));

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

    public function updated(string $field): void
    {
        if (in_array($field, ['hazardMark', 'hazardTime', 'hazardMark2', 'hazardTime2', 'reactionMark', 'reactionMark2', 'quickfireMark', 'quickfireMark2', 'pipeTime', 'pipeTime2'])) {
            $this->calculatePreview();
        }
    }

    public function calculatePreview(): void
    {
        $scoring = app(ScoringService::class);

        $scores = [];

        $hazardCorrect = $this->parseFraction($this->hazardMark ?: $this->hazardMark2);
        $hazardSeconds = $this->parseSeconds($this->hazardTime ?: $this->hazardTime2);
        $scores[] = $hazardCorrect !== null ? $scoring->scoreHazard($hazardCorrect, $hazardSeconds)['score'] : null;

        $reaction = $this->parseFraction($this->reactionMark ?: $this->reactionMark2);
        $scores[] = $reaction !== null ? $scoring->scoreReactionRisk($reaction) : null;

        $quick = $this->parseFraction($this->quickfireMark ?: $this->quickfireMark2);
        $scores[] = $quick !== null ? $scoring->scoreQuickfire($quick) : null;

        $pipeSeconds = $this->parseSeconds($this->pipeTime ?: $this->pipeTime2);
        $pipeCompleted = $pipeSeconds !== null || $this->pipeTime !== '' && $this->pipeTime !== '0' || $this->pipeTime2 !== '' && $this->pipeTime2 !== '0';
        $scores[] = $pipeCompleted ? $scoring->scorePipeFit(true, $pipeSeconds) : null;

        $this->previewScores = $scores;
        $this->totalPreview = array_sum(array_filter($scores, fn (?int $s): bool => $s !== null));
    }

    public function updatedOcrImage(): void
    {
        $this->runOcr();
    }

    public function runOcr(): void
    {
        if (! $this->ocrImage) {
            $this->ocrStatus = 'Please select an image first.';

            return;
        }

        $this->ocrStatus = 'Processing image...';

        $path = $this->ocrImage->store('tmp');

        try {
            $provider = config('ai.ocr.provider', 'openai');
            $model = config('ai.ocr.model', 'gpt-4o');

            $response = agent(
                instructions: (new ScorecardOcrAgent)->instructions(),
                schema: fn (JsonSchema $schema) => (new ScorecardOcrAgent)->schema($schema),
            )->prompt(
                'Extract all visible scorecard data from this photograph.',
                attachments: [
                    Files\Image::fromStorage($path),
                ],
                provider: Lab::tryFrom($provider) ?? Lab::OpenAI,
                model: $model,
            );

            foreach (['scorecardId', 'name', 'phone', 'email', 'hazardMark', 'hazardTime', 'hazardMark2', 'hazardTime2', 'reactionMark', 'reactionMark2', 'quickfireMark', 'quickfireMark2', 'pipeTime', 'pipeTime2'] as $field) {
                if (isset($response[$field]) && property_exists($this, $field)) {
                    $this->{$field} = $response[$field];
                }
            }

            $this->ocrStatus = 'Scorecard data extracted successfully. Review and save.';
            $this->calculatePreview();
        } catch (Exception $e) {
            $this->ocrStatus = 'Failed to process image: '.$e->getMessage();
        } finally {
            if (Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
        }
    }

    public function save(): void
    {
        $this->validate();
        $scoring = app(ScoringService::class);

        $this->saveNotice = '';
        $this->saveNoticeTone = 'pending';

        $attempts = [];

        $hazardCorrect = $this->parseFraction($this->hazardMark);
        $hazardSeconds = $this->parseSeconds($this->hazardTime);
        if ($hazardCorrect !== null) {
            $result = $scoring->scoreHazard($hazardCorrect, $hazardSeconds);
            $attempts[] = ['game_code' => 'hazard_hunt_ride', 'raw_result' => $result['rawResult'], 'score' => $result['score'], 'breakdown' => $result['breakdown']];
        }

        $hazardCorrect2 = $this->parseFraction($this->hazardMark2);
        $hazardSeconds2 = $this->parseSeconds($this->hazardTime2);
        if ($hazardCorrect2 !== null) {
            $result = $scoring->scoreHazard($hazardCorrect2, $hazardSeconds2);
            $attempts[] = ['game_code' => 'hazard_hunt_ride', 'raw_result' => $result['rawResult'], 'score' => $result['score'], 'breakdown' => $result['breakdown']];
        }

        $reaction = $this->parseFraction($this->reactionMark);
        if ($reaction !== null) {
            $score = $scoring->scoreReactionRisk($reaction);
            $attempts[] = ['game_code' => 'reaction_risk', 'raw_result' => "{$reaction}/10 sticks", 'score' => $score, 'breakdown' => []];
        }

        $reaction2 = $this->parseFraction($this->reactionMark2);
        if ($reaction2 !== null) {
            $score = $scoring->scoreReactionRisk($reaction2);
            $attempts[] = ['game_code' => 'reaction_risk', 'raw_result' => "{$reaction2}/10 sticks", 'score' => $score, 'breakdown' => []];
        }

        $quick = $this->parseFraction($this->quickfireMark);
        if ($quick !== null) {
            $score = $scoring->scoreQuickfire($quick);
            $attempts[] = ['game_code' => 'quickfire_quiz', 'raw_result' => "{$quick}/10", 'score' => $score, 'breakdown' => []];
        }

        $quick2 = $this->parseFraction($this->quickfireMark2);
        if ($quick2 !== null) {
            $score = $scoring->scoreQuickfire($quick2);
            $attempts[] = ['game_code' => 'quickfire_quiz', 'raw_result' => "{$quick2}/10", 'score' => $score, 'breakdown' => []];
        }

        $pipeSeconds = $this->parseSeconds($this->pipeTime);
        $pipeFailed = $this->isPipeFitFail($this->pipeTime);
        if (! $pipeFailed && ($pipeSeconds !== null || $this->pipeTime !== '' && $this->pipeTime !== '0')) {
            $completed = ! preg_match('/fail|incomplete|incorrect/i', $this->pipeTime);
            $score = $scoring->scorePipeFit($completed, $pipeSeconds);
            $rawResult = $completed ? 'pass + '.($pipeSeconds ?? '0').' sec' : 'fail';
            $attempts[] = ['game_code' => 'pipe_fit_challenge', 'raw_result' => $rawResult, 'score' => $score, 'breakdown' => []];
        }

        $pipeSeconds2 = $this->parseSeconds($this->pipeTime2);
        $pipeFailed2 = $this->isPipeFitFail($this->pipeTime2);
        if (! $pipeFailed2 && ($pipeSeconds2 !== null || $this->pipeTime2 !== '' && $this->pipeTime2 !== '0')) {
            $completed2 = ! preg_match('/fail|incomplete|incorrect/i', $this->pipeTime2);
            $score = $scoring->scorePipeFit($completed2, $pipeSeconds2);
            $rawResult = $completed2 ? 'pass + '.($pipeSeconds2 ?? '0').' sec' : 'fail';
            $attempts[] = ['game_code' => 'pipe_fit_challenge', 'raw_result' => $rawResult, 'score' => $score, 'breakdown' => []];
        }

        try {
            DB::transaction(function () use ($attempts, &$participant): void {
                $participant = Participant::firstOrCreate(
                    ['scorecard_id' => strtoupper($this->scorecardId)],
                    ['name' => $this->name, 'phone' => $this->phone, 'email' => $this->email]
                );

                if ($participant->wasRecentlyCreated === false) {
                    $participant->update(['name' => $this->name, 'phone' => $this->phone, 'email' => $this->email]);
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
            });

            $this->loadLeaderboard();

            $completedGamesCount = count(array_unique(array_column($attempts, 'game_code')));
            $totalSaved = array_sum(array_column($attempts, 'score'));
            $this->saveNotice = "{$this->name} saved. Total {$totalSaved}/400. {$completedGamesCount}/4 games recorded.";
            $this->saveNoticeTone = 'success';

            if ($this->isCurrentLeader($this->scorecardId)) {
                $this->celebrationName = $this->name;
                $this->celebrationScore = "Total {$totalSaved}/400";
                $this->showCelebration = true;
            }

            $this->resetEntryForm();
            $this->calculatePreview();
            $this->dispatch('score-saved');
        } catch (Exception) {
            $this->saveNotice = 'Failed to save score. Please try again.';
            $this->saveNoticeTone = 'error';
        }
    }

    private function isCurrentLeader(string $scorecardId): bool
    {
        if ($this->entries === []) {
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

    private function parseFraction(?string $raw): ?int
    {
        if (in_array($raw, [null, '', '0'], true)) {
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
        if (in_array($raw, [null, '', '0'], true)) {
            return false;
        }

        return (bool) preg_match('/\b(fail|incomplete|incorrect)\b/i', $raw);
    }

    private function parseSeconds(?string $raw): ?int
    {
        if (in_array($raw, [null, '', '0'], true)) {
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

    public function render(): View
    {
        return view('livewire.public.leaderboard-page')
            ->layout('components.layouts.app');
    }
}
