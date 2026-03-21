<div class="space-y-6">
    <div>
        <x-ui.heading level="h1" size="xl">Point of Sale</x-ui.heading>
        <x-ui.text class="mt-1 opacity-60">Scan barcode, manage cart, and checkout.</x-ui.text>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <x-ui.card size="xl">
            <x-ui.heading level="h2" size="sm" class="mb-3">Scan Barcode</x-ui.heading>
            <x-ui.text class="mb-3 text-xs opacity-60">Scan product barcodes here on POS. The Products page is only for managing product data.</x-ui.text>

            <div x-data="posScanner($wire)" class="space-y-3">
                <div id="barcode-reader" class="overflow-hidden rounded-lg border border-gray-300 dark:border-neutral-700"></div>

                <div class="grid gap-2 sm:flex">
                    <x-ui.button type="button" wire:ignore x-on:click="start()" class="w-full sm:w-auto">Start Camera</x-ui.button>
                    <x-ui.button type="button" variant="outline" wire:ignore x-on:click="stop()" class="w-full sm:w-auto">Stop Camera</x-ui.button>
                </div>

                <div class="flex items-center justify-between rounded-lg border border-gray-300 px-3 py-2 dark:border-neutral-700">
                    <x-ui.text class="text-xs">Auto restart after capture</x-ui.text>
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 px-2 py-1 text-xs dark:border-neutral-700"
                        x-on:click="autoRestart = !autoRestart"
                        x-text="autoRestart ? 'On' : 'Off'"
                    ></button>
                </div>

                <x-ui.text class="text-xs opacity-60" x-text="statusText"></x-ui.text>
            </div>

            <x-ui.separator class="my-4" />

            <x-ui.field>
                <x-ui.label>Search by Product Name</x-ui.label>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <x-ui.select
                        placeholder="Find a product..."
                        icon="magnifying-glass"
                        searchable
                        wire:model="searchProductId"
                    >
                        @forelse ($this->availableProducts as $product)
                            <x-ui.select.option value="{{ $product['id'] }}">
                                {{ $product['name'] }} ({{ $product['barcode'] }}) - ${{ number_format((float) $product['price'], 2) }}
                            </x-ui.select.option>
                        @empty
                            <x-ui.select.option value="" disabled>No products available</x-ui.select.option>
                        @endforelse
                    </x-ui.select>
                    <x-ui.button type="button" wire:click="addProductBySearch" class="w-full sm:w-auto">Add</x-ui.button>
                </div>
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>Manual Barcode Input</x-ui.label>
                <div
                    x-data
                    x-on:focus-pos-barcode-input.window="$nextTick(() => $refs.barcodeInput?.focus())"
                    class="space-y-2"
                >
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <x-ui.input
                            x-ref="barcodeInput"
                            wire:model.live.debounce.0ms="barcodeInput"
                            wire:keydown.enter.prevent="addProductByBarcode"
                            placeholder="Enter barcode"
                            autocomplete="off"
                        />
                        <x-ui.button type="button" wire:click="addProductByBarcode" class="w-full sm:w-auto">Add</x-ui.button>
                    </div>

                    @php
                        $scanFeedbackClasses = match ($scanMessageType) {
                            'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300',
                            'warning' => 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300',
                            'error' => 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/30 dark:text-rose-300',
                            default => 'border-gray-200 bg-gray-50 text-gray-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300',
                        };
                    @endphp

                    <div class="rounded-lg border px-3 py-2 text-sm {{ $scanFeedbackClasses }}" aria-live="polite">
                        {{ $scanMessage }}
                    </div>
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
                    <div class="flex flex-col gap-3 rounded-lg border border-gray-300 p-3 dark:border-neutral-700 sm:flex-row sm:items-center sm:justify-between" wire:key="cart-item-{{ $item['product_id'] }}">
                        <div>
                            <x-ui.text class="font-medium">{{ $item['name'] }}</x-ui.text>
                            <x-ui.text class="text-xs opacity-60">{{ $item['barcode'] }} · Sell: ${{ number_format((float) $item['price'], 2) }}</x-ui.text>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 sm:justify-end">
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
                <!-- Amount Summary Section -->
                <div class="rounded-lg bg-gray-50 p-3 dark:bg-neutral-800">
                    <div class="mb-2 flex items-center justify-between">
                        <x-ui.text class="text-xs opacity-60">Amount to Pay</x-ui.text>
                        <x-ui.text class="text-2xl font-bold text-green-600">{{ number_format($this->total, 2) }}</x-ui.text>
                    </div>
                </div>

                <x-ui.field>
                    <x-ui.label>Method</x-ui.label>
                    <x-ui.select wire:model="paymentMethod">
                        <x-ui.select.option value="Cash">Cash</x-ui.select.option>
                        <x-ui.select.option value="M-pesa">M-pesa</x-ui.select.option>
                        <x-ui.select.option value="Tigo-pesa">Tigo-pesa</x-ui.select.option>
                        <x-ui.select.option value="Bank">Bank</x-ui.select.option>
                    </x-ui.select>
                    <x-ui.error name="paymentMethod" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Amount Received (Payment)</x-ui.label>
                    <x-ui.input wire:model.live="paymentAmount" type="number" step="0.01" min="0" placeholder="0.00" />
                    <x-ui.text class="mt-1 text-xs opacity-60">Auto-filled with amount to pay. Edit to calculate change.</x-ui.text>
                    <x-ui.error name="paymentAmount" />
                </x-ui.field>

                <!-- Change Summary Section -->
                <div class="rounded-lg bg-blue-50 p-3 dark:bg-neutral-800">
                    <div class="flex items-center justify-between">
                        <x-ui.text class="font-medium">Change Due</x-ui.text>
                        <x-ui.text class="text-2xl font-bold text-blue-600">{{ number_format($this->change, 2) }}</x-ui.text>
                    </div>
                </div>

                <x-ui.button class="w-full" wire:click="checkout">Complete Checkout</x-ui.button>
            </div>
        </x-ui.card>
    </div>
