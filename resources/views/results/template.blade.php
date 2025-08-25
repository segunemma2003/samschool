<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .school-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .student-info {
            margin-bottom: 15px;
        }
        .comment-box {
            border: 1px solid #000;
            padding: 10px;
            min-height: 40px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="school-name">{{ $school->school_name ?? 'SCHOOL NAME' }}</div>
        <div>{{ $school->school_address ?? 'School Address' }}</div>
        <div style="font-weight: bold; margin: 10px 0;">STUDENT'S REPORT CARD</div>
        <div>
            <span style="border: 1px solid #000; padding: 3px 8px; margin-right: 10px;">TERM: {{ $term->name ?? 'N/A' }}</span>
            <span style="border: 1px solid #000; padding: 3px 8px;">SESSION: {{ $academy->title ?? 'N/A' }}</span>
        </div>
    </div>

    <!-- Student Information -->
    <div class="student-info">
        <p><strong>Name:</strong> {{ $student->name ?? 'N/A' }}</p>
        <p><strong>Class:</strong> {{ $student->class->name ?? 'N/A' }}</p>
        <p><strong>Admission No:</strong> {{ $student->admission_no ?? 'N/A' }}</p>
        <p><strong>Days Present:</strong> {{ $studentAttendance->days_present ?? 'N/A' }} | <strong>Days Absent:</strong> {{ $studentAttendance->days_absent ?? 'N/A' }}</p>
    </div>

    <!-- Academic Performance Table -->
    <div style="font-weight: bold; margin-bottom: 10px;">ACADEMIC PERFORMANCE</div>
    <table>
        <thead>
            <tr>
                <th>SUBJECT</th>
                <th>SCORE</th>
                <th>GRADE</th>
                <th>REMARK</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Get subjects data from calculated_data
                $subjectsData = [];
                if (isset($subjects) && is_array($subjects) && count($subjects) > 0) {
                    $subjectsData = $subjects;
                } elseif (isset($calculatedData['subjects']) && is_array($calculatedData['subjects'])) {
                    $subjectsData = $calculatedData['subjects'];
                } elseif (isset($studentResult) && $studentResult->calculated_data) {
                    $parsedData = json_decode($studentResult->calculated_data, true);
                    if (isset($parsedData['subjects']) && is_array($parsedData['subjects'])) {
                        $subjectsData = $parsedData['subjects'];
                    }
                }

                // Helper function to get grade remark
                function getGradeRemark($grade) {
                    $gradeNumber = (int) filter_var($grade, FILTER_SANITIZE_NUMBER_INT);
                    return match (true) {
                        $gradeNumber <= 2 => 'EXCELLENT',
                        $gradeNumber <= 4 => 'CREDIT',
                        $gradeNumber <= 6 => 'PASS',
                        default => 'FAIL'
                    };
                }
            @endphp

            @if(count($subjectsData) > 0)
                @foreach($subjectsData as $subject)
                    <tr>
                        <td style="text-align: left;">{{ $subject['subject_name'] ?? 'Subject' }}</td>
                        <td>{{ $subject['total'] ?? 'N/A' }}</td>
                        <td>{{ $subject['grade'] ?? 'N/A' }}</td>
                        <td>{{ getGradeRemark($subject['grade'] ?? 'F9') }}</td>
                    </tr>
                @endforeach

                <!-- Summary row -->
                <tr style="font-weight: bold;">
                    <td>TOTAL</td>
                    <td>{{ $studentResult->total_score ?? $studentResult->calculation_total ?? 'N/A' }}</td>
                    <td>{{ $summary['grade'] ?? 'N/A' }}</td>
                    <td>POSITION: {{ $summary['position'] ?? 'N/A' }} - {{ $percent ?? 'N/A' }}%</td>
                </tr>
            @else
                <tr>
                    <td colspan="4">No subjects data available</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Grade Scale -->
    <table style="margin-top: 15px;">
        <tr>
            <th>A 70-100 (EXCELLENT)</th>
            <th>C 50-69 (CREDIT)</th>
            <th>P 40-49 (PASS)</th>
            <th>F 0-39 (FAIL)</th>
        </tr>
    </table>

    <!-- Comments Section -->
    <div style="margin-top: 20px;">
        <div style="display: flex; gap: 20px;">
            <div style="flex: 1;">
                <div style="font-weight: bold;">TEACHER'S COMMENT</div>
                <div class="comment-box">
                    {{ $studentComment->comment ?? 'No comment available' }}
                </div>
            </div>

            <div style="flex: 1;">
                <div style="font-weight: bold;">PRINCIPAL'S COMMENT</div>
                <div class="comment-box">
                    {{ $principalComment ?? 'No comment available' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Signature Section -->
    <div style="margin-top: 30px; text-align: center;">
        <div style="display: flex; justify-content: space-between;">
            <div style="text-align: center;">
                <div style="border-top: 1px solid #000; width: 150px; margin-bottom: 5px;"></div>
                <div style="font-size: 10px;">Class Teacher's Signature</div>
            </div>

            <div style="text-align: center;">
                <div style="border-top: 1px solid #000; width: 150px; margin-bottom: 5px;"></div>
                <div style="font-size: 10px;">Principal's Signature</div>
                <div style="font-size: 8px; margin-top: 3px;">{{ now()->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>
</body>
</html>
