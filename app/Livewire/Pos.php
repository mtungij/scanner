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

    public string $scanMessage = 'Ready to scan a product barcode.';

    public string $scanMessageType = 'neutral';

    public string $paymentAmount = '';

    public string $paymentMethod = 'Cash';

    public string $searchProductId = '';

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
            $this->focusBarcodeInput();

            return;
        }

        $product = Product::query()->where('barcode', $this->barcodeInput)->first();

        if (! $product) {
            $this->setScanFeedback(sprintf('Barcode %s was not found.', $this->barcodeInput), 'error');
            Toast::error('Product not found for this barcode.');
            $this->focusBarcodeInput();

            return;
        }

        $this->addProductToCart($product, true);
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

    public function addProductBySearch(): void
    {
        if ($this->searchProductId === '') {
            return;
        }

        $product = Product::query()->findOrFail((int) $this->searchProductId);

        $this->addProductToCart($product);
        $this->searchProductId = '';
    }

    private function autoSetPaymentAmount(): void
    {
        if ($this->paymentAmount === '' && $this->total > 0) {
            $this->paymentAmount = (string) number_format($this->total, 2, '.', '');
        }
    }

    public function getAvailableProductsProperty(): array
    {
        return Product::query()
            ->where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'barcode', 'price', 'stock_quantity'])
            ->toArray();
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
            'paymentMethod' => ['required', 'string', 'in:Cash,M-pesa,Tigo-pesa,Bank'],
        ]);

        $sale = DB::transaction(function (): Sale {
            $sale = Sale::query()->create([
                'user_id' => auth()->id(),
                'total_amount' => $this->total,
                'payment_received' => (float) $this->paymentAmount,
                'change_given' => $this->change,
                'payment_method' => $this->paymentMethod,
            ]);

            foreach ($this->cart as $item) {
                $product = Product::query()->lockForUpdate()->findOrFail($item['product_id']);

                if ($product->stock_quantity < $item['quantity']) {
                    abort(422, 'Insufficient stock for '.$product->name);
                }

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => (int) $item['quantity'],
                    'buy_price' => $product->buy_price,
                    'unit_price' => (float) $item['price'],
                    'line_total' => ((float) $item['price']) * ((int) $item['quantity']),
                    'profit_amount' => (((float) $item['price']) - (float) ($product->buy_price ?? 0)) * ((int) $item['quantity']),
                ]);

                $product->decrement('stock_quantity', (int) $item['quantity']);
            }

            return $sale;
        });

        $this->reset(['cart', 'barcodeInput', 'paymentAmount', 'paymentMethod']);
        Toast::success('Checkout complete.');

        $this->redirectRoute('receipt', ['sale' => $sale->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.pos');
    }

    private function addProductToCart(Product $product, bool $focusBarcodeInput = false): void
    {
        $cartItem = $this->cart[$product->id] ?? null;
        $nextQuantity = ($cartItem['quantity'] ?? 0) + 1;

        if ($nextQuantity > $product->stock_quantity) {
            $this->setScanFeedback(sprintf('%s is out of stock for another scan.', $product->name), 'warning');
            Toast::warning('Insufficient stock for this product.');

            if ($focusBarcodeInput) {
                $this->focusBarcodeInput();
            }

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

        $this->setScanFeedback(sprintf('Added %s to cart from barcode %s.', $product->name, $product->barcode), 'success');
        $this->autoSetPaymentAmount();
        $this->barcodeInput = '';

        if ($focusBarcodeInput) {
            $this->focusBarcodeInput();
        }
    }

    private function setScanFeedback(string $message, string $type): void
    {
        $this->scanMessage = $message;
        $this->scanMessageType = $type;
    }

    private function focusBarcodeInput(): void
    {
        $this->dispatch('focus-pos-barcode-input');
    }
}