</div>

@script
<script>
    window.posScanner = (wire) => ({
        scanner: null,
        currentCameraId: null,
        currentCameraLabel: '',
        lastDecodedText: null,
        lastDecodedAt: 0,
        audioContext: null,
        isProcessingScan: false,
        autoRestart: false,
        statusText: 'Camera is off',
        async start() {
            if (!window.Html5Qrcode) {
                await this.loadLibrary();
            }

            if (this.scanner) {
                this.statusText = this.currentCameraLabel
                    ? `Camera is already running (${this.currentCameraLabel})`
                    : 'Camera is already running';
                return;
            }

            this.statusText = 'Starting camera...';
            this.scanner = new Html5Qrcode('barcode-reader');

            try {
                const devices = await Html5Qrcode.getCameras();
                if (!devices.length) {
                    this.statusText = 'No camera detected on this device';
                    return;
                }

                const selectedCamera = this.pickBackCamera(devices);
                this.currentCameraId = selectedCamera.id;
                this.currentCameraLabel = selectedCamera.label || 'Back camera';

                await this.scanner.start(
                    this.currentCameraId,
                    { fps: 10, qrbox: { width: 250, height: 100 } },
                    (decodedText) => {
                        this.handleDecoded(decodedText, wire);
                    },
                    () => {}
                );

                this.statusText = `Back camera active (${this.currentCameraLabel})`;
            } catch (error) {
                this.statusText = this.formatScannerError(error);
                await this.stop();
            }
        },
        async stop() {
            if (!this.scanner) {
                this.statusText = 'Camera is off';
                return;
            }

            try {
                await this.scanner.stop();
            } catch (error) {
            }

            try {
                await this.scanner.clear();
            } catch (error) {
            }

            this.scanner = null;
            this.currentCameraId = null;
            this.currentCameraLabel = '';
            this.isProcessingScan = false;
            this.statusText = 'Camera is off';
        },
        async handleDecoded(decodedText, wire) {
            if (this.isProcessingScan || this.isDuplicateScan(decodedText)) {
                return;
            }

            this.isProcessingScan = true;
            this.statusText = 'Code captured';
            this.playScanBeep();
            await wire.call('scanBarcode', decodedText);

            await this.stop();

            if (this.autoRestart) {
                this.statusText = 'Captured. Restarting scanner...';
                await new Promise((resolve) => setTimeout(resolve, 700));
                await this.start();

                return;
            }

            this.statusText = 'Captured. Tap Start Camera for next item';
        },
        pickBackCamera(devices) {
            const backCameraKeywords = ['back', 'rear', 'environment', 'wide', 'ultra'];

            const preferredCamera = devices.find((device) => {
                const label = (device.label ?? '').toLowerCase();

                return backCameraKeywords.some((keyword) => label.includes(keyword));
            });

            return preferredCamera ?? devices[devices.length - 1];
        },
        isDuplicateScan(decodedText) {
            const now = Date.now();
            const cooldownMs = 1200;

            const isDuplicate = this.lastDecodedText === decodedText && (now - this.lastDecodedAt) < cooldownMs;

            if (!isDuplicate) {
                this.lastDecodedText = decodedText;
                this.lastDecodedAt = now;
            }

            return isDuplicate;
        },
        playScanBeep() {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;

            if (!AudioContextClass) {
                return;
            }

            if (!this.audioContext) {
                this.audioContext = new AudioContextClass();
            }

            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();

            oscillator.type = 'sine';
            oscillator.frequency.value = 1100;

            gainNode.gain.setValueAtTime(0.001, this.audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.12, this.audioContext.currentTime + 0.01);
            gainNode.gain.exponentialRampToValueAtTime(0.001, this.audioContext.currentTime + 0.12);

            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);

            oscillator.start();
            oscillator.stop(this.audioContext.currentTime + 0.12);
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
        formatScannerError(error) {
            const message = (error && error.message) ? error.message.toLowerCase() : '';

            if (message.includes('permission') || message.includes('notallowederror')) {
                return 'Camera permission denied. Allow camera access in browser settings.';
            }

            if (message.includes('secure context') || message.includes('https')) {
                return 'Camera requires HTTPS (or localhost). Open POS on https to scan.';
            }

            return 'Unable to start camera. Check browser permission and use HTTPS on phone.';
        },
    });
</script>
@endscript
