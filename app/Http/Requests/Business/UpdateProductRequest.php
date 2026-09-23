<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    public function rules(): array
    {
        $businessId = current_business()->id;
        $productId = $this->route('product')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('business_id', $businessId),
            ],
            'sku' => [
                'nullable', 'string', 'max:100',
                Rule::unique('products', 'sku')->where('business_id', $businessId)->ignore($productId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'selling_price' => ['required', 'integer', 'min:0'],
            'cost_price' => ['nullable', 'integer', 'min:0'],
            // stock_quantity is intentionally not editable here — every
            // stock change goes through the ledger via StockService
            // (spec §24), never a direct field edit.
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_featured' => ['boolean'],
            'status' => ['required', Rule::in(['active', 'archived'])],
        ];
    }
}
