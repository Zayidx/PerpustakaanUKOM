<?php

namespace App\Livewire\Guru;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Dashboard extends Component
{
     #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Siswa Dashboard')]
    public function render()
    {
        return view('livewire.guru.dashboard');
    }
}
