<?php

namespace App\Http\Livewire\HazardHunt;

use Livewire\Component;

class HazardStartPage extends Component
{
    public string $scorecardId = '';

    public string $error = '';

    public int $countdown = 0;

    public bool $showCountdown = false;

    protected $rules = [
        'scorecardId' => 'required|string|min:2|max:40',
    ];

    public function confirmAndStart(): void
    {
        $this->validate();
        $this->error = '';
        $this->showCountdown = true;
        $this->countdown = 3;

        $this->dispatch('countdown-start');
    }

    public function startGame(): void
    {
        session(['hazard_scorecard_id' => $this->scorecardId]);
        $this->redirect(route('hazard.play'));
    }

    public function render()
    {
        return view('livewire.hazard-hunt.start-page')
            ->layout('components.layouts.app');
    }
}
