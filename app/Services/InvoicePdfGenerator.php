<?php
// app/Services/InvoicePdfGenerator.php
namespace App\Services;

use App\Models\SchoolInvoice;
use App\Models\SchoolInformation;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfGenerator
{
    public function generate(SchoolInvoice $invoice)
    {
        $school = SchoolInformation::where('status', 'true')->first();
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'school' => $school,
        ]);

        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    }
}
