<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>School Broad Sheet</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      margin: 20px;
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .header-left {
      text-align: left;
    }
    .header-right {
      text-align: right;
    }
    .horizontal-line {
      border-top: 2px solid black;
      margin: 5px 0;
    }
    .info {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
    }
    .info-left {
      text-align: left;
    }
    .info-center {
      text-align: center;
      flex: 1;
    }
    .info-right {
      text-align: right;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      border: 1px solid black;
      padding: 5px;
      text-align: center;
    }
    th {
      background-color: #f2f2f2;
    }
    .logo {
      text-align: center;
      margin-bottom: 10px;
    }
    .logo img {
      width: 100px;
      height: auto;
    }
    .criteria {
      margin-top: 20px;
      font-size: 12px;
      text-align: left;
    }
    .criteria h4 {
      margin-bottom: 5px;
    }
    .criteria ul {
      padding-left: 20px;
      margin: 5px 0;
    }
    .criteria p {
      margin: 5px 0;
    }
  </style>
</head>
<body>

<div class="logo">
  <img src="{{ $data['school']->school_logo ? Storage::url($data['school']->school_logo) : 'https://via.placeholder.com/100' }}" alt="School Logo"> <!-- Replace 'logo.png' with the actual image file -->
</div>

<div class="header">
  <div class="header-left">
    <strong> {{$data['school']->school_name}}</strong>
  </div>
  <div class="header-right">
    {{-- <strong>School Code:</strong> <span>12345</span> <br> --}}
    {{-- <strong>Edu Zone:</strong> <span>All</span> --}}
  </div>
</div>
<div class="horizontal-line"></div>
<div class="header-left">
  <strong>Phone:</strong> {{$data['school']->school_phone}}
</div>

<div class="info">
  <div class="info-left">
    <strong>Class:</strong> {{$data['className']}} <br>
    <strong>Term:</strong> {{$data['academy']->title}} {{$data['term']->name}}
  </div>
  <div class="info-center">
    <h3>Broad Sheet</h3>
  </div>
  <div class="info-right">
    <strong>Class Teacher:</strong> {{$data['classTeacherName']}} <br>
    <strong>Date:</strong>  {{ \Carbon\Carbon::now()->toFormattedDateString() }}
  </div>
</div>

<table>
  <thead>
    <tr>
      <th rowspan="2">S/N</th>
      <th rowspan="2">Name of Student</th>
      <th rowspan="2">Sex</th>
      <th colspan="{{count($data['students'][0]['scores'])}}">Subjects</th>
      <th rowspan="2">Remarks</th>
    </tr>
    <tr>
        @foreach($data['students'][0]['scores'] as $score)
            <th>{{ $score['subject'] }}</th>
        @endforeach
    </tr>
  </thead>
  <tbody>
    @foreach($data['students'] as $index => $studentData)
        <tr>
            <td>{{ $index + 1 }}</td>
        <td>{{ $studentData['student']->name }}</td>
        <td>{{ $studentData['student']->gender }}</td>
        @foreach($studentData['scores'] as $score)
            <td>{{ $score['score'] }}</td>
        @endforeach
        <td>{{$studentData['remark']}}</td>
        </tr>
        @endforeach

    <!-- Add more rows as needed -->
  </tbody>
</table>

<div class="criteria">
  <h4>ACADEMIC CRITERIA USED FOR PASSED/PROMOTED STUDENTS</h4>
  <ul>
    <li>Results with only {{$data['term']->name}} term out of 3 terms in a session.</li>
    <li>Results with exams written of less than {{count($data['courses'])}} subjects in an academic session.</li>
    {{-- <li>Results with exams written of less than 5 subjects in a term.</li> --}}
  </ul>
  <p><strong>NOTE:</strong> <br/>Cumulative results of 1st, 2nd, and 3rd term scores are used in 3rd term promotional results.</p>
  <h4>INCOMPLETE RESULTS:</h4>
  <p><strong>***</strong> English is compulsory with 40% as the pass mark.</p>
  <p><strong>***</strong> Mathematics is compulsory with 40% as the pass mark.</p>
  <p><strong>***</strong> Additional 4 subject(s) with 40% as the pass mark also required.</p>

  <h4>RESULTS SUMMARY</h4>
  <p><strong>STUDENTS IN ROLL:</strong> {{count($data['students'])}}</p>
  <p><strong>NO. OF PASSED / PROMOTED:</strong> {{$data['totalPassed']}}</p>
  <p><strong>PERCENTAGE PASSED / PROMOTED (%):</strong> {{$data['passPercent']}}%</p>
</div>

</body>
</html>
