<?php

namespace App\Services\Pdf;

use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class ReceiptPdfService
{
    public function stream(Receipt $receipt): Response
    {
        return Pdf::loadView('pdf.receipt', ['receipt' => $receipt->load(['customer', 'payment.order'])])
            ->stream("{$receipt->receipt_number}.pdf");
    }
}
