<?php

namespace App\Livewire\Guru;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Dashboard extends Component
{
     #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Guru Dashboard')]
    public function render()
    {
        return view('livewire.guru.dashboard');
    } // Render tampilan komponen dashboard guru
}
