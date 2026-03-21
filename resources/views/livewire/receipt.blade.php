<div id="receipt-print-root" class="mx-auto max-w-2xl space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <x-ui.heading level="h1" size="xl">Receipt #{{ $sale->id }}</x-ui.heading>
            <x-ui.text class="mt-1 opacity-60">{{ $sale->created_at->format('M d, Y H:i') }}</x-ui.text>
        </div>

        <div class="no-print grid gap-2 sm:flex">
            <x-ui.button variant="outline" type="button" onclick="window.print()" class="w-full sm:w-auto">Print</x-ui.button>
            <x-ui.button href="{{ route('pos') }}" wire:navigate class="w-full sm:w-auto">New Sale</x-ui.button>
        </div>
    </div>

    <x-ui.card size="xl" class="receipt-compact space-y-4">
        <div class="grid gap-2 sm:grid-cols-2">
            <x-ui.text>Cashier: <strong>{{ $sale->user?->name ?? 'N/A' }}</strong></x-ui.text>
            <x-ui.text>Payment Method: <strong>{{ $sale->payment_method ?? 'Cash' }}</strong></x-ui.text>
            <x-ui.text>Total: <strong>${{ number_format((float) $sale->total_amount, 2) }}</strong></x-ui.text>
            <x-ui.text>Payment: <strong>${{ number_format((float) $sale->payment_received, 2) }}</strong></x-ui.text>
            <x-ui.text>Profit: <strong>${{ number_format((float) $sale->items->sum('profit_amount'), 2) }}</strong></x-ui.text>
            <x-ui.text colspan="2">Change: <strong>${{ number_format((float) $sale->change_given, 2) }}</strong></x-ui.text>
        </div>

        <x-ui.separator />

        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-300 dark:border-neutral-700">
                        <th class="px-2 py-2">Item</th>
                        <th class="px-2 py-2 text-right">Qty</th>
                        <th class="px-2 py-2 text-right">Sell Price</th>
                        <th class="px-2 py-2 text-right">Subtotal</th>
                        <th class="px-2 py-2 text-right">Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr class="border-b border-gray-200 last:border-b-0 dark:border-neutral-800" wire:key="receipt-item-{{ $item->id }}">
                            <td class="px-2 py-2">{{ $item->product?->name ?? 'Deleted product' }}</td>
                            <td class="px-2 py-2 text-right">{{ $item->quantity }}</td>
                            <td class="px-2 py-2 text-right">${{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="px-2 py-2 text-right">${{ number_format((float) $item->line_total, 2) }}</td>
                            <td class="px-2 py-2 text-right">${{ number_format((float) $item->profit_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>

    @once
        <style>
            @media print {
                @page {
                    size: 58mm auto;
                    margin: 2mm;
                }

                html,
                body {
                    width: 58mm;
                    margin: 0 !important;
                    padding: 0 !important;
                    background: #fff !important;
                }

                body * {
                    visibility: hidden;
                }

                #receipt-print-root,
                #receipt-print-root * {
                    visibility: visible;
                }

                #receipt-print-root {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 58mm;
                    max-width: 58mm;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                #receipt-print-root,
                #receipt-print-root * {
                    color: #000 !important;
                    opacity: 1 !important;
                    text-shadow: none !important;
                    box-shadow: none !important;
                    background: transparent !important;
                }

                #receipt-print-root .overflow-x-auto {
                    overflow: visible !important;
                }

                #receipt-print-root table {
                    width: 100% !important;
                    min-width: 0 !important;
                    table-layout: fixed;
                    font-size: 11px;
                }

                #receipt-print-root th,
                #receipt-print-root td {
                    padding: 2px 1px !important;
                    border-color: #000 !important;
                    word-break: break-word;
                }

                #receipt-print-root .receipt-compact {
                    font-size: 12px;
                    line-height: 1.35;
                    padding: 8px !important;
                }

                .no-print {
                    display: none !important;
                }
            }
        </style>
    @endonce
</div>
