@extends('layouts.vertical', ['title' => 'Ongeza Mteja'])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => 'Ongeza Mteja', 'sub_title' => 'Wateja'])

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('customers.store') }}">
                @include('business.customers._form')
            </form>
        </div>
    </div>
@endsection
