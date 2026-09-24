<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StorePaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\PaymentServiceInterface;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Payment::class);

        $payments = Payment::query()
            ->with(['order.customer'])
            ->when($request->method, fn ($q, $method) => $q->where('method', $method))
            ->latest('paid_at')
            ->paginate(20)
            ->withQueryString();

        return view('business.payments.index', ['payments' => $payments]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Payment::class);

        $unpaidOrders = Order::query()
            ->with('customer')
            ->whereNotIn('status', ['draft', 'cancelled', 'refunded'])
            ->whereColumn('amount_paid', '<', 'total')
            ->latest()
            ->limit(100)
            ->get();

        return view('business.payments.create', [
            'orders' => $unpaidOrders,
            'preselectedOrder' => $request->order_id
                ? Order::find($request->order_id)
                : null,
        ]);
    }

    public function store(StorePaymentRequest $request, PaymentServiceInterface $payments)
    {
        $order = Order::findOrFail($request->order_id);

        $payment = $payments->recordPayment($order, [
            'amount' => (int) $request->amount,
            'method' => $request->method,
            'reference' => $request->reference,
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()->route('orders.show', $order->refresh())->with('status', 'Malipo yamerekodiwa.');
    }

    public function refund(Payment $payment, PaymentServiceInterface $payments)
    {
        $this->authorize('refund', $payment);

        $payments->refund($payment);

        return redirect()->route('orders.show', $payment->order)->with('status', 'Malipo yamerejeshwa.');
    }
}
