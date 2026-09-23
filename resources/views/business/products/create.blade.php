@extends('layouts.vertical', ['title' => 'Ongeza Bidhaa'])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => 'Ongeza Bidhaa', 'sub_title' => 'Bidhaa'])

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                @include('business.products._form')
            </form>
        </div>
    </div>
@endsection
