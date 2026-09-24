<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Pdf\InvoicePdfService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = Invoice::query()
            ->with('customer')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('business.invoices.index', ['invoices' => $invoices]);
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        return view('business.invoices.show', [
            'invoice' => $invoice->load(['items', 'customer', 'order']),
        ]);
    }

    public function pdf(Invoice $invoice, InvoicePdfService $pdf)
    {
        $this->authorize('view', $invoice);

        return $pdf->stream($invoice);
    }
}
