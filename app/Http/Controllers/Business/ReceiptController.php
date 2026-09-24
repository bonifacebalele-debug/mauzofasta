<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use App\Services\Pdf\ReceiptPdfService;

class ReceiptController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Receipt::class);

        return view('business.receipts.index', [
            'receipts' => Receipt::with('customer')->latest('issued_at')->paginate(20),
        ]);
    }

    public function show(Receipt $receipt)
    {
        $this->authorize('view', $receipt);

        return view('business.receipts.show', [
            'receipt' => $receipt->load(['customer', 'payment.order']),
        ]);
    }

    public function pdf(Receipt $receipt, ReceiptPdfService $pdf)
    {
        $this->authorize('view', $receipt);

        return $pdf->stream($receipt);
    }
}
