// resources/views/pdf/invoice.blade.php
<!DOCTYPE html>
<html>
<head>
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .school-logo { max-height: 100px; }
        .invoice-info { text-align: right; }
        .invoice-title { text-align: center; margin: 20px 0; font-size: 24px; }
        .student-info { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .total-section { text-align: right; margin-top: 20px; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; }
        .bank-details { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            @if($school->logo_path)
                <img src="{{ storage_path('app/public/' . $school->logo_path) }}" class="school-logo">
            @endif
            <h2>{{ $school->school_name }}</h2>
            <p>{{ $school->address }}</p>
            <p>Phone: {{ $school->phone }} | Email: {{ $school->email }}</p>
        </div>
        <div class="invoice-info">
            <h3>INVOICE</h3>
            <p>No: {{ $invoice->invoice_number }}</p>
            <p>Date: {{ $invoice->issue_date->format('d/m/Y') }}</p>
            <p>Due Date: {{ $invoice->due_date->format('d/m/Y') }}</p>
        </div>
    </div>

    <div class="student-info">
        <h4>Student Information</h4>
        <p>Name: {{ $invoice->student->name }}</p>
        <p>Class: {{ $invoice->student->schoolClass->name }}</p>
        <p>Academic Year: {{ $invoice->academicYear->name }}</p>
        <p>Term: {{ $invoice->term->name }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Fee Description</th>
                <th>Amount (₦)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->studentFees as $fee)
                <tr>
                    <td>{{ $fee->classFee->feeStructure->name }}</td>
                    <td>{{ number_format($fee->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <p><strong>Total Amount: ₦{{ number_format($invoice->total_amount, 2) }}</strong></p>
        <p><strong>Amount Paid: ₦{{ number_format($invoice->paid_amount, 2) }}</strong></p>
        <p><strong>Balance Due: ₦{{ number_format($invoice->balance, 2) }}</strong></p>
    </div>

    <div class="bank-details">
        <h4>Bank Details</h4>
        <p>Bank Name: {{ $school->bank_name }}</p>
        <p>Account Name: {{ $school->bank_account_name }}</p>
        <p>Account Number: {{ $school->bank_account_number }}</p>
    </div>

    <div class="footer">
        <p>Thank you for your payment. Please quote the invoice number when making payment.</p>
        <p>{{ $school->school_name }} - {{ $school->website }}</p>
    </div>
</body>
</html>
