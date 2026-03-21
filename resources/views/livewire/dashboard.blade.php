<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <x-ui.heading level="h1" size="xl">POS Dashboard</x-ui.heading>
            <x-ui.text class="mt-1 opacity-60">{{ $this->dashboardSummaryLabel }}</x-ui.text>
        </div>
        <x-ui.button href="{{ route('pos') }}" icon="qr-code" wire:navigate class="w-full sm:w-auto">Open POS</x-ui.button>
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
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <x-ui.heading level="h3" size="sm">Today Transactions</x-ui.heading>
            <x-ui.link href="{{ route('transactions') }}" wire:navigate>View all</x-ui.link>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-sm">
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

    <div class="grid gap-4 lg:grid-cols-2">
        <x-ui.card size="xl">
            <div class="mb-4 flex items-center justify-between">
                <x-ui.heading level="h3" size="sm">Trending Products Today (Top 10)</x-ui.heading>
                <x-ui.text class="text-xs opacity-60">Today: {{ $this->currentDateLabel }}</x-ui.text>
            </div>

            <div class="space-y-3">
                @forelse ($this->todayTopProducts as $index => $product)
                    <div wire:key="top-product-{{ $product['id'] }}">
                        <div class="mb-1 flex flex-col gap-1 text-sm sm:flex-row sm:items-center sm:justify-between">
                            <x-ui.text class="break-words">#{{ $index + 1 }} {{ $product['name'] }}</x-ui.text>
                            <x-ui.text class="font-medium">{{ $product['sold_quantity'] }} sold</x-ui.text>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-neutral-800">
                            <div class="h-2 rounded-full bg-blue-600" style="width: {{ $product['bar_percentage'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <x-ui.text class="text-sm opacity-60">No products sold today.</x-ui.text>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card size="xl">
            <div class="mb-4 flex items-center justify-between">
                <x-ui.heading level="h3" size="sm">Empty Stock Products (Top 10)</x-ui.heading>
                <x-ui.text class="text-xs opacity-60">Today: {{ $this->currentDateLabel }}</x-ui.text>
            </div>

            <div class="space-y-3">
                @forelse ($this->emptyStockProducts as $product)
                    <div wire:key="empty-stock-product-{{ $product['id'] }}">
                        <div class="mb-1 flex flex-col gap-1 text-sm sm:flex-row sm:items-center sm:justify-between">
                            <x-ui.text class="break-words">{{ $product['name'] }}</x-ui.text>
                            <x-ui.text class="font-medium">{{ $product['sold_today'] }} sold today</x-ui.text>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-neutral-800">
                            <div class="h-2 rounded-full bg-red-600" style="width: {{ $product['bar_percentage'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <x-ui.text class="text-sm opacity-60">No empty stock products.</x-ui.text>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</div>
