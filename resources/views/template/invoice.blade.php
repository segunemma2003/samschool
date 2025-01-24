<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>

    <style>
        h4 {
            margin: 0;
        }
        .w-full {
            width: 100%;
        }
        .w-half {
            width: 50%;
        }
        .margin-top {
            margin-top: 1.25rem;
        }
        .footer {
            font-size: 0.875rem;
            padding: 1rem;
            background-color: rgb(241 245 249);
        }
        table {
            width: 100%;
            border-spacing: 0;
        }
        table.products {
            font-size: 0.875rem;
        }
        table.products tr {
            background-color: rgb(96 165 250);
        }
        table.products th {
            color: #ffffff;
            padding: 0.5rem;
        }
        table tr.items {
            background-color: rgb(241 245 249);
        }
        table tr.items td {
            padding: 0.5rem;
        }
        .total {
            text-align: right;
            margin-top: 1rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <table class="w-full">
        <tr>
            <img  src="{{ $school->school_logo ? Storage::url($school->school_logo) : 'https://via.placeholder.com/100' }}"
            alt="{{$school->school_logo}}" width="200" />
            <h1> {{$school->school_name}}</h1>
            <p>{{$school->school_address}}</p>
            <p>{{$school->email}}, {{$school->school_phone}}</p>
        </tr>
        <tr>
            <td class="w-half">
                <h2>Term: {{$term->name}}</h2><br/>

                <h2>Session: {{$academy->title}}</h2>
                <h2>Date: {{$date}}</h2>
            </td>
            <td class="w-half">
                <h2>Invoice ID: {{$record->order_code}}</h2>
            </td>
        </tr>
    </table>

    <div class="margin-top">
        <table class="w-full">
            <tr>
                <td class="w-half">
                    <div><h4>To:</h4></div>
                    <div><b>Name: </b> {{$record->student->name}}</div>
                    <div><b>Class: </b>{{$record->student->class->name}}</div>
                </td>

            </tr>
        </table>
    </div>

    <div class="margin-top">
        <table class="products">
            <tr>
                <th>No</th>
                <th>Name</th>
                <th>Amount</th>
            </tr>
            @foreach($record->invoice_details as $item)
            <tr class="items">


                <td>
                    {{ $loop->index+1 }}
                </td>
                <td>
                        {{ $item->group->name }}
                    </td>

                    <td>
                        {{ number_format($item->amount, 2, '.', ',') }}
                    </td>

            </tr>
            @endforeach
        </table>
    </div>

    <div class="total">
        Total:{{ number_format($record->total_amount, 2, '.', ',') }} NGN
    </div>

    <div class="footer margin-top">
        <div>
            <h4>Note:</h4>
            <br />{!! $record->note ?? ""!!}</div>
        <div>Thank you</div>
        <div>
            <img
            width="40"
          height="40"
            src="{{ $school->school_stamp ? Storage::url($school->school_stamp) : 'https://via.placeholder.com/100' }}"
            alt="{{ $school->school_stamp}}"
           class="mx-auto rounded-md w-[100px] h-[100px] object-cover"
          />
        </div>
        <div>&copy; School Management</div>
    </div>
</body>
</html>
