<?php

namespace App\Http\Livewire\Quiz;

use App\Events\QuizPlayerJoined;
use App\Models\Participant;
use App\Models\QuizPlayer;
use App\Models\QuizRound;
use Illuminate\View\View;
use Livewire\Component;

class QuizJoinPage extends Component
{
    public ?QuizRound $round = null;

    public string $code = '';

    public string $scorecardId = '';

    public string $name = '';

    public string $statusMessage = 'Enter your Scorecard ID and name to join.';

    public string $playerToken = '';

    public function mount(string $code = ''): void
    {
        if ($code !== '' && $code !== '0') {
            $this->code = $code;
            $this->loadRound();
        }
    }

    public function loadRound(): void
    {
        $this->round = QuizRound::where('code', strtoupper($this->code))
            ->where(function ($q): void {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $this->round instanceof QuizRound) {
            $this->statusMessage = 'Invalid or expired join code.';
        }
    }

    public function joinRound(): void
    {
        $this->validate([
            'code' => 'required|string|size:6',
            'scorecardId' => 'required|string|max:40',
            'name' => 'required|string|max:100',
        ]);

        $this->loadRound();
        if (! $this->round instanceof QuizRound) {
            return;
        }

        if ($this->round->phase_name !== 'lobby') {
            $this->statusMessage = 'This round has already started.';

            return;
        }

        $existing = QuizPlayer::where('quiz_round_id', $this->round->id)
            ->where('scorecard_id', strtoupper($this->scorecardId))
            ->first();

        if ($existing) {
            session(['quiz_player_token' => $existing->player_token]);
            $this->statusMessage = 'You are already in this round. Redirecting...';
            $this->redirect(route('quiz.play', ['round' => $this->round->id]));

            return;
        }

        $count = $this->round->players()->count();
        if ($count >= 3) {
            $this->statusMessage = 'Round is full (max 3 players).';

            return;
        }

        $participant = Participant::firstOrCreate(
            ['scorecard_id' => strtoupper($this->scorecardId)],
            ['name' => $this->name]
        );

        $token = bin2hex(random_bytes(16));

        QuizPlayer::create([
            'quiz_round_id' => $this->round->id,
            'participant_id' => $participant->id,
            'scorecard_id' => strtoupper($this->scorecardId),
            'name' => $this->name,
            'player_token' => $token,
        ]);

        $this->playerToken = $token;
        session(['quiz_player_token' => $token]);
        $this->statusMessage = 'Joined! Redirecting...';
        $this->round->refresh();

        $playerCount = $this->round->players()->count();
        QuizPlayerJoined::dispatch($this->round->id, $this->name, $playerCount);

        $this->redirect(route('quiz.play', ['round' => $this->round->id]));
    }

    public function render(): View
    {
        $players = $this->round instanceof QuizRound ? $this->round->players()->get() : collect();

        return view('livewire.quiz.join-page', [
            'players' => $players,
        ])->layout('components.layouts.app');
    }
}
