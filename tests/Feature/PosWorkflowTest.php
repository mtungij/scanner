<?php

declare(strict_types=1);

use App\Livewire\Pos;
use App\Livewire\Products;
use App\Livewire\Users;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

it('allows admin to create a product', function () {
    $admin = User::factory()->admin()->create();

    actingAs($admin);

    livewire(Products::class)
        ->set('name', 'Milk 1L')
        ->set('barcode', '123456789012')
        ->set('price', '4.50')
        ->set('stockQuantity', '20')
        ->call('saveProduct')
        ->assertHasNoErrors();

    expect(Product::query()->where('barcode', '123456789012')->exists())->toBeTrue();
});

it('denies cashier from products management page', function () {
    $cashier = User::factory()->cashier()->create();

    actingAs($cashier);

    get(route('products'))->assertForbidden();
});

it('creates sale and reduces stock on checkout', function () {
    $cashier = User::factory()->cashier()->create();
    $product = Product::factory()->create([
        'barcode' => '987654321098',
        'price' => 5.00,
        'stock_quantity' => 10,
    ]);

    actingAs($cashier);

    livewire(Pos::class)
        ->set('barcodeInput', $product->barcode)
        ->call('addProductByBarcode')
        ->set('paymentAmount', '10.00')
        ->set('paymentMethod', 'M-pesa')
        ->call('checkout')
        ->assertHasNoErrors();

    $sale = Sale::query()->latest('id')->first();

    expect($sale)->not->toBeNull();
    expect((float) $sale->total_amount)->toBe(5.00);
    expect((float) $sale->payment_received)->toBe(10.00);
    expect((float) $sale->change_given)->toBe(5.00);
    expect($sale->payment_method)->toBe('M-pesa');

    expect(SaleItem::query()->where('sale_id', $sale->id)->exists())->toBeTrue();
    expect($product->fresh()->stock_quantity)->toBe(9);
});

it('allows admin to create cashier from users screen', function () {
    $admin = User::factory()->admin()->create();

    actingAs($admin);

    livewire(Users::class)
        ->set('name', 'Cashier Two')
        ->set('email', 'cashier2@example.com')
        ->set('role', 'cashier')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('createUser')
        ->assertHasNoErrors();

    expect(User::query()->where('email', 'cashier2@example.com')->where('role', 'cashier')->exists())->toBeTrue();
});

it('denies cashier from users management page', function () {
    $cashier = User::factory()->cashier()->create();

    actingAs($cashier);

    get(route('users'))->assertForbidden();
});
