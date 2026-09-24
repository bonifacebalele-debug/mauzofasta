@extends('layouts.vertical', ['title' => 'Rekodi Malipo'])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => 'Rekodi Malipo', 'sub_title' => 'Malipo'])

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('payments.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Chagua Agizo Lenye Deni</label>
                    <select class="form-select" name="order_id" required>
                        <option value="">-- Chagua Agizo --</option>
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}" @selected(optional($preselectedOrder)->id === $order->id)>
                                {{ $order->order_number }} — {{ $order->customer->name }} (Deni: {{ money($order->balance()) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kiasi (Tsh)</label>
                        <input class="form-control" type="number" min="1" name="amount" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Njia ya Malipo</label>
                        <select class="form-select" name="method" required>
                            <option value="cash">Cash</option>
                            <option value="mpesa">M-Pesa</option>
                            <option value="airtel_money">Airtel Money</option>
                            <option value="mixx_by_yas">Mixx by Yas</option>
                            <option value="halopesa">HaloPesa</option>
                            <option value="bank">Benki</option>
                            <option value="other">Nyingine</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kumbukumbu ya Malipo (si lazima)</label>
                    <input class="form-control" type="text" name="reference">
                </div>

                <button type="submit" class="btn btn-primary">Hifadhi Malipo</button>
                <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Ghairi</a>
            </form>
        </div>
    </div>
@endsection
