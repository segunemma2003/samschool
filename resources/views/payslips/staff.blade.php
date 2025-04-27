<!DOCTYPE html>
<html>
<head>
    <title>Salary Payslip</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .info { margin-bottom: 30px; }
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
        <h2>Salary Payslip</h2>
    </div>

    <div class="info">
        <p><strong>Staff Name:</strong> {{ $salary->staff->name }}</p>
        <p><strong>Employee ID:</strong> {{ $salary->staff->employee_id }}</p>
        <p><strong>Month:</strong> {{ \Carbon\Carbon::createFromFormat('Y-m', $salary->month)->format('F Y') }}</p>
        <p><strong>Payment Date:</strong> {{ $salary->paid_at?->format('d/m/Y') ?? 'Pending' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th colspan="2">Earnings</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic Salary</td>
                <td>{{ number_format($salary->basic_salary, 2) }}</td>
            </tr>

            @foreach($salary->allowances as $allowance)
            <tr>
                <td>{{ $allowance['name'] }}</td>
                <td>{{ number_format($allowance['amount'], 2) }}</td>
            </tr>
            @endforeach

            <tr class="total-row">
                <td>Total Earnings</td>
                <td>{{ number_format($salary->gross_salary, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table style="margin-top: 20px;">
        <thead>
            <tr>
                <th colspan="2">Deductions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salary->deductions as $deduction)
            <tr>
                <td>{{ $deduction['name'] }} @if($deduction['is_statutory']) (Statutory) @endif</td>
                <td>{{ number_format($deduction['amount'], 2) }}</td>
            </tr>
            @endforeach

            @if($salary->tax_amount > 0)
            <tr>
                <td>Tax ({{ $salary->tax_rate }}%)</td>
                <td>{{ number_format($salary->tax_amount, 2) }}</td>
            </tr>
            @endif

            <tr class="total-row">
                <td>Total Deductions</td>
                <td>{{ number_format($salary->total_deductions + $salary->tax_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table style="margin-top: 20px;">
        <tbody>
            <tr class="total-row">
                <td>Net Salary</td>
                <td>{{ number_format($salary->net_salary, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on: {{ now()->format('d/m/Y H:i') }}</p>
        <p>This is a computer generated payslip and does not require a signature</p>
    </div>
</body>
</html>
