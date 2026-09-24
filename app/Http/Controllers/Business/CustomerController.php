<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StoreCustomerRequest;
use App\Http\Requests\Business\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Customer::class);

        $customers = Customer::query()
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('business.customers.index', ['customers' => $customers]);
    }

    public function create()
    {
        $this->authorize('create', Customer::class);

        return view('business.customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        Customer::create($request->validated());

        return redirect()->route('customers.index')->with('status', 'Mteja ameongezwa.');
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);

        return view('business.customers.show', [
            'customer' => $customer,
            'orders' => $customer->orders()->latest()->paginate(10),
        ]);
    }

    public function edit(Customer $customer)
    {
        $this->authorize('update', $customer);

        return view('business.customers.edit', ['customer' => $customer]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return redirect()->route('customers.index')->with('status', 'Mteja amesasishwa.');
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        return redirect()->route('customers.index')->with('status', 'Mteja amefutwa.');
    }

    public function storeNote(Request $request, Customer $customer)
    {
        $this->authorize('update', $customer);

        $request->validate(['note' => ['required', 'string', 'max:1000']]);

        $customer->notes()->create([
            'note' => $request->note,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('customers.show', $customer)->with('status', 'Maelezo yamehifadhiwa.');
    }
}
