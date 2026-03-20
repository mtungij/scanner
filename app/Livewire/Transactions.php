<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Sale;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app-sidebar')]
final class Transactions extends Component
{
    public string $search = '';

    public function getSalesProperty()
    {
        return Sale::query()
            ->with(['user', 'items'])
            ->when($this->search !== '', function ($query): void {
                $query->where('id', $this->search)
                    ->orWhereHas('user', function ($userQuery): void {
                        $userQuery->where('name', 'like', "%{$this->search}%");
                    });
            })
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.transactions');
    }
}
