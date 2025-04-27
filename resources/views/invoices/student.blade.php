<!DOCTYPE html>
<html>
<head>
    <title>School Fee Invoice</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .invoice-info { margin-bottom: 30px; }
        .student-info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total-row { font-weight: bold; }
        .footer { margin-top: 50px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <h2>School Fee Invoice</h2>
    </div>

    <div class="invoice-info">
        <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
        <p><strong>Issue Date:</strong> {{ $invoice->issue_date->format('d/m/Y') }}</p>
        <p><strong>Due Date:</strong> {{ $invoice->due_date->format('d/m/Y') }}</p>
    </div>

    <div class="student-info">
        <p><strong>Student Name:</strong> {{ $invoice->student->name }}</p>
        <p><strong>Class:</strong> {{ $invoice->student->schoolClass->name }}</p>
        <p><strong>Academic Year:</strong> {{ $invoice->academicYear->name }}</p>
        <p><strong>Term:</strong> {{ $invoice->term->name }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fee Category</th>
                <th>Description</th>
                <th>Amount (₦)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($feeItems as $category => $items)
                <tr>
                    <td colspan="3" style="background-color: #f8f9fa;"><strong>{{ $category }}</strong></td>
                </tr>
                @foreach($items as $item)
                <tr>
                    <td></td>
                    <td>{{ $item->feeStructure->description ?? 'Standard fee' }}</td>
                    <td>{{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            @endforeach
            <tr class="total-row">
                <td colspan="2" style="text-align: right;">Total Amount Due:</td>
                <td>{{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2" style="text-align: right;">Amount Paid:</td>
                <td>{{ number_format($invoice->total_paid, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2" style="text-align: right;">Balance:</td>
                <td>{{ number_format($invoice->total_amount - $invoice->total_paid, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on: {{ now()->format('d/m/Y H:i') }}</p>
        <p>Thank you for your prompt payment</p>
    </div>
</body>
</html>
