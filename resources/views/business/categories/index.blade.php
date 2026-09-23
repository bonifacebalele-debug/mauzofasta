@extends('layouts.vertical', ['title' => 'Makundi ya Bidhaa'])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => 'Makundi ya Bidhaa', 'sub_title' => 'Bidhaa'])

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row">
        <div class="col-md-4">
            @can('create', \App\Models\Category::class)
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-3">Ongeza Kundi</h6>
                        <form method="POST" action="{{ route('categories.store') }}">
                            @csrf
                            <div class="mb-3">
                                <input class="form-control" type="text" name="name" placeholder="Jina la kundi" required>
                            </div>
                            <div class="mb-3">
                                <select class="form-select" name="parent_id">
                                    <option value="">Hakuna kundi mzazi</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Ongeza</button>
                        </form>
                    </div>
                </div>
            @endcan
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Jina</th>
                                    <th>Kundi Mzazi</th>
                                    <th>Bidhaa</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $category)
                                    <tr>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->parent?->name ?? '—' }}</td>
                                        <td>{{ $category->products_count }}</td>
                                        <td class="text-end">
                                            @can('delete', $category)
                                                <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                                    onsubmit="return confirm('Una hakika unataka kufuta {{ $category->name }}?')" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Bado hakuna makundi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
