<?php

namespace App\Http\Controllers\Business;

use App\Actions\Orders\CompleteSaleAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Business\QuickSaleRequest;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\Inventory\StockService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Order::class);

        $orders = Order::query()
            ->with('customer')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, fn ($q, $search) => $q->where('order_number', 'like', "%{$search}%")
                ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%")))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('business.orders.index', ['orders' => $orders]);
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        return view('business.orders.show', [
            'order' => $order->load(['items.product', 'items.variant', 'payments', 'statusHistory.changedBy', 'customer']),
        ]);
    }

    public function quickSale()
    {
        $this->authorize('create', Order::class);

        return view('business.orders.quick-sale', [
            'products' => Product::where('status', 'active')->orderBy('name')->get(),
            'customers' => Customer::orderBy('name')->limit(200)->get(),
            'canApplyDiscount' => request()->user()->can('applyDiscount', Order::class),
        ]);
    }

    public function storeQuickSale(QuickSaleRequest $request, CompleteSaleAction $completeSale)
    {
        $business = current_business();

        $customer = $request->customer_id
            ? Customer::findOrFail($request->customer_id)
            : Customer::create([
                'name' => $request->new_customer_name,
                'phone' => $request->new_customer_phone,
            ]);

        $paymentData = $request->filled('payment_amount') && $request->payment_amount > 0
            ? [
                'amount' => (int) $request->payment_amount,
                'method' => $request->payment_method,
                'reference' => $request->payment_reference,
            ]
            : null;

        $order = $completeSale->execute(
            $business,
            $customer,
            $request->items,
            $paymentData,
            [
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'notes' => $request->notes,
            ],
            $request->user()->can('applyDiscount', Order::class),
            $request->user()->id,
        );

        if ($paymentData) {
            AuditLog::record('payment.confirmed', [
                'entity_type' => Order::class,
                'entity_id' => $order->id,
                'metadata' => ['amount' => $paymentData['amount'], 'method' => $paymentData['method']],
            ]);
        }

        return redirect()->route('orders.show', $order)->with('status', 'Mauzo yamekamilika.');
    }

    public function cancel(Request $request, Order $order, StockService $stock)
    {
        $this->authorize('cancel', $order);

        $request->validate(['reason' => ['required', 'string', 'max:255']]);

        abort_unless($order->isCancellable(), 422, 'Agizo hili haliwezi kughairiwa.');

        foreach ($order->items as $item) {
            if ($item->product) {
                $stock->recordMovement(
                    $item->product,
                    'return',
                    $item->quantity,
                    $item->variant,
                    $order,
                    'Kughairiwa kwa agizo',
                    $request->user()->id,
                );
            }
        }

        $order->transitionTo('cancelled', $request->user()->id, $request->reason);

        AuditLog::record('order.cancelled', [
            'entity_type' => Order::class,
            'entity_id' => $order->id,
            'metadata' => ['reason' => $request->reason],
        ]);

        return redirect()->route('orders.show', $order)->with('status', 'Agizo limeghairiwa.');
    }

    public function complete(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        abort_unless($order->status === 'paid', 422, 'Agizo lazima liwe limelipwa kabla ya kukamilika.');

        $order->transitionTo('completed', $request->user()->id);

        return redirect()->route('orders.show', $order)->with('status', 'Agizo limekamilika.');
    }
}
