@extends('layouts.guest', ['title' => 'Weka Password Mpya'])

@section('content')
    <h4 class="fs-20 text-center">Weka Password Mpya</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label for="email" class="form-label">Barua pepe</label>
            <input class="form-control" type="email" name="email" id="email" value="{{ old('email', $request->email) }}" required autofocus>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password Mpya</label>
            <input class="form-control" type="password" name="password" id="password" required>
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Thibitisha Password</label>
            <input class="form-control" type="password" name="password_confirmation" id="password_confirmation" required>
        </div>

        <div class="mb-0 text-start">
            <button class="btn btn-primary w-100" type="submit">Badilisha Password</button>
        </div>
    </form>
@endsection
