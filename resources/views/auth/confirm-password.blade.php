@extends('layouts.guest', ['title' => 'Thibitisha Password'])

@section('content')
    <h4 class="fs-20 text-center">Thibitisha Password</h4>
    <p class="text-muted text-center mb-4">Hii ni eneo salama la programu. Tafadhali thibitisha password yako kabla ya kuendelea.</p>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input class="form-control" type="password" name="password" id="password" required autofocus>
        </div>

        <div class="mb-0 text-start">
            <button class="btn btn-primary w-100" type="submit">Thibitisha</button>
        </div>
    </form>
@endsection
