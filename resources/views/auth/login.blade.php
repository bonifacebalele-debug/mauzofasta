@extends('layouts.guest', ['title' => 'Ingia'])

@section('content')
    <h4 class="fs-20 text-center">Ingia</h4>
    <p class="text-muted text-center mb-4">Weka barua pepe na password yako kuingia kwenye akaunti yako.</p>

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

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Barua pepe</label>
            <input class="form-control" type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="mb-3">
            <a href="{{ route('password.request') }}" class="text-muted float-end"><small>Umesahau password?</small></a>
            <label for="password" class="form-label">Password</label>
            <input class="form-control" type="password" name="password" id="password" required>
        </div>

        <div class="mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="remember" id="remember">
                <label class="form-check-label" for="remember">Nikumbuke</label>
            </div>
        </div>

        <div class="mb-0 text-start">
            <button class="btn btn-primary w-100" type="submit">Ingia</button>
        </div>
    </form>

    <div class="text-center mt-4">
        <p class="text-muted mb-0">Huna akaunti?
            <a href="{{ route('business.register') }}" class="fw-bold text-decoration-underline">Anza Bure</a>
        </p>
    </div>
@endsection
