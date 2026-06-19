<?php

namespace App\Http\Livewire\Public;

use Illuminate\View\View;
use Livewire\Component;

class ScoringGuidePage extends Component
{
    public function render(): View
    {
        return view('livewire.public.scoring-guide-page')
            ->layout('components.layouts.app');
    }
}
