<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <x-ui.heading level="h1" size="xl">Sales Transactions</x-ui.heading>
            <x-ui.text class="mt-1 opacity-60">Recorded checkout history.</x-ui.text>
        </div>
        <x-ui.button href="{{ route('pos') }}" icon="plus" wire:navigate>New Transaction</x-ui.button>
    </div>

    <x-ui.card size="xl">
        <x-ui.input
            wire:model.live.debounce.300ms="search"
            type="search"
            placeholder="Search by sale # or cashier"
            icon="magnifying-glass"
        />

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-300 dark:border-neutral-700">
                        <th class="px-2 py-2">Sale #</th>
                        <th class="px-2 py-2">Cashier</th>
                        <th class="px-2 py-2 text-right">Items</th>
                        <th class="px-2 py-2 text-right">Total</th>
                        <th class="px-2 py-2">Date</th>
                        <th class="px-2 py-2 text-right">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->sales as $sale)
                        <tr class="border-b border-gray-200 last:border-b-0 dark:border-neutral-800" wire:key="sale-{{ $sale->id }}">
                            <td class="px-2 py-2">#{{ $sale->id }}</td>
                            <td class="px-2 py-2">{{ $sale->user?->name ?? 'N/A' }}</td>
                            <td class="px-2 py-2 text-right">{{ $sale->items->sum('quantity') }}</td>
                            <td class="px-2 py-2 text-right">${{ number_format((float) $sale->total_amount, 2) }}</td>
                            <td class="px-2 py-2">{{ $sale->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-2 py-2 text-right">
                                <x-ui.link href="{{ route('receipt', ['sale' => $sale->id]) }}" wire:navigate>View</x-ui.link>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-2 py-6 text-center text-neutral-500">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
