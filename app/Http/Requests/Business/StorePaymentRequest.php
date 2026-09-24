<?php

namespace App\Http\Requests\Business;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Payment::class);
    }

    public function rules(): array
    {
        $businessId = current_business()->id;

        return [
            'order_id' => [
                'required',
                Rule::exists('orders', 'id')->where('business_id', $businessId),
            ],
            'amount' => ['required', 'integer', 'min:1'],
            'method' => ['required', Rule::in(['cash', 'mpesa', 'airtel_money', 'mixx_by_yas', 'halopesa', 'bank', 'other'])],
            'reference' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('order_id') || ! $this->filled('amount')) {
                return;
            }

            $order = Order::find($this->order_id);

            if ($order && (int) $this->amount > $order->balance()) {
                $validator->errors()->add('amount', 'Kiasi kimezidi deni lililobaki.');
            }
        });
    }
}
