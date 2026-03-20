<div class="space-y-6">
    <div>
        <x-ui.heading level="h1" size="xl">Point of Sale</x-ui.heading>
        <x-ui.text class="mt-1 opacity-60">Scan barcode, manage cart, and checkout.</x-ui.text>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <x-ui.card size="xl">
            <x-ui.heading level="h2" size="sm" class="mb-3">Scan Barcode</x-ui.heading>

            <div x-data="posScanner($wire)" class="space-y-3">
                <div id="barcode-reader" class="overflow-hidden rounded-lg border border-gray-300 dark:border-neutral-700"></div>

                <div class="flex gap-2">
                    <x-ui.button type="button" wire:ignore x-on:click="start()">Start Camera</x-ui.button>
                    <x-ui.button type="button" variant="outline" wire:ignore x-on:click="stop()">Stop Camera</x-ui.button>
                </div>
            </div>

            <x-ui.separator class="my-4" />

            <x-ui.field>
                <x-ui.label>Manual Barcode Input</x-ui.label>
                <div class="flex gap-2">
                    <x-ui.input wire:model="barcodeInput" placeholder="Enter barcode" />
                    <x-ui.button type="button" wire:click="addProductByBarcode">Add</x-ui.button>
                </div>
            </x-ui.field>
        </x-ui.card>

        <x-ui.card size="xl">
            <div class="mb-3 flex items-center justify-between">
                <x-ui.heading level="h2" size="sm">Cart</x-ui.heading>
                <x-ui.badge>{{ $this->cartCount }} item(s)</x-ui.badge>
            </div>

            @error('cart')
                <x-ui.alerts variant="error" icon="exclamation-triangle">{{ $message }}</x-ui.alerts>
            @enderror

            <div class="space-y-2">
                @forelse ($cart as $item)
                    <div class="flex items-center justify-between rounded-lg border border-gray-300 p-3 dark:border-neutral-700" wire:key="cart-item-{{ $item['product_id'] }}">
                        <div>
                            <x-ui.text class="font-medium">{{ $item['name'] }}</x-ui.text>
                            <x-ui.text class="text-xs opacity-60">{{ $item['barcode'] }} · ${{ number_format((float) $item['price'], 2) }}</x-ui.text>
                        </div>

                        <div class="flex items-center gap-2">
                            <x-ui.button size="xs" variant="outline" wire:click="decreaseQuantity({{ $item['product_id'] }})">-</x-ui.button>
                            <span class="w-6 text-center text-sm">{{ $item['quantity'] }}</span>
                            <x-ui.button size="xs" variant="outline" wire:click="increaseQuantity({{ $item['product_id'] }})">+</x-ui.button>
                            <x-ui.button size="xs" color="red" variant="none" wire:click="removeItem({{ $item['product_id'] }})">Remove</x-ui.button>
                        </div>
                    </div>
                @empty
                    <x-ui.text class="text-sm opacity-60">Cart is empty.</x-ui.text>
                @endforelse
            </div>

            <x-ui.separator class="my-4" />

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <x-ui.text class="font-medium">Total</x-ui.text>
                    <x-ui.text class="font-semibold">${{ number_format($this->total, 2) }}</x-ui.text>
                </div>

                <x-ui.field>
                    <x-ui.label>Payment (Cash)</x-ui.label>
                    <x-ui.input wire:model="paymentAmount" type="number" step="0.01" min="0" placeholder="0.00" />
                    <x-ui.error name="paymentAmount" />
                </x-ui.field>

                <div class="flex items-center justify-between">
                    <x-ui.text class="font-medium">Change</x-ui.text>
                    <x-ui.text class="font-semibold">${{ number_format($this->change, 2) }}</x-ui.text>
                </div>

                <x-ui.button class="w-full" wire:click="checkout">Checkout</x-ui.button>
            </div>
        </x-ui.card>
    </div>
</div>

@script
<script>
    window.posScanner = (wire) => ({
        scanner: null,
        currentCameraId: null,
        async start() {
            if (!window.Html5Qrcode) {
                await this.loadLibrary();
            }

            if (this.scanner) {
                return;
            }

            this.scanner = new Html5Qrcode('barcode-reader');

            const devices = await Html5Qrcode.getCameras();
            if (!devices.length) {
                return;
            }

            this.currentCameraId = devices[0].id;

            await this.scanner.start(
                this.currentCameraId,
                { fps: 10, qrbox: { width: 250, height: 100 } },
                (decodedText) => {
                    wire.call('scanBarcode', decodedText);
                },
                () => {}
            );
        },
        async stop() {
            if (!this.scanner) {
                return;
            }

            await this.scanner.stop();
            await this.scanner.clear();
            this.scanner = null;
        },
        async loadLibrary() {
            await new Promise((resolve, reject) => {
                const existingScript = document.querySelector('script[data-pos-scanner]');
                if (existingScript) {
                    existingScript.addEventListener('load', resolve, { once: true });
                    return;
                }

                const script = document.createElement('script');
                script.src = 'https://unpkg.com/html5-qrcode/html5-qrcode.min.js';
                script.async = true;
                script.dataset.posScanner = '1';
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        },
    });
</script>
@endscript
