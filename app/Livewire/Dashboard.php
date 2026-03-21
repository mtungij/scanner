<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app-sidebar')]
class Dashboard extends Component
{
    public function getTodaySalesTotalProperty(): float
    {
        return (float) $this->todaySalesQuery()->sum('total_amount');
    }

    public function getTodayTransactionsCountProperty(): int
    {
        return $this->todaySalesQuery()->count();
    }

    public function getRecentSalesProperty()
    {
        return $this->todaySalesQuery()
            ->with('user')
            ->latest()
            ->limit(8)
            ->get();
    }

    public function getTodayTopProductsProperty(): Collection
    {
        $products = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereDate('sales.created_at', today())
            ->when($this->isCashier(), function (Builder $query): void {
                $query->where('sales.user_id', auth()->id());
            })
            ->selectRaw('products.id, products.name, SUM(sale_items.quantity) as sold_quantity')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('sold_quantity')
            ->limit(10)
            ->get();

        $maxSoldQuantity = (int) $products->max('sold_quantity');

        return $products->map(function ($product) use ($maxSoldQuantity): array {
            $soldQuantity = (int) $product->sold_quantity;

            return [
                'id' => (int) $product->id,
                'name' => (string) $product->name,
                'sold_quantity' => $soldQuantity,
                'bar_percentage' => $maxSoldQuantity > 0 ? (int) round(($soldQuantity / $maxSoldQuantity) * 100) : 0,
            ];
        });
    }

    public function getEmptyStockProductsProperty(): Collection
    {
        $products = Product::query()
            ->leftJoin('sale_items', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('sales', function ($join): void {
                $join->on('sales.id', '=', 'sale_items.sale_id')
                    ->whereDate('sales.created_at', today());

                if ($this->isCashier()) {
                    $join->where('sales.user_id', auth()->id());
                }
            })
            ->where('products.stock_quantity', '<=', 0)
            ->selectRaw('products.id, products.name, products.stock_quantity, COALESCE(SUM(sale_items.quantity), 0) as sold_today')
            ->groupBy('products.id', 'products.name', 'products.stock_quantity')
            ->orderByDesc('sold_today')
            ->orderBy('products.name')
            ->limit(10)
            ->get();

        $maxSoldToday = (int) $products->max('sold_today');

        return $products->map(function ($product) use ($maxSoldToday): array {
            $soldToday = (int) $product->sold_today;

            return [
                'id' => (int) $product->id,
                'name' => (string) $product->name,
                'stock_quantity' => (int) $product->stock_quantity,
                'sold_today' => $soldToday,
                'bar_percentage' => $maxSoldToday > 0 ? (int) round(($soldToday / $maxSoldToday) * 100) : 0,
            ];
        });
    }

    public function getDashboardSummaryLabelProperty(): string
    {
        if ($this->isCashier()) {
            return 'Your current sales summary.';
        }

        return 'Current sales summary.';
    }

    public function getCurrentDateLabelProperty(): string
    {
        return today()->format('M d, Y');
    }

    public function render()
    {
        return view('livewire.dashboard');
    }

    private function todaySalesQuery(): Builder
    {
        return Sale::query()
            ->whereDate('created_at', today())
            ->when($this->isCashier(), function (Builder $query): void {
                $query->where('user_id', auth()->id());
            });
    }

    private function isCashier(): bool
    {
        return auth()->user()?->role === 'cashier';
    }
}
