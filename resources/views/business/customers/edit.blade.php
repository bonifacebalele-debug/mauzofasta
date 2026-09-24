@extends('layouts.vertical', ['title' => 'Hariri Mteja'])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => 'Hariri Mteja', 'sub_title' => 'Wateja'])

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('customers.update', $customer) }}">
                @method('PUT')
                @include('business.customers._form')
            </form>
        </div>
    </div>
@endsection
