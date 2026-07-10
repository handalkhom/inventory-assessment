<?php

namespace App\Livewire;

use App\Enums\MovementType;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockMovementService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class StockAdjustmentManager extends Component
{
    #[Validate('required|integer|exists:products,id')]
    public $productId = null;

    #[Validate('required|integer|exists:warehouses,id')]
    public $warehouseId = null;

    #[Validate('required|integer|min:1')]
    public $quantity = 1;

    #[Validate('nullable|string')]
    public $notes = '';

    #[On('product-selected')]
    public function selectProduct($productId)
    {
        $this->productId = $productId;
        $this->resetQuantity();
    }

    public function updatedWarehouseId()
    {
        $this->resetQuantity();
    }

    public function updatedProductId()
    {
        $this->resetQuantity();
    }

    private function resetQuantity()
    {
        $this->quantity = 1;
        $this->resetValidation('quantity');
    }

    #[Computed]
    public function availableStock()
    {
        if (!$this->productId || !$this->warehouseId) {
            return 0;
        }

        $product = Product::find($this->productId);
        if (!$product) {
            return 0;
        }

        return $product->warehouses()
            ->where('warehouse_id', $this->warehouseId)
            ->first()?->pivot->quantity_on_hand ?? 0;
    }

    public function submit(StockMovementService $service)
    {
        $this->validate();

        // Additional server-side validation mirroring the Alpine one
        if ($this->quantity > $this->availableStock) {
             throw ValidationException::withMessages([
                 'quantity' => 'Quantity cannot exceed current stock.',
             ]);
        }

        $product = Product::find($this->productId);

        try {
            $service->createMovement([
                'product_sku' => $product->sku,
                'warehouse_id' => $this->warehouseId,
                'movement_type' => MovementType::ADJUSTMENT->value,
                // Negative quantity for outbound adjustments
                'quantity' => -abs($this->quantity),
                'notes' => $this->notes,
                'moved_by' => auth()->check() ? auth()->user()->name : 'System User',
            ]);

            // Dispatch event to alpine for toast
            $this->dispatch('stock-adjusted');

            // Reset form
            $this->reset([
                            'notes',
                            'productId',
                            'warehouseId',
                        ]);

            $this->quantity = 1;

        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.stock-adjustment-manager', [
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
