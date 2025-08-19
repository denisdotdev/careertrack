<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        
        return view('livewire.dashboard', [
            'user' => $user,
            'companies' => $user->companies()->with('pivot')->get(),
        ])->layout('layouts.app');
    }
} 