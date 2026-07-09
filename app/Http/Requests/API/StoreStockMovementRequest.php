<?php

namespace App\Http\Requests\API;

use App\Enums\MovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockMovementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authentication handled by Sanctum middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_sku' => ['required', 'string', 'exists:products,sku'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'movement_type' => ['required', Rule::enum(MovementType::class)],
            'quantity' => ['required', 'integer', 'not_in:0'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'moved_by' => ['required', 'string', 'max:255'],
        ];
    }
}
