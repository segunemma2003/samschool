<?php
namespace App\Services;

use App\Models\Payroll;
use App\Models\SchoolInformation;
use Barryvdh\DomPDF\Facade\Pdf;

class PayslipPdfGenerator
{
    public function generate(Payroll $payroll)
    {
        $school = SchoolInformation::where('status', 'true')->first();
        $pdf = Pdf::loadView('pdf.payslip', [
            'payroll' => $payroll,
            'school' => $school,
        ]);

        return $pdf->stream("payslip-{$payroll->payslip->payslip_number}.pdf");
    }
}
