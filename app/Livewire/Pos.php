<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use App\Models\Sale;
use App\Support\Toast;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app-sidebar')]
class Pos extends Component
{
    public string $barcodeInput = '';

    public string $paymentAmount = '';

    /** @var array<int, array<string, mixed>> */
    public array $cart = [];

    public function scanBarcode(string $barcode): void
    {
        $this->barcodeInput = trim($barcode);
        $this->addProductByBarcode();
    }

    public function addProductByBarcode(): void
    {
        if ($this->barcodeInput === '') {
            return;
        }

        $product = Product::query()->where('barcode', $this->barcodeInput)->first();

        if (! $product) {
            Toast::error('Product not found for this barcode.');

            return;
        }

        $cartItem = $this->cart[$product->id] ?? null;
        $nextQuantity = ($cartItem['quantity'] ?? 0) + 1;

        if ($nextQuantity > $product->stock_quantity) {
            Toast::warning('Insufficient stock for this product.');

            return;
        }

        $this->cart[$product->id] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'barcode' => $product->barcode,
            'price' => (float) $product->price,
            'quantity' => $nextQuantity,
            'stock_quantity' => $product->stock_quantity,
        ];

        $this->barcodeInput = '';
    }

    public function increaseQuantity(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        if ($this->cart[$productId]['quantity'] >= $this->cart[$productId]['stock_quantity']) {
            Toast::warning('Insufficient stock for this product.');

            return;
        }

        $this->cart[$productId]['quantity']++;
    }

    public function decreaseQuantity(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        $this->cart[$productId]['quantity']--;

        if ($this->cart[$productId]['quantity'] <= 0) {
            unset($this->cart[$productId]);
        }
    }

    public function removeItem(int $productId): void
    {
        unset($this->cart[$productId]);
    }

    public function getCartCountProperty(): int
    {
        return array_sum(array_column($this->cart, 'quantity'));
    }

    public function getTotalProperty(): float
    {
        $total = 0;

        foreach ($this->cart as $item) {
            $total += ((float) $item['price']) * ((int) $item['quantity']);
        }

        return $total;
    }

    public function getChangeProperty(): float
    {
        $payment = (float) ($this->paymentAmount !== '' ? $this->paymentAmount : '0');

        return max($payment - $this->total, 0);
    }

    public function checkout(): void
    {
        if ($this->cart === []) {
            $this->addError('cart', 'Cart is empty.');

            return;
        }

        $this->validate([
            'paymentAmount' => ['required', 'numeric', 'min:'.$this->total],
        ]);

        $sale = DB::transaction(function (): Sale {
            $sale = Sale::query()->create([
                'user_id' => auth()->id(),
                'total_amount' => $this->total,
                'payment_received' => (float) $this->paymentAmount,
                'change_given' => $this->change,
            ]);

            foreach ($this->cart as $item) {
                $product = Product::query()->lockForUpdate()->findOrFail($item['product_id']);

                if ($product->stock_quantity < $item['quantity']) {
                    abort(422, 'Insufficient stock for '.$product->name);
                }

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => (float) $item['price'],
                    'line_total' => ((float) $item['price']) * ((int) $item['quantity']),
                ]);

                $product->decrement('stock_quantity', (int) $item['quantity']);
            }

            return $sale;
        });

        $this->reset(['cart', 'barcodeInput', 'paymentAmount']);
        Toast::success('Checkout complete.');

        $this->redirectRoute('receipt', ['sale' => $sale->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.pos');
    }
}
