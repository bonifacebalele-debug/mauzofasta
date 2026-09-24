<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('customer'));
    }

    public function rules(): array
    {
        $businessId = current_business()->id;
        $customerId = $this->route('customer')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required', 'string', 'max:20',
                Rule::unique('customers', 'phone')->where('business_id', $businessId)->ignore($customerId),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'region' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:255'],
        ];
    }
}
