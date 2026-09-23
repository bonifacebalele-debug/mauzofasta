@extends('layouts.guest', ['title' => 'Umesahau Password'])

@section('content')
    <h4 class="fs-20 text-center">Umesahau Password?</h4>
    <p class="text-muted text-center mb-4">Weka barua pepe yako, tutakutumia link ya kubadilisha password.</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Barua pepe</label>
            <input class="form-control" type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="mb-0 text-start">
            <button class="btn btn-primary w-100" type="submit">Tuma Link ya Kubadilisha Password</button>
        </div>
    </form>

    <div class="text-center mt-4">
        <a href="{{ route('login') }}" class="text-muted"><small>Rudi kuingia</small></a>
    </div>
@endsection
