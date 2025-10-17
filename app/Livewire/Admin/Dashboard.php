<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{

    #[Layout('components.layouts.dashboard-layouts')]
    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
