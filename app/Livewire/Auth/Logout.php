<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Keluar')]
#[Layout('components.layouts.auth-layouts')]
class Logout extends Component
{
    public function mount(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $this->redirectRoute('login');
    }

    public function render()
    {
        return view('livewire.auth.logout');
    }
}
