<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="application-name" content="{{ config('app.name') }}" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Student Result - {{ $student->name }}</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            @page {
                margin: 0;
                size: A4;
            }
            body {
                font-family: 'Arial', sans-serif;
                margin: 0;
                padding: 20px;
                background-color: #ffffff;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #e5e7eb;
                padding-bottom: 20px;
            }
            .school-logo {
                max-width: 100px;
                margin: 0 auto 10px;
            }
            .school-name {
                font-size: 24px;
                font-weight: bold;
                color: #1f2937;
                margin-bottom: 5px;
            }
            .school-address {
                font-size: 14px;
                color: #6b7280;
            }
            .student-info {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
                margin-bottom: 30px;
                padding: 15px;
                background-color: #f9fafb;
                border-radius: 8px;
            }
            .info-item {
                display: flex;
                flex-direction: column;
            }
            .info-label {
                font-size: 12px;
                color: #6b7280;
                margin-bottom: 4px;
            }
            .info-value {
                font-size: 14px;
                color: #1f2937;
                font-weight: 500;
            }
            .result-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 30px;
            }
            .result-table th,
            .result-table td {
                padding: 12px;
                text-align: left;
                border: 1px solid #e5e7eb;
            }
            .result-table th {
                background-color: #f3f4f6;
                font-weight: 600;
            }
            .result-table tr:nth-child(even) {
                background-color: #f9fafb;
            }
            .summary-section {
                margin-bottom: 30px;
                padding: 15px;
                background-color: #f9fafb;
                border-radius: 8px;
            }
            .summary-title {
                font-size: 16px;
                font-weight: 600;
                color: #1f2937;
                margin-bottom: 15px;
            }
            .summary-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 15px;
            }
            .summary-item {
                padding: 10px;
                background-color: #ffffff;
                border-radius: 6px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .summary-label {
                font-size: 12px;
                color: #6b7280;
                margin-bottom: 4px;
            }
            .summary-value {
                font-size: 14px;
                color: #1f2937;
                font-weight: 500;
            }
            .comments-section {
                margin-bottom: 30px;
            }
            .comment-box {
                padding: 15px;
                background-color: #f9fafb;
                border-radius: 8px;
                margin-bottom: 15px;
            }
            .comment-title {
                font-size: 14px;
                font-weight: 600;
                color: #1f2937;
                margin-bottom: 8px;
            }
            .comment-content {
                font-size: 14px;
                color: #4b5563;
                line-height: 1.5;
            }
            .signature-section {
                display: flex;
                justify-content: space-between;
                margin-top: 50px;
                padding-top: 20px;
                border-top: 2px solid #e5e7eb;
            }
            .signature-item {
                text-align: center;
            }
            .signature-line {
                width: 150px;
                border-bottom: 1px solid #000;
                margin: 50px auto 5px;
            }
            .signature-name {
                font-size: 14px;
                font-weight: 600;
                color: #1f2937;
            }
            .signature-title {
                font-size: 12px;
                color: #6b7280;
            }
        </style>

        @filamentStyles
        @vite('resources/css/app.css')
    </head>

    <body class="antialiased p-4 text-gray-800 bg-gray-50">
        <div class="header">
            @if($school->logo)
                <img src="{{ $school->logo }}" alt="School Logo" class="school-logo">
            @endif
            <div class="school-name">{{ $school->name }}</div>
            <div class="school-address">{{ $school->address }}</div>
        </div>

        <div class="student-info">
            <div class="info-item">
                <span class="info-label">Student Name</span>
                <span class="info-value">{{ $student->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Registration Number</span>
                <span class="info-value">{{ $student->registration_number }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Class</span>
                <span class="info-value">{{ $class->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Term</span>
                <span class="info-value">{{ $term->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Academic Year</span>
                <span class="info-value">{{ $academy->title }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Date</span>
                <span class="info-value">{{ now()->format('d M, Y') }}</span>
            </div>
        </div>

        <table class="result-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    @foreach($markObtained as $mark)
                        <th>{{ $mark->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($courses as $course)
                    <tr>
                        <td>{{ $course->subject->subjectDepot->name }}</td>
                        @foreach($markObtained as $mark)
                            <td>
                                @php
                                    $score = $course->scoreBoard->firstWhere('result_section_type_id', $mark->id);
                                    echo $score ? $score->score : '-';
                                @endphp
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-section">
            <div class="summary-title">Performance Summary</div>
            <div class="summary-grid">
                @foreach($studentSummary as $summary)
                    <div class="summary-item">
                        <div class="summary-label">{{ $summary->name }}</div>
                        <div class="summary-value">
                            @php
                                $score = $courses->first()->scoreBoard->firstWhere('result_section_type_id', $summary->id);
                                echo $score ? $score->score : '-';
                            @endphp
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="comments-section">
            <div class="comment-box">
                <div class="comment-title">Class Teacher's Comment</div>
                <div class="comment-content">{{ $studentComment->comment ?? 'No comment available.' }}</div>
            </div>
            <div class="comment-box">
                <div class="comment-title">Principal's Comment</div>
                <div class="comment-content">{{ $principalComment }}</div>
            </div>
        </div>

        <div class="signature-section">
            <div class="signature-item">
                <div class="signature-line"></div>
                <div class="signature-name">Class Teacher</div>
                <div class="signature-title">Signature</div>
            </div>
            <div class="signature-item">
                <div class="signature-line"></div>
                <div class="signature-name">Principal</div>
                <div class="signature-title">Signature</div>
            </div>
        </div>

        @livewire('notifications')

        @filamentScripts
        @vite('resources/js/app.js')
    </body>
</html>
