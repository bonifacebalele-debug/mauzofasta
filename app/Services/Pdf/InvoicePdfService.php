<?php

namespace App\Services\Pdf;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class InvoicePdfService
{
    public function stream(Invoice $invoice): Response
    {
        return Pdf::loadView('pdf.invoice', ['invoice' => $invoice->load(['items', 'customer', 'order'])])
            ->stream("{$invoice->invoice_number}.pdf");
    }
}
