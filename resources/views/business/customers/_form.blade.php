@csrf

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">Jina la Mteja</label>
        <input class="form-control" type="text" name="name" id="name" value="{{ old('name', $customer->name ?? '') }}" required autofocus>
    </div>
    <div class="col-md-6 mb-3">
        <label for="phone" class="form-label">Namba ya Simu</label>
        <input class="form-control" type="tel" name="phone" id="phone" value="{{ old('phone', $customer->phone ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="email" class="form-label">Barua Pepe (si lazima)</label>
        <input class="form-control" type="email" name="email" id="email" value="{{ old('email', $customer->email ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label for="region" class="form-label">Mkoa</label>
        <input class="form-control" type="text" name="region" id="region" value="{{ old('region', $customer->region ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label for="district" class="form-label">Wilaya</label>
        <input class="form-control" type="text" name="district" id="district" value="{{ old('district', $customer->district ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="area" class="form-label">Mtaa/Eneo</label>
        <input class="form-control" type="text" name="area" id="area" value="{{ old('area', $customer->area ?? '') }}">
    </div>
</div>

<button type="submit" class="btn btn-primary">{{ isset($customer) ? 'Hifadhi Mabadiliko' : 'Ongeza Mteja' }}</button>
<a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Ghairi</a>
