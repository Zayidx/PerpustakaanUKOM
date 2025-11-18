<?php

namespace App\Livewire\Concerns;

trait HandlesAlerts
{
    protected function flashSuccess(string $message): void
    {
        session()->flash('message', $message);
        $this->dispatch('notify', type: 'success', message: $message);
    }

    protected function flashError(string $message): void
    {
        session()->flash('error', $message);
        $this->dispatch('notify', type: 'error', message: $message);
    }
}
