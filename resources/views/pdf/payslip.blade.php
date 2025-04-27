<!DOCTYPE html>
<html>
<head>
    <title>Payslip - {{ $payroll->payslip->payslip_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .school-logo { max-height: 100px; }
        .payslip-info { text-align: right; }
        .payslip-title { text-align: center; margin: 20px 0; font-size: 24px; }
        .staff-info { margin-bottom: 20px; }
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
        <div class="payslip-info">
            <h3>PAYSLIP</h3>
            <p>No: {{ $payroll->payslip->payslip_number }}</p>
            <p>Date: {{ $payroll->payslip->issue_date->format('d/m/Y') }}</p>
            <p>Period: {{ $payroll->month }} {{ $payroll->year }}</p>
        </div>
    </div>

    <div class="staff-info">
        <h4>Staff Information</h4>
        <p>Name: {{ $payroll->staffSalary->staff->name }}</p>
        <p>Position: {{ $payroll->staffSalary->staff->position }}</p>
        <p>Department: {{ $payroll->staffSalary->staff->department->name ?? 'N/A' }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Amount (₦)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic Salary</td>
                <td>{{ number_format($payroll->basic_salary, 2) }}</td>
            </tr>

            @foreach($payroll->payslip->payrollItems as $item)
                @if($item->type === 'allowance')
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endif
            @endforeach

            <tr>
                <td><strong>Total Earnings</strong></td>
                <td><strong>{{ number_format($payroll->basic_salary + $payroll->total_allowances, 2) }}</strong></td>
            </tr>

            @foreach($payroll->payslip->payrollItems as $item)
                @if($item->type === 'deduction')
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endif
            @endforeach

            <tr>
                <td><strong>Total Deductions</strong></td>
                <td><strong>{{ number_format($payroll->total_deductions, 2) }}</strong></td>
            </tr>

            <tr>
                <td><strong>Net Salary</strong></td>
                <td><strong>{{ number_format($payroll->net_salary, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="bank-details">
        <h4>Payment Information</h4>
        <p>Payment Method: {{ ucfirst($payroll->payment_method) }}</p>
        @if($payroll->transaction_reference)
            <p>Transaction Reference: {{ $payroll->transaction_reference }}</p>
        @endif
        <p>Payment Date: {{ $payroll->paid_at?->format('d/m/Y') ?? 'Pending' }}</p>
    </div>

    <div class="footer">
        <p>This is a computer generated payslip and does not require signature.</p>
        <p>{{ $school->school_name }} - {{ $school->website }}</p>
    </div>
</body>
</html>
