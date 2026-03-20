<div class="space-y-6">
    <div class="flex items-center justify-between gap-3">
        <div>
            <x-ui.heading level="h1" size="xl">POS Dashboard</x-ui.heading>
            <x-ui.text class="mt-1 opacity-60">Daily sales summary.</x-ui.text>
        </div>
        <x-ui.button href="{{ route('pos') }}" icon="qr-code" wire:navigate>Open POS</x-ui.button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-ui.card size="xl">
            <x-ui.text class="text-sm opacity-60">Today Total Sales</x-ui.text>
            <x-ui.heading level="h2" size="lg" class="mt-2">${{ number_format($this->todaySalesTotal, 2) }}</x-ui.heading>
        </x-ui.card>

        <x-ui.card size="xl">
            <x-ui.text class="text-sm opacity-60">Today Transactions</x-ui.text>
            <x-ui.heading level="h2" size="lg" class="mt-2">{{ $this->todayTransactionsCount }}</x-ui.heading>
        </x-ui.card>
    </div>

    <x-ui.card size="xl">
        <div class="mb-4 flex items-center justify-between">
            <x-ui.heading level="h3" size="sm">Recent Transactions</x-ui.heading>
            <x-ui.link href="{{ route('transactions') }}" wire:navigate>View all</x-ui.link>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-300 dark:border-neutral-700">
                        <th class="px-2 py-2 font-medium">Sale #</th>
                        <th class="px-2 py-2 font-medium">Cashier</th>
                        <th class="px-2 py-2 font-medium text-right">Total</th>
                        <th class="px-2 py-2 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->recentSales as $sale)
                        <tr class="border-b border-gray-200 last:border-b-0 dark:border-neutral-800" wire:key="sale-{{ $sale->id }}">
                            <td class="px-2 py-2">#{{ $sale->id }}</td>
                            <td class="px-2 py-2">{{ $sale->user?->name ?? 'N/A' }}</td>
                            <td class="px-2 py-2 text-right">${{ number_format((float) $sale->total_amount, 2) }}</td>
                            <td class="px-2 py-2">{{ $sale->created_at->format('M d, H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-2 py-6 text-center text-neutral-500">No sales yet today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
