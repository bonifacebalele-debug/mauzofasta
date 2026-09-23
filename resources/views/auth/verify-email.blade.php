@extends('layouts.guest', ['title' => 'Thibitisha Barua Pepe'])

@section('content')
    <h4 class="fs-20 text-center">Thibitisha Barua Pepe Yako</h4>
    <p class="text-muted text-center mb-4">
        Asante kwa kujiunga! Kabla ya kuanza, je unaweza kuthibitisha barua pepe yako kwa kubofya link tuliyokutumia?
        Kama hukupokea barua pepe, tunaweza kukutumia nyingine.
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success">
            Link mpya ya uthibitisho imetumwa kwenye barua pepe uliyotoa wakati wa usajili.
        </div>
    @endif

    <div class="d-flex justify-content-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary">Tuma Link Nyingine</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link text-muted">Toka</button>
        </form>
    </div>
@endsection
