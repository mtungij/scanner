<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use App\Support\Toast;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app-sidebar')]
class Products extends Component
{
    public string $search = '';

    public ?int $editingProductId = null;

    public string $name = '';

    public string $barcode = '';

    public string $buyPrice = '';

    public string $price = '';

    public string $stockQuantity = '0';

    public string $unit = 'piece';

    public string $category = '';

    public string $expireDate = '';

    public string $previewBarcode = '';

    public string $previewName = '';

    public bool $barcodeWasGenerated = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['required', 'string', 'max:255', Rule::unique('products', 'barcode')->ignore($this->editingProductId)],
            'buyPrice' => ['nullable', 'numeric', 'min:0.01'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'stockQuantity' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:255'],
            'expireDate' => ['nullable', 'date'],
        ];
    }

    public function getProductsProperty()
    {
        return Product::query()
            ->when($this->search !== '', function ($query): void {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('barcode', 'like', "%{$this->search}%");
            })
            ->orderByDesc('id')
            ->get();
    }

    public function generateBarcode(): void
    {
        $this->barcode = $this->createUniqueBarcode();
        $this->barcodeWasGenerated = true;
    }

    public function setScannedBarcode(string $barcode): void
    {
        $this->barcode = trim($barcode);
        $this->barcodeWasGenerated = false;
    }

    public function saveProduct(): void
    {
        $isCreating = $this->editingProductId === null;

        if (blank($this->barcode)) {
            $this->barcode = $this->createUniqueBarcode();
            $this->barcodeWasGenerated = true;
        }

        $validated = $this->validate();

        $product = Product::query()->updateOrCreate(
            ['id' => $this->editingProductId],
            [
                'name' => $validated['name'],
                'barcode' => $validated['barcode'],
                'buy_price' => $validated['buyPrice'] ?: null,
                'price' => $validated['price'],
                'stock_quantity' => $validated['stockQuantity'],
                'unit' => $validated['unit'],
                'category' => $validated['category'] ?: null,
                'expire_date' => $validated['expireDate'] ?: null,
            ]
        );

        $message = $this->editingProductId ? 'Product updated successfully.' : 'Product created successfully.';

        if ($isCreating && $this->barcodeWasGenerated) {
            $this->previewBarcode = $product->barcode;
            $this->previewName = $product->name;
            $this->dispatch('open-modal', id: 'barcode-modal');
        }

        $this->resetForm();
        $this->dispatch('close-modal', id: 'product-modal');
        Toast::success($message);
    }

    public function editProduct(int $productId): void
    {
        $product = Product::query()->findOrFail($productId);

        $this->editingProductId = $product->id;
        $this->name = $product->name;
        $this->barcode = $product->barcode;
        $this->buyPrice = (string) ($product->buy_price ?? '');
        $this->price = (string) $product->price;
        $this->stockQuantity = (string) $product->stock_quantity;
        $this->unit = $product->unit ?? 'piece';
        $this->category = $product->category ?? '';
        $this->expireDate = (string) ($product->getRawOriginal('expire_date') ?? '');
        $this->barcodeWasGenerated = false;
    }

    public function deleteProduct(int $productId): void
    {
        Product::query()->findOrFail($productId)->delete();
        Toast::success('Product deleted successfully.');
    }

    public function previewBarcodeLabel(int $productId): void
    {
        $product = Product::query()->findOrFail($productId);

        $this->previewBarcode = $product->barcode;
        $this->previewName = $product->name;
    }

    public function resetForm(): void
    {
        $this->resetValidation();
        $this->reset(['editingProductId', 'name', 'barcode', 'buyPrice', 'price', 'stockQuantity', 'unit', 'category', 'expireDate', 'barcodeWasGenerated']);
        $this->stockQuantity = '0';
        $this->unit = 'piece';
    }

    public function render()
    {
        return view('livewire.products');
    }

    protected function createUniqueBarcode(): string
    {
        do {
            $barcode = str_pad((string) random_int(0, 999_999_999_999), 12, '0', STR_PAD_LEFT);
        } while (Product::query()->where('barcode', $barcode)->exists());

        return $barcode;
    }
}
