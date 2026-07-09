<div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow-md border border-gray-100">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Stock Adjustment</h2>

    <form wire:submit="submit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Product Selection -->
            <div>
                <label for="productId" class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                <select id="productId" wire:model.live="productId" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">Select a product...</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                    @endforeach
                </select>
                @error('productId') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Warehouse Selection -->
            <div>
                <label for="warehouseId" class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                <select id="warehouseId" wire:model.live="warehouseId" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">Select a warehouse...</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
                @error('warehouseId') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
            <div class="text-sm text-gray-600 mb-1">Current Stock</div>
            <div class="text-2xl font-semibold text-gray-900" wire:loading.class="opacity-50" wire:target="productId, warehouseId">
                {{ $this->availableStock }} <span class="text-base font-normal text-gray-500">units</span>
            </div>
        </div>

        <!-- Alpine Component for Quantity -->
        <div 
            x-data="{ 
                qty: @entangle('quantity'), 
                maxStock: @entangle('availableStock').live,
                
                increment() {
                    if (this.qty < this.maxStock) this.qty++;
                },
                decrement() {
                    if (this.qty > 1) this.qty--;
                },
                validateInput() {
                    if (this.qty > this.maxStock) {
                        this.qty = this.maxStock;
                    }
                    if (this.qty < 1 || isNaN(this.qty) || this.qty === '') {
                        this.qty = 1;
                    }
                }
            }" 
            class="space-y-2"
        >
            <label for="quantity" class="block text-sm font-medium text-gray-700">Adjustment Quantity</label>
            <div class="flex items-center space-x-3">
                <button type="button" @click="decrement" :disabled="qty <= 1 || maxStock === 0" class="w-10 h-10 flex items-center justify-center rounded-md border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                    </svg>
                </button>
                
                <input 
                    type="number" 
                    id="quantity" 
                    x-model.number.debounce.500ms="qty" 
                    @input="validateInput"
                    :disabled="maxStock === 0"
                    class="w-24 text-center border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-semibold text-gray-900 disabled:opacity-50 disabled:bg-gray-100"
                    min="1"
                    :max="maxStock"
                >

                <button type="button" @click="increment" :disabled="qty >= maxStock || maxStock === 0" class="w-10 h-10 flex items-center justify-center rounded-md border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <p class="text-xs text-gray-500">Amount to remove from current stock.</p>
            @error('quantity') <span class="text-red-500 text-xs block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Reason for Adjustment</label>
            <textarea id="notes" wire:model="notes" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="e.g. Expired, Damaged, Quality Check"></textarea>
            @error('notes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="pt-4 flex items-center justify-between">
            <button 
                type="submit" 
                class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition-colors"
                wire:loading.attr="disabled"
            >
                <svg wire:loading wire:target="submit" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Submit Adjustment</span>
            </button>
            
            <!-- Alpine Toast Notification -->
            <div 
                x-data="{ show: false }" 
                x-on:stock-adjusted.window="show = true; setTimeout(() => show = false, 3000)"
                x-show="show" 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform translate-y-2"
                class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded relative flex items-center shadow-sm"
                style="display: none;"
            >
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span class="block sm:inline">Stock adjusted successfully!</span>
            </div>
        </div>
    </form>
</div>
