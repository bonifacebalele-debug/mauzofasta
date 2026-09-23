@extends('layouts.guest', ['title' => 'Anza Bure', 'wide' => true])

@section('content')
    <h4 class="fs-20 text-center">Fungua Akaunti ya MAUZO FASTA</h4>
    <p class="text-muted text-center mb-4">Dakika 5 tu kuanza kuuza.</p>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('business.register') }}" enctype="multipart/form-data">
        @csrf

        <h6 class="text-uppercase text-muted fs-13 mb-3">1. Taarifa za Biashara</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="business_name" class="form-label">Jina la Biashara</label>
                <input class="form-control" type="text" name="business_name" id="business_name" value="{{ old('business_name') }}" required autofocus>
            </div>
            <div class="col-md-6 mb-3">
                <label for="category" class="form-label">Aina ya Biashara</label>
                <select class="form-select" name="category" id="category" required>
                    <option value="" disabled {{ old('category') ? '' : 'selected' }}>Chagua...</option>
                    @foreach (['Rejareja' => 'Rejareja (Duka)', 'Mitandao ya Kijamii' => 'Mauzo ya Mitandao ya Kijamii', 'Chakula' => 'Chakula', 'Huduma' => 'Huduma', 'Jumla/Usambazaji' => 'Jumla / Usambazaji', 'Nyingine' => 'Nyingine'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-8 mb-3">
                <label for="description" class="form-label">Maelezo ya Biashara (si lazima)</label>
                <textarea class="form-control" name="description" id="description" rows="2">{{ old('description') }}</textarea>
            </div>
            <div class="col-md-4 mb-3">
                <label for="logo" class="form-label">Logo (si lazima)</label>
                <input class="form-control" type="file" name="logo" id="logo" accept="image/*">
            </div>
        </div>

        <h6 class="text-uppercase text-muted fs-13 mb-3 mt-2">2. Taarifa Zako</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="owner_name" class="form-label">Jina Lako</label>
                <input class="form-control" type="text" name="owner_name" id="owner_name" value="{{ old('owner_name') }}" required>
            </div>
            <div class="col-md-3 mb-3">
                <label for="phone" class="form-label">Namba ya Simu</label>
                <input class="form-control" type="tel" name="phone" id="phone" value="{{ old('phone') }}" placeholder="07XXXXXXXX" required>
            </div>
            <div class="col-md-3 mb-3">
                <label for="email" class="form-label">Barua Pepe</label>
                <input class="form-control" type="email" name="email" id="email" value="{{ old('email') }}" required>
            </div>
        </div>

        <h6 class="text-uppercase text-muted fs-13 mb-3 mt-2">3. Mahali Biashara Ipo</h6>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="region" class="form-label">Mkoa</label>
                <input class="form-control" type="text" name="region" id="region" value="{{ old('region') }}" required>
            </div>
            <div class="col-md-3 mb-3">
                <label for="district" class="form-label">Wilaya</label>
                <input class="form-control" type="text" name="district" id="district" value="{{ old('district') }}" required>
            </div>
            <div class="col-md-3 mb-3">
                <label for="ward" class="form-label">Kata (si lazima)</label>
                <input class="form-control" type="text" name="ward" id="ward" value="{{ old('ward') }}">
            </div>
            <div class="col-md-3 mb-3">
                <label for="area" class="form-label">Mtaa/Eneo (si lazima)</label>
                <input class="form-control" type="text" name="area" id="area" value="{{ old('area') }}">
            </div>
        </div>

        <h6 class="text-uppercase text-muted fs-13 mb-3 mt-2">4. Nenosiri</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Nenosiri</label>
                <input class="form-control" type="password" name="password" id="password" required minlength="8">
            </div>
            <div class="col-md-6 mb-3">
                <label for="password_confirmation" class="form-label">Thibitisha Nenosiri</label>
                <input class="form-control" type="password" name="password_confirmation" id="password_confirmation" required minlength="8">
            </div>
        </div>

        <button class="btn btn-primary w-100 mt-2" type="submit">Fungua Biashara</button>
    </form>

    <div class="text-center mt-4">
        <p class="text-muted mb-0">Una akaunti tayari?
            <a href="{{ route('login') }}" class="fw-bold text-decoration-underline">Ingia</a>
        </p>
    </div>
@endsection
