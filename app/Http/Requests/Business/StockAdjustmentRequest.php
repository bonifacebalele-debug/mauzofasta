<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('adjustStock', $this->route('product'));
    }

    public function rules(): array
    {
        return [
            'variant_id' => [
                'nullable',
                Rule::exists('product_variants', 'id')->where('product_id', $this->route('product')->id),
            ],
            'new_quantity' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}
