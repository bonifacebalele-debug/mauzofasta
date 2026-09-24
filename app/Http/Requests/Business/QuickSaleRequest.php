<?php

namespace App\Http\Requests\Business;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class QuickSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Order::class);
    }

    public function rules(): array
    {
        $businessId = current_business()->id;

        return [
            'customer_id' => [
                'nullable',
                Rule::exists('customers', 'id')->where('business_id', $businessId),
            ],
            'new_customer_name' => ['required_without:customer_id', 'nullable', 'string', 'max:255'],
            'new_customer_phone' => [
                'required_without:customer_id', 'nullable', 'string', 'max:20',
                Rule::unique('customers', 'phone')->where('business_id', $businessId),
            ],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->where('business_id', $businessId),
            ],
            'items.*.variant_id' => ['nullable', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],

            'payment_method' => ['nullable', Rule::in(['cash', 'mpesa', 'airtel_money', 'mixx_by_yas', 'halopesa', 'bank', 'other'])],
            'payment_amount' => ['nullable', 'integer', 'min:0'],
            'payment_reference' => ['nullable', 'string', 'max:255'],

            'discount_type' => ['nullable', Rule::in(['fixed', 'percentage'])],
            'discount_value' => ['nullable', 'integer', 'min:0'],

            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->filled('payment_amount') && ! $this->filled('payment_method')) {
                $validator->errors()->add('payment_method', 'Chagua njia ya malipo.');
            }
        });
    }
}
