<?php

namespace App\Livewire\Siswa;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Dashboard extends Component
{

    #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Siswa Dashboard')]
    public function render()
    {
        return view('livewire.siswa.dashboard');
    } // Render tampilan komponen dashboard siswa
}
