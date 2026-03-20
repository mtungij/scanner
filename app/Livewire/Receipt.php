<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Sale;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app-sidebar')]
class Receipt extends Component
{
    public Sale $sale;

    public function mount(Sale $sale): void
    {
        $this->sale = $sale->load(['items.product', 'user']);
    }

    public function render()
    {
        return view('livewire.receipt');
    }
}
