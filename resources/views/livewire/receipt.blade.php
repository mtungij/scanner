<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center justify-between gap-3">
        <div>
            <x-ui.heading level="h1" size="xl">Receipt #{{ $sale->id }}</x-ui.heading>
            <x-ui.text class="mt-1 opacity-60">{{ $sale->created_at->format('M d, Y H:i') }}</x-ui.text>
        </div>

        <div class="flex gap-2">
            <x-ui.button variant="outline" type="button" onclick="window.print()">Print</x-ui.button>
            <x-ui.button href="{{ route('pos') }}" wire:navigate>New Sale</x-ui.button>
        </div>
    </div>

    <x-ui.card size="xl" class="space-y-4">
        <div class="grid gap-2 sm:grid-cols-2">
            <x-ui.text>Cashier: <strong>{{ $sale->user?->name ?? 'N/A' }}</strong></x-ui.text>
            <x-ui.text>Payment: <strong>${{ number_format((float) $sale->payment_received, 2) }}</strong></x-ui.text>
            <x-ui.text>Total: <strong>${{ number_format((float) $sale->total_amount, 2) }}</strong></x-ui.text>
            <x-ui.text>Change: <strong>${{ number_format((float) $sale->change_given, 2) }}</strong></x-ui.text>
        </div>

        <x-ui.separator />

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-300 dark:border-neutral-700">
                        <th class="px-2 py-2">Item</th>
                        <th class="px-2 py-2 text-right">Qty</th>
                        <th class="px-2 py-2 text-right">Price</th>
                        <th class="px-2 py-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr class="border-b border-gray-200 last:border-b-0 dark:border-neutral-800" wire:key="receipt-item-{{ $item->id }}">
                            <td class="px-2 py-2">{{ $item->product?->name ?? 'Deleted product' }}</td>
                            <td class="px-2 py-2 text-right">{{ $item->quantity }}</td>
                            <td class="px-2 py-2 text-right">${{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="px-2 py-2 text-right">${{ number_format((float) $item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
