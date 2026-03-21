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
                       <x-ui.label>Buy Price (Cost)</x-ui.label>
                       <x-ui.input wire:model="buyPrice" type="number" step="0.01" placeholder="0.00" />
                       <x-ui.error name="buyPrice" />
                   </x-ui.field>

                <x-ui.field>
                       <x-ui.label>Sell Price</x-ui.label>
                    <div x-data="productBarcodeScanner($wire)" class="space-y-2">
                        <div class="flex gap-2">
                            <x-ui.input wire:model="barcode" placeholder="e.g. 123456789012" />
                            <x-ui.button type="button" variant="outline" x-on:click="start()">Scan</x-ui.button>
                            <x-ui.button type="button" variant="outline" x-on:click="stop()">Stop</x-ui.button>
                        </div>
                        <div id="product-barcode-reader" class="overflow-hidden rounded-lg border border-gray-300 dark:border-neutral-700"></div>
                        <x-ui.text class="text-xs opacity-60" x-text="statusText"></x-ui.text>
                        <x-ui.button type="button" variant="outline" wire:click="generateBarcode">Generate</x-ui.button>
                    </div>
                   <x-ui.field>
                       <x-ui.label>Unit</x-ui.label>
                       <x-ui.select wire:model="unit">
                           <x-ui.select.option value="piece">Piece</x-ui.select.option>
                           <x-ui.select.option value="kg">Kilogram (kg)</x-ui.select.option>
                           <x-ui.select.option value="g">Gram (g)</x-ui.select.option>
                           <x-ui.select.option value="liter">Liter (L)</x-ui.select.option>
                           <x-ui.select.option value="ml">Milliliter (ml)</x-ui.select.option>
                           <x-ui.select.option value="box">Box</x-ui.select.option>
                           <x-ui.select.option value="bag">Bag</x-ui.select.option>
                           <x-ui.select.option value="pack">Pack</x-ui.select.option>
                       </x-ui.select>
                       <x-ui.error name="unit" />
                   </x-ui.field>

                   <x-ui.field>
                       <x-ui.label>Category</x-ui.label>
                       <x-ui.input wire:model="category" placeholder="e.g., Dairy, Beverages, Snacks" />
                       <x-ui.error name="category" />
                   </x-ui.field>

                   <x-ui.field>
                       <x-ui.label>Expiry Date</x-ui.label>
                       <x-ui.input wire:model="expireDate" type="date" />
                       <x-ui.error name="expireDate" />
                   </x-ui.field>
                    <x-ui.text class="text-xs opacity-60">Scan existing barcode or leave empty and click Save to auto-generate.</x-ui.text>
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
                           <th class="px-2 py-2">Category</th>
                        <th class="px-2 py-2 text-right">Price</th>
                           <th class="px-2 py-2">Unit</th>
                        <th class="px-2 py-2 text-right">Stock</th>
                        <th class="px-2 py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->products as $product)
                        <tr class="border-b border-gray-200 last:border-b-0 dark:border-neutral-800" wire:key="product-{{ $product->id }}">
                            <td class="px-2 py-2">{{ $product->name }}</td>
                            <td class="px-2 py-2 font-mono text-xs">{{ $product->barcode }}</td>
                               <td class="px-2 py-2"><x-ui.badge>{{ $product->category ?? 'N/A' }}</x-ui.badge></td>
                            <td class="px-2 py-2 text-right">${{ number_format((float) $product->price, 2) }}</td>
                               <td class="px-2 py-2">{{ $product->unit ?? 'piece' }}</td>
                            <td class="px-2 py-2 text-right">{{ $product->stock_quantity }}</td>
                            <td class="px-2 py-2">
                                <div class="flex justify-end gap-2">
                                    <x-ui.button
                                        size="xs"
                                        variant="soft"
                                        wire:click="previewBarcodeLabel({{ $product->id }})"
                                        x-on:click.debounce.50ms="$nextTick(() => $modal.open('barcode-modal'))"
                                    >
                                        Barcode
                                    </x-ui.button>
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

    <x-ui.modal id="barcode-modal" heading="Barcode Label" width="md">
        <div x-data="barcodeLabelRenderer(@entangle('previewBarcode'), @entangle('previewName'))" x-init="init()" class="space-y-4">
            <div class="rounded-lg border border-gray-300 bg-white p-4 text-center dark:border-neutral-700 dark:bg-neutral-900">
                <x-ui.text class="mb-2 text-sm font-medium" x-text="productName || 'Product'"></x-ui.text>
                <svg x-ref="barcodeSvg" class="mx-auto"></svg>
                <x-ui.text class="mt-2 font-mono text-xs" x-text="barcodeValue"></x-ui.text>
            </div>

            <x-ui.text class="text-xs opacity-60">
                Print this label or show it on another screen, then scan from POS camera.
            </x-ui.text>
        </div>

        <x-slot:footer>
            <x-ui.button variant="outline" x-on:click="$data.close()">Close</x-ui.button>
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-md bg-primary px-3 py-2 text-sm font-medium text-primary-content"
                x-on:click="window.printBarcodeLabel?.()"
            >
                Print Label
            </button>
        </x-slot:footer>
    </x-ui.modal>
</div>

