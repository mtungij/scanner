<?php

declare(strict_types=1);

use App\Livewire\Dashboard;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

it('shows cashier current sales only for today and does not show profit', function () {
    $cashier = User::factory()->cashier()->create();
    $otherCashier = User::factory()->cashier()->create();

    $cashierProduct = Product::factory()->create(['name' => 'Cashier Product']);
    $otherCashierProduct = Product::factory()->create(['name' => 'Other Cashier Product']);

    $cashierSaleToday = Sale::factory()->create([
        'user_id' => $cashier->id,
        'total_amount' => 120.00,
        'created_at' => now()->startOfDay()->addHours(2),
    ]);

    SaleItem::factory()->create([
        'sale_id' => $cashierSaleToday->id,
        'product_id' => $cashierProduct->id,
        'quantity' => 3,
    ]);

    $otherCashierSaleToday = Sale::factory()->create([
        'user_id' => $otherCashier->id,
        'total_amount' => 350.00,
        'created_at' => now()->startOfDay()->addHours(3),
    ]);

    SaleItem::factory()->create([
        'sale_id' => $otherCashierSaleToday->id,
        'product_id' => $otherCashierProduct->id,
        'quantity' => 7,
    ]);

    Sale::factory()->create([
        'user_id' => $cashier->id,
        'total_amount' => 500.00,
        'created_at' => now()->subDay(),
    ]);

    actingAs($cashier);

    livewire(Dashboard::class)
        ->assertSet('todaySalesTotal', 120.00)
        ->assertSet('todayTransactionsCount', 1)
        ->assertSet('dashboardSummaryLabel', 'Your current sales summary.')
        ->assertSet('todayTopProducts.0.name', 'Cashier Product')
        ->assertSet('todayTopProducts.0.sold_quantity', 3);

    get(route('dashboard'))
        ->assertSuccessful()
        ->assertDontSee('Profit')
        ->assertSee('Trending Products Today (Top 10)')
        ->assertSee('Empty Stock Products (Top 10)')
        ->assertSee('Today: '.today()->format('M d, Y'));
});

it('shows at most 10 empty stock products in dashboard graph', function () {
    $cashier = User::factory()->cashier()->create();

    actingAs($cashier);

    Product::factory()->count(12)->create([
        'stock_quantity' => 0,
    ]);

    livewire(Dashboard::class)
        ->assertCount('emptyStockProducts', 10);
});
