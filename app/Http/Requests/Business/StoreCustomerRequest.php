<?php

namespace App\Http\Requests\Business;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Customer::class);
    }

    public function rules(): array
    {
        $businessId = current_business()->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required', 'string', 'max:20',
                Rule::unique('customers', 'phone')->where('business_id', $businessId),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'region' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:255'],
        ];
    }
}
