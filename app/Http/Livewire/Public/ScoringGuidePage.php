<?php

namespace App\Http\Livewire\Public;

use Livewire\Component;

class ScoringGuidePage extends Component
{
    public function render()
    {
        return view('livewire.public.scoring-guide-page')
            ->layout('components.layouts.app');
    }
}
