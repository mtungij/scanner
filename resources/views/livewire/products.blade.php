<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <x-ui.heading level="h1" size="xl">Products</x-ui.heading>
            <x-ui.text class="mt-1 opacity-60">Manage barcode-based products.</x-ui.text>
        </div>

        <x-ui.modal id="product-modal" heading="Product" width="md">
            <x-slot:trigger>
                <x-ui.button icon="plus" x-on:click="$wire.resetForm()">Add Product</x-ui.button>
            </x-slot:trigger>

            <div class="space-y-4">
                <x-ui.field>
                    <x-ui.label>Name</x-ui.label>
                    <x-ui.input wire:model="name" placeholder="Product name" />
                    <x-ui.error name="name" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Barcode</x-ui.label>
                    <div class="flex gap-2">
                        <x-ui.input wire:model="barcode" placeholder="e.g. 123456789012" />
                        <x-ui.button type="button" variant="outline" wire:click="generateBarcode">Generate</x-ui.button>
                    </div>
                    <x-ui.error name="barcode" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Price</x-ui.label>
                    <x-ui.input wire:model="price" type="number" step="0.01" placeholder="0.00" />
                    <x-ui.error name="price" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Stock Quantity</x-ui.label>
                    <x-ui.input wire:model="stockQuantity" type="number" min="0" />
                    <x-ui.error name="stockQuantity" />
                </x-ui.field>
            </div>

            <x-slot:footer>
                <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
                <x-ui.button wire:click="saveProduct">Save</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    </div>

    <x-ui.card size="xl">
        <x-ui.input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by name or barcode" icon="magnifying-glass" />

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-300 dark:border-neutral-700">
                        <th class="px-2 py-2">Name</th>
                        <th class="px-2 py-2">Barcode</th>
                        <th class="px-2 py-2 text-right">Price</th>
                        <th class="px-2 py-2 text-right">Stock</th>
                        <th class="px-2 py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->products as $product)
                        <tr class="border-b border-gray-200 last:border-b-0 dark:border-neutral-800" wire:key="product-{{ $product->id }}">
                            <td class="px-2 py-2">{{ $product->name }}</td>
                            <td class="px-2 py-2 font-mono text-xs">{{ $product->barcode }}</td>
                            <td class="px-2 py-2 text-right">${{ number_format((float) $product->price, 2) }}</td>
                            <td class="px-2 py-2 text-right">{{ $product->stock_quantity }}</td>
                            <td class="px-2 py-2">
                                <div class="flex justify-end gap-2">
                                    <x-ui.button
                                        size="xs"
                                        variant="outline"
                                        wire:click="editProduct({{ $product->id }})"
                                        x-on:click.debounce.50ms="$nextTick(() => $modal.open('product-modal'))"
                                    >
                                        Edit
                                    </x-ui.button>
                                    <x-ui.button size="xs" color="red" variant="soft" wire:click="deleteProduct({{ $product->id }})">Delete</x-ui.button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-2 py-6 text-center text-neutral-500">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
