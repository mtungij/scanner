<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Sale;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app-sidebar')]
class Dashboard extends Component
{
    public function getTodaySalesTotalProperty(): float
    {
        return (float) Sale::query()
            ->whereDate('created_at', now())
            ->sum('total_amount');
    }

    public function getTodayTransactionsCountProperty(): int
    {
        return Sale::query()
            ->whereDate('created_at', now())
            ->count();
    }

    public function getRecentSalesProperty()
    {
        return Sale::query()
            ->with('user')
            ->latest()
            ->limit(8)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
