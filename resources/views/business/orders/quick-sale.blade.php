@extends('layouts.vertical', ['title' => 'Uza'])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => 'Uza (Fast Sale)', 'sub_title' => 'Mauzo'])

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('orders.quickSale.store') }}" id="quick-sale-form">
        @csrf

        <div class="card">
            <div class="card-body">
                <h6 class="text-uppercase text-muted fs-13 mb-3">1. Mteja</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Chagua Mteja Aliyepo</label>
                        <select class="form-select" name="customer_id">
                            <option value="">-- Mteja Mpya --</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                    {{ $customer->name }} ({{ $customer->phone }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Jina la Mteja Mpya</label>
                        <input class="form-control" type="text" name="new_customer_name" value="{{ old('new_customer_name') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Simu ya Mteja Mpya</label>
                        <input class="form-control" type="tel" name="new_customer_phone" value="{{ old('new_customer_phone') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h6 class="text-uppercase text-muted fs-13 mb-3">2. Bidhaa</h6>

                <table class="table" id="items-table">
                    <thead>
                        <tr>
                            <th>Bidhaa</th>
                            <th style="width:120px;">Idadi</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="item-row">
                            <td>
                                <select class="form-select" name="items[0][product_id]" required>
                                    <option value="">-- Chagua Bidhaa --</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }} ({{ money($product->selling_price) }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input class="form-control" type="number" name="items[0][quantity]" value="1" min="1" required>
                            </td>
                            <td>
                                <button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Ondoa">
                                    <i class="ri-close-line"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <button type="button" class="btn btn-outline-primary btn-sm" id="add-row">+ Ongeza Bidhaa Nyingine</button>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h6 class="text-uppercase text-muted fs-13 mb-3">3. Malipo (si lazima kama bado hajalipa)</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Njia ya Malipo</label>
                        <select class="form-select" name="payment_method">
                            <option value="">-- Hajalipa --</option>
                            <option value="cash">Cash</option>
                            <option value="mpesa">M-Pesa</option>
                            <option value="airtel_money">Airtel Money</option>
                            <option value="mixx_by_yas">Mixx by Yas</option>
                            <option value="halopesa">HaloPesa</option>
                            <option value="bank">Benki</option>
                            <option value="other">Nyingine</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kiasi Alicholipa (Tsh)</label>
                        <input class="form-control" type="number" min="0" name="payment_amount" value="{{ old('payment_amount') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kumbukumbu ya Malipo (si lazima)</label>
                        <input class="form-control" type="text" name="payment_reference" value="{{ old('payment_reference') }}">
                    </div>
                </div>

                @if ($canApplyDiscount)
                    <h6 class="text-uppercase text-muted fs-13 mb-3 mt-2">Punguzo (si lazima)</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Aina ya Punguzo</label>
                            <select class="form-select" name="discount_type">
                                <option value="">Hakuna</option>
                                <option value="fixed">Kiasi (Tsh)</option>
                                <option value="percentage">Asilimia (%)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Thamani ya Punguzo</label>
                            <input class="form-control" type="number" min="0" name="discount_value" value="{{ old('discount_value') }}">
                        </div>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Maelezo (si lazima)</label>
                    <textarea class="form-control" name="notes" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg mt-2">Kamilisha Mauzo</button>
    </form>
@endsection

@section('script')
    <script>
        (function () {
            let rowIndex = 1;
            const tbody = document.querySelector('#items-table tbody');
            const productOptions = document.querySelector('.item-row select[name^="items"]').innerHTML;

            document.getElementById('add-row').addEventListener('click', function () {
                const row = document.createElement('tr');
                row.className = 'item-row';
                row.innerHTML = `
                    <td><select class="form-select" name="items[${rowIndex}][product_id]" required>${productOptions}</select></td>
                    <td><input class="form-control" type="number" name="items[${rowIndex}][quantity]" value="1" min="1" required></td>
                    <td><button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Ondoa"><i class="ri-close-line"></i></button></td>
                `;
                tbody.appendChild(row);
                rowIndex++;
            });

            tbody.addEventListener('click', function (e) {
                const button = e.target.closest('.remove-row');
                if (button && tbody.querySelectorAll('.item-row').length > 1) {
                    button.closest('.item-row').remove();
                }
            });
        })();
    </script>
@endsection