@script
<script>
    window.barcodeLabelRenderer = (barcodeState, nameState) => ({
        barcodeValue: barcodeState,
        productName: nameState,
        async init() {
            await this.loadJsBarcode();
            this.$watch('barcodeValue', async () => {
                await this.renderBarcode();
            });

            window.printBarcodeLabel = () => this.printLabel();

            await this.renderBarcode();
        },
        async loadJsBarcode() {
            if (window.JsBarcode) {
                return;
            }

            await new Promise((resolve, reject) => {
                const existingScript = document.querySelector('script[data-jsbarcode]');
                if (existingScript) {
                    existingScript.addEventListener('load', resolve, { once: true });
                    return;
                }

                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js';
                script.async = true;
                script.dataset.jsbarcode = '1';
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        },
        async renderBarcode() {
            if (!this.barcodeValue || !this.$refs.barcodeSvg || !window.JsBarcode) {
                return;
            }

            window.JsBarcode(this.$refs.barcodeSvg, this.barcodeValue, {
                format: 'CODE128',
                displayValue: false,
                width: 2,
                height: 70,
                margin: 4,
            });
        },
        printLabel() {
            if (!this.$refs.barcodeSvg || !this.barcodeValue) {
                return;
            }

            const printWindow = window.open('', '_blank', 'width=420,height=600');
            if (!printWindow) {
                return;
            }

            const svgMarkup = this.$refs.barcodeSvg.outerHTML;

            printWindow.document.write(`
                <html>
                    <head>
                        <title>Barcode Label</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 0; padding: 24px; }
                            .label { border: 1px solid #ddd; border-radius: 8px; padding: 16px; text-align: center; }
                            .name { font-size: 14px; font-weight: 600; margin-bottom: 12px; }
                            .code { font-family: monospace; font-size: 12px; margin-top: 10px; }
                            svg { width: 100%; height: auto; }
                        </style>
                    </head>
                    <body>
                        <div class="label">
                            <div class="name">${this.productName || 'Product'}</div>
                            ${svgMarkup}
                            <div class="code">${this.barcodeValue}</div>
                        </div>
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        },
    });

    window.productBarcodeScanner = (wire) => ({
        scanner: null,
        statusText: 'Scanner off',
        async start() {
            if (!this.canUseCamera()) {
                this.statusText = 'Camera needs HTTPS (or localhost). Open app with https:// URL.';
                return;
            }

            if (!window.Html5Qrcode) {
                await this.loadScannerLibrary();
            }

            if (this.scanner) {
                this.statusText = 'Scanner already running';
                return;
            }

            this.scanner = new Html5Qrcode('product-barcode-reader');
            this.statusText = 'Starting scanner...';

            try {
                const onDecode = async (decodedText) => {
                    await wire.call('setScannedBarcode', decodedText);
                    this.statusText = `Captured: ${decodedText}`;
                    await this.stop();
                };

                const config = { fps: 10, qrbox: { width: 260, height: 100 } };

                try {
                    await this.scanner.start(
                        { facingMode: { exact: 'environment' } },
                        config,
                        onDecode,
                        () => {}
                    );

                    this.statusText = 'Back camera active';

                    return;
                } catch (error) {
                }

                const devices = await Html5Qrcode.getCameras();
                if (!devices.length) {
                    this.statusText = 'No camera detected';
                    return;
                }

                const selectedCamera = this.pickBackCamera(devices);

                await this.scanner.start(
                    selectedCamera.id,
                    config,
                    onDecode,
                    () => {}
                );

                this.statusText = `Back camera active (${selectedCamera.label || 'camera'})`;
            } catch (error) {
                this.statusText = this.getScannerErrorMessage(error);
                await this.stop();
            }
        },
        async stop() {
            if (!this.scanner) {
                this.statusText = 'Scanner off';
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
        },
        pickBackCamera(devices) {
            const backCameraKeywords = ['back', 'rear', 'environment', 'wide', 'ultra'];

            const preferredCamera = devices.find((device) => {
                const label = (device.label ?? '').toLowerCase();

                return backCameraKeywords.some((keyword) => label.includes(keyword));
            });

            return preferredCamera ?? devices[devices.length - 1];
        },
        async loadScannerLibrary() {
            await new Promise((resolve, reject) => {
                const existingScript = document.querySelector('script[data-product-scanner]');
                if (existingScript) {
                    existingScript.addEventListener('load', resolve, { once: true });
                    return;
                }

                const script = document.createElement('script');
                script.src = 'https://unpkg.com/html5-qrcode/html5-qrcode.min.js';
                script.async = true;
                script.dataset.productScanner = '1';
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        },
        canUseCamera() {
            return window.isSecureContext || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
        },
        getScannerErrorMessage(error) {
            const message = (error && error.message ? error.message : '').toLowerCase();

            if (message.includes('notallowederror') || message.includes('permission') || message.includes('denied')) {
                return 'Camera blocked. Allow camera permission in browser/site settings.';
            }

            if (message.includes('notfounderror') || message.includes('no cameras')) {
                return 'No camera found on this device.';
            }

            if (message.includes('secure context') || message.includes('https')) {
                return 'Camera requires HTTPS (or localhost).';
            }

            if (message.includes('notreadableerror') || message.includes('trackstarterror')) {
                return 'Camera is busy. Close other camera apps/tabs and try again.';
            }

            return `Unable to start scanner: ${error?.message || 'Unknown error'}`;
        },
    });
</script>
@endscript
