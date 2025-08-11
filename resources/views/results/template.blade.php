<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Report Card - {{ $student->name }}</title>
    <style>
        @page {
            margin: 0.5in;
            size: A4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            line-height: 1.3;
            color: #000;
            background: #fff;
            font-size: 11px;
        }

        .container {
            width: 100%;
            max-width: 100%;
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
            background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .school-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            display: block;
            border: 3px solid #2c3e50;
            border-radius: 50%;
            padding: 5px;
            background: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .school-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
            color: #2c3e50;
            letter-spacing: 1px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .school-address {
            font-size: 11px;
            margin-bottom: 5px;
            color: #34495e;
            font-style: italic;
        }

        .contact-info {
            font-size: 10px;
            color: #7f8c8d;
            line-height: 1.4;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            text-transform: uppercase;
            color: #2c3e50;
            letter-spacing: 2px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 8px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .academic-year {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 25px;
            color: #34495e;
            background: #ecf0f1;
            padding: 8px;
            border-radius: 5px;
            display: inline-block;
            margin-left: 50%;
            transform: translateX(-50%);
        }

        .class-term-info {
            text-align: right;
            margin-bottom: 20px;
        }

        .class-box, .term-box {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #fff;
            padding: 8px 15px;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: bold;
            display: inline-block;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            margin-left: 10px;
        }

        /* Student Information */
        .student-section {
            border: 2px solid #2c3e50;
            margin-bottom: 25px;
            padding: 20px;
            border-radius: 8px;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .student-header {
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
            color: white;
            padding: 10px;
            font-weight: bold;
            text-align: center;
            border-bottom: 2px solid #3498db;
            margin-bottom: 20px;
            border-radius: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .student-data {
            display: flex;
            gap: 20px;
        }

        .student-info {
            flex: 2;
        }

        .student-photo {
            flex: 1;
            text-align: center;
        }

        .photo-placeholder {
            width: 80px;
            height: 100px;
            border: 2px dashed #3498db;
            background: linear-gradient(135deg, #ecf0f1 0%, #bdc3c7 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 8px;
            color: #7f8c8d;
            border-radius: 5px;
        }

        .data-row {
            display: flex;
            margin-bottom: 8px;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 5px;
        }

        .data-label {
            font-weight: bold;
            width: 120px;
            flex-shrink: 0;
        }

        .data-value {
            flex: 1;
            border-bottom: 2px solid #3498db;
            padding-left: 15px;
            font-weight: 500;
            color: #2c3e50;
        }

        .barcode {
            width: 120px;
            height: 30px;
            background: repeating-linear-gradient(
                90deg,
                #000 0px,
                #000 2px,
                transparent 2px,
                transparent 4px
            );
            margin-top: 5px;
        }

        /* Summary Stats */
        .summary-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 10px;
        }

        .stat-item {
            flex: 1;
            text-align: center;
            padding: 15px 10px;
            border: 2px solid #2c3e50;
            background: linear-gradient(135deg, #ecf0f1 0%, #bdc3c7 100%);
            font-weight: bold;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .stat-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #3498db, #e74c3c, #f39c12);
        }

        /* Section Styling */
        .section {
            margin-bottom: 25px;
            border: 2px solid #2c3e50;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .section-title {
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
            color: white;
            padding: 12px;
            font-weight: bold;
            text-align: center;
            border-bottom: 2px solid #3498db;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #3498db, #e74c3c);
        }

        .section-content {
            padding: 15px;
        }

        /* Table Styling */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .data-table th {
            background: #f0f0f0;
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
        }

        .data-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }

        .subject-name {
            text-align: left;
            font-weight: bold;
            width: 100px;
        }

        /* Grade Styling */
        .grade-a, .grade-a1, .grade-b2, .grade-b3 {
            color: #000;
            font-weight: bold;
        }

        .grade-c, .grade-c4, .grade-c5, .grade-c6 {
            color: #000;
            font-weight: bold;
        }

        .grade-p, .grade-d7, .grade-e8 {
            color: #000;
            font-weight: bold;
        }

        .grade-f, .grade-f9 {
            color: #ff0000;
            font-weight: bold;
        }

        /* Behavioral Table */
        .behavioral-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }

        .behavioral-table th {
            background: #f0f0f0;
            border: 1px solid #000;
            padding: 4px 2px;
            text-align: center;
            font-weight: bold;
        }

        .behavioral-table td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
        }

        .category-header {
            background: #e0e0e0;
            font-weight: bold;
            text-align: left;
            padding: 4px;
        }

        .attribute-name {
            text-align: left;
            padding-left: 10px;
        }

        /* Rating Keys */
        .rating-keys {
            border: 1px solid #000;
            padding: 10px;
            margin: 20px 0;
            background: #f9f9f9;
        }

        .rating-keys h4 {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .rating-list {
            font-size: 8px;
            line-height: 1.4;
        }

        /* Comments Section */
        .comments-section {
            margin-bottom: 20px;
        }

        .comment-box {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 15px;
            min-height: 60px;
        }

        .comment-title {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
        }

        .comment-content {
            font-size: 9px;
            line-height: 1.4;
            min-height: 30px;
        }

        .promotion-status {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 3px 8px;
            font-weight: bold;
            font-size: 9px;
            margin-right: 10px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 15px;
            padding-top: 5px;
            font-size: 8px;
            text-align: center;
        }

        /* Print styles */
        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
                <!-- Header -->
        <div class="header">
            @if($school && $school->logo_path)
                <img src="{{ storage_path('app/public/' . $school->logo_path) }}" alt="School Logo" class="school-logo">
            @endif
            <div class="school-name">{{ $school->school_name ?? 'SCHOOL NAME' }}</div>
            <div class="school-address">{{ $school->school_address ?? 'School Address' }}</div>
            <div class="contact-info">
                TEL: {{ $school->school_phone ?? 'N/A' }} |
                EMAIL: {{ $school->email ?? 'N/A' }} |
                WEBSITE: {{ $school->school_website ?? 'N/A' }}
            </div>
        </div>

        <div class="class-term-info">
            <div class="class-box">{{ $class->name ?? 'CLASS' }}</div>
            <div class="term-box">{{ $term->name ?? 'TERM' }}</div>
        </div>

        <div class="report-title">Continuous Assessment Report</div>
        <div class="academic-year">{{ $academy->title ?? 'ACADEMIC YEAR' }}</div>

        <!-- Student Information -->
        <div class="student-section">
            <div class="student-header">STUDENT INFORMATION</div>
            <div class="student-data">
                <div class="student-info">
                    <div class="data-row">
                        <div class="data-label">NAME:</div>
                        <div class="data-value">{{ $student->name }}</div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">SPIN:</div>
                        <div class="data-value">{{ $student->spin ?? 'N/A' }}</div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">ADMISSION NO:</div>
                        <div class="data-value">{{ $student->registration_number ?? 'N/A' }}</div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">SEX:</div>
                        <div class="data-value">{{ $student->gender ?? 'N/A' }}</div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">CLASS:</div>
                        <div class="data-value">{{ $class->name ?? 'N/A' }}</div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">BARCODE:</div>
                        <div class="data-value">
                            <div class="barcode">{{ $student->registration_number ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
                <div class="student-photo">
                    @if($student->photo)
                        <img src="{{ $student->photo }}" alt="Student Photo" style="width: 80px; height: 100px; object-fit: cover; border: 1px solid #000;">
                    @else
                        <div class="photo-placeholder">STUDENT PHOTO</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="summary-stats">
            <div class="stat-item">NO. IN CLASS: {{ $totalSubject ?? 'N/A' }}</div>
            <div class="stat-item">TOTAL TERM SCORE: {{ number_format($totalScore, 0) ?? 'N/A' }}</div>
            <div class="stat-item">
                POSITION:
                @php
                    $position = '-';
                    foreach($studentSummary as $summary) {
                        if ($summary->calc_pattern == 'position') {
                            $score = $courses->first()->scoreBoard->firstWhere('result_section_type_id', $summary->id);
                            $position = $score ? $score->score : '-';
                            break;
                        }
                    }
                    echo $position;
                @endphp
                - {{ $percent ?? 'N/A' }}%
                @php
                    if ($percent >= 80) echo 'GOLD';
                    elseif ($percent >= 70) echo 'SILVER';
                    elseif ($percent >= 60) echo 'BRONZE';
                    else echo 'N/A';
                @endphp
            </div>
        </div>

        <!-- Attendance -->
        <div class="section">
            <div class="section-title">Attendance</div>
            <div class="section-content">
                <table class="data-table">
                    <tr>
                        <th>Times Sch. Opened</th>
                        <th>Times Present</th>
                        <th>Times Absent</th>
                    </tr>
                    <tr>
                        <td>{{ $studentAttendance->expected_present ?? 'N/A' }}</td>
                        <td>{{ $studentAttendance->total_present ?? 'N/A' }}</td>
                        <td style="color: #ff0000;">{{ $studentAttendance->total_absent ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Terminal Duration -->
        <div class="section">
            <div class="section-title">Terminal Duration ({{ $term->duration ?? 'N/A' }} WEEKS)</div>
            <div class="section-content">
                <table class="data-table">
                    <tr>
                        <th>Term Begins</th>
                        <th>Term Ends</th>
                        <th>Next Term Begins</th>
                    </tr>
                    <tr>
                        <td>{{ $school->term_begin ? \Carbon\Carbon::parse($school->term_begin)->format('d M Y') : 'N/A' }}</td>
                        <td>{{ $school->term_ends ? \Carbon\Carbon::parse($school->term_ends)->format('d M Y') : 'N/A' }}</td>
                        <td>{{ $school->next_term_begins ? \Carbon\Carbon::parse($school->next_term_begins)->format('d M Y') : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Academic Performance - Current Term -->
        <div class="section">
            <div class="section-title">Academic Performance - Marks Obtained ({{ $term->name ?? 'Current Term' }})</div>
            <div class="section-content">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="subject-name">Subject</th>
                            <th>{{ $term->name ?? 'Current' }} C.A (40%)</th>
                            <th>{{ $term->name ?? 'Current' }} Exam (60%)</th>
                            <th>Term Avg Score (100%)</th>
                            <th>Grade</th>
                            <th>Position</th>
                        </tr>
                        <tr>
                            <th class="subject-name">Max. Obtainable Mark</th>
                            <th>40%</th>
                            <th>60%</th>
                            <th>100%</th>
                            <th>-</th>
                            <th>-</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($courses as $course)
                            @php
                                $caScore = 0;
                                $examScore = 0;
                                $totalScore = 0;

                                foreach($course->scoreBoard as $score) {
                                    $sectionType = $resultSectionTypes->where('id', $score->result_section_type_id)->first();
                                    if ($sectionType) {
                                        if (str_contains(strtolower($sectionType->name), 'ca') || str_contains(strtolower($sectionType->name), 'continuous')) {
                                            $caScore += (float) $score->score;
                                        } elseif (str_contains(strtolower($sectionType->name), 'exam') || str_contains(strtolower($sectionType->name), 'final')) {
                                            $examScore += (float) $score->score;
                                        }
                                    }
                                }

                                $totalScore = $caScore + $examScore;

                                $grade = '';
                                if ($totalScore >= 70) $grade = 'A';
                                elseif ($totalScore >= 50) $grade = 'C';
                                elseif ($totalScore >= 40) $grade = 'P';
                                else $grade = 'F';

                                $gradeClass = 'grade-' . strtolower($grade);
                            @endphp
                            <tr>
                                <td class="subject-name">{{ $course->subject->subjectDepot->name ?? 'N/A' }}</td>
                                <td>{{ number_format($caScore, 0) }}</td>
                                <td>{{ number_format($examScore, 0) }}</td>
                                <td>{{ number_format($totalScore, 0) }}</td>
                                <td class="{{ $gradeClass }}">{{ $grade }}</td>
                                <td>
                                    @php
                                        $position = '-';
                                        foreach($studentSummary as $summary) {
                                            if ($summary->calc_pattern == 'position') {
                                                $score = $course->scoreBoard->firstWhere('result_section_type_id', $summary->id);
                                                $position = $score ? $score->score . 'TH' : '-';
                                                break;
                                            }
                                        }
                                        echo $position;
                                    @endphp
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Annual Summary -->
        <div class="section">
            <div class="section-title">Academic Performance - Annual Summary</div>
            <div class="section-content">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="subject-name">Subject</th>
                            <th>Class Avg Score</th>
                            <th>Class Lowest Score</th>
                            <th>Class Highest Score</th>
                            <th>1st Term Avg</th>
                            <th>2nd Term Avg</th>
                            <th>Year Avg</th>
                            <th>Grade</th>
                            <th>Position</th>
                            <th>Teacher's Comment</th>
                            <th>Sign</th>
                        </tr>
                        <tr>
                            <th class="subject-name">Max. Obtainable Mark</th>
                            <th>100%</th>
                            <th>100%</th>
                            <th>100%</th>
                            <th>100%</th>
                            <th>100%</th>
                            <th>100%</th>
                            <th>-</th>
                            <th>-</th>
                            <th>-</th>
                            <th>-</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($courses as $course)
                            @php
                                $classAvg = 0;
                                $classLowest = 0;
                                $classHighest = 0;
                                $firstTermAvg = $annualSummaryData[$course->subject_id]['first_term_avg'] ?? 0;
                                $secondTermAvg = $annualSummaryData[$course->subject_id]['second_term_avg'] ?? 0;
                                $yearAvg = $annualSummaryData[$course->subject_id]['year_avg'] ?? 0;

                                // Get class statistics from termSummary
                                foreach($termSummary as $summary) {
                                    $score = $course->scoreBoard->firstWhere('result_section_type_id', $summary->id);
                                    if ($score) {
                                        if ($summary->calc_pattern == 'class_average') $classAvg = $score->score;
                                        elseif ($summary->calc_pattern == 'class_lowest_score') $classLowest = $score->score;
                                        elseif ($summary->calc_pattern == 'class_highest_score') $classHighest = $score->score;
                                    }
                                }

                                $grade = '';
                                if ($yearAvg >= 75) $grade = 'A1';
                                elseif ($yearAvg >= 70) $grade = 'B2';
                                elseif ($yearAvg >= 65) $grade = 'B3';
                                elseif ($yearAvg >= 61) $grade = 'C4';
                                elseif ($yearAvg >= 55) $grade = 'C5';
                                elseif ($yearAvg >= 50) $grade = 'C6';
                                elseif ($yearAvg >= 45) $grade = 'D7';
                                elseif ($yearAvg >= 40) $grade = 'E8';
                                else $grade = 'F9';

                                $comment = $yearAvg >= 50 ? 'PASS' : 'FAIL';
                            @endphp
                            <tr>
                                <td class="subject-name">{{ $course->subject->subjectDepot->name ?? 'N/A' }}</td>
                                <td>{{ number_format($classAvg, 0) }}</td>
                                <td>{{ number_format($classLowest, 0) }}</td>
                                <td>{{ number_format($classHighest, 0) }}</td>
                                <td>{{ $firstTermAvg > 0 ? number_format($firstTermAvg, 0) : '-' }}</td>
                                <td>{{ $secondTermAvg > 0 ? number_format($secondTermAvg, 0) : '-' }}</td>
                                <td>{{ number_format($yearAvg, 0) }}</td>
                                <td class="grade-{{ strtolower($grade) }}">{{ $grade }}</td>
                                <td>
                                    @php
                                        $position = '-';
                                        foreach($studentSummary as $summary) {
                                            if ($summary->calc_pattern == 'position') {
                                                $score = $course->scoreBoard->firstWhere('result_section_type_id', $summary->id);
                                                $position = $score ? $score->score . 'TH' : '-';
                                                break;
                                            }
                                        }
                                        echo $position;
                                    @endphp
                                </td>
                                <td>{{ $comment }}</td>
                                <td>
                                    @if($course->subject->teacher && $course->subject->teacher->signature)
                                        <img src="{{ $course->subject->teacher->signature }}"
                                             alt="Teacher Signature"
                                             style="width: 40px; height: 20px; object-fit: contain;">
                                    @else
                                        {{ $course->subject->teacher->name ?? 'N/A' }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Skills Development and Behavioral Attributes -->
        <div class="section">
            <div class="section-title">Skills Development and Behavioral Attributes</div>
            <div class="section-content">
                <table class="behavioral-table">
                    <thead>
                        <tr>
                            <th rowspan="2">Category</th>
                            <th rowspan="2">Attribute</th>
                            <th colspan="3">Term Ratings</th>
                        </tr>
                        <tr>
                            <th>1st Term</th>
                            <th>2nd Term</th>
                            <th>3rd Term</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="category-header" rowspan="5">PERSONAL DEV.</td>
                            <td class="attribute-name">OBEDIENCE</td>
                            <td>{{ $behavioralData['obedience']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['obedience']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['obedience']['3rd'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="attribute-name">HONESTY</td>
                            <td>{{ $behavioralData['honesty']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['honesty']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['honesty']['3rd'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="attribute-name">SELF-CONTROL</td>
                            <td>{{ $behavioralData['self_control']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['self_control']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['self_control']['3rd'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="attribute-name">SELF-RELIANCE</td>
                            <td>{{ $behavioralData['self_reliance']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['self_reliance']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['self_reliance']['3rd'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="attribute-name">USE OF INITIATIVE</td>
                            <td>{{ $behavioralData['initiative']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['initiative']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['initiative']['3rd'] ?? '-' }}</td>
                        </tr>

                        <tr>
                            <td class="category-header" rowspan="5">SENSE OF RESP.</td>
                            <td class="attribute-name">PUNCTUALITY</td>
                            <td>{{ $behavioralData['punctuality']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['punctuality']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['punctuality']['3rd'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="attribute-name">NEATNESS</td>
                            <td>{{ $behavioralData['neatness']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['neatness']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['neatness']['3rd'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="attribute-name">PERSEVERANCE</td>
                            <td>{{ $behavioralData['perseverance']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['perseverance']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['perseverance']['3rd'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="attribute-name">ATTENDANCE</td>
                            <td>{{ $behavioralData['attendance']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['attendance']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['attendance']['3rd'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="attribute-name">ATTENTIVENESS</td>
                            <td>{{ $behavioralData['attentiveness']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['attentiveness']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['attentiveness']['3rd'] ?? '-' }}</td>
                        </tr>

                        <tr>
                            <td class="category-header" rowspan="5">SOCIAL DEV.</td>
                            <td class="attribute-name">COURTESY/POLITENESS</td>
                            <td>{{ $behavioralData['courtesy']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['courtesy']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['courtesy']['3rd'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="attribute-name">CONSIDERATION FOR OTHERS</td>
                            <td>{{ $behavioralData['consideration']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['consideration']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['consideration']['3rd'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="attribute-name">SOCIABILITY/TEAM PLAYER</td>
                            <td>{{ $behavioralData['sociability']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['sociability']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['sociability']['3rd'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="attribute-name">PROMPTNESS IN DOING WORK</td>
                            <td>{{ $behavioralData['promptness']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['promptness']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['promptness']['3rd'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="attribute-name">ACCEPTS RESPONSIBILITIES</td>
                            <td>{{ $behavioralData['responsibility']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['responsibility']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['responsibility']['3rd'] ?? '-' }}</td>
                        </tr>

                        <tr>
                            <td class="category-header" rowspan="5">PSYCHOMOTOR (SKILLS) DEV.</td>
                            <td class="attribute-name">READING AND WRITING SKILLS</td>
                            <td>{{ $behavioralData['reading_writing']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['reading_writing']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['reading_writing']['3rd'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="attribute-name">VERBAL COMMUNICATION</td>
                            <td>{{ $behavioralData['verbal_communication']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['verbal_communication']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['verbal_communication']['3rd'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="attribute-name">SPORT AND GAME</td>
                            <td>{{ $behavioralData['sport_game']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['sport_game']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['sport_game']['3rd'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="attribute-name">INQUISITIVENESS</td>
                            <td>{{ $behavioralData['inquisitiveness']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['inquisitiveness']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['inquisitiveness']['3rd'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="attribute-name">DEXTERITY (MUSICAL & ART MATERIALS)</td>
                            <td>{{ $behavioralData['dexterity']['1st'] ?? '-' }}</td>
                            <td>{{ $behavioralData['dexterity']['2nd'] ?? '-' }}</td>
                            <td>{{ $behavioralData['dexterity']['3rd'] ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rating Keys -->
        <div class="rating-keys">
            <h4>Keys to Ratings on Observable Behaviour</h4>
            <div class="rating-list">
                5.) Maintains an excellent degree of observable traits<br>
                4.) Maintains high level of observable traits<br>
                3.) Acceptable level of observable traits<br>
                2.) Shows minimal regards for observable traits<br>
                1.) Has no regard for observable traits
            </div>
        </div>

        <!-- Comments Section -->
        <div class="comments-section">
            <div class="section-title">Remarks and Conclusion</div>
            <div class="comment-box">
                <div class="comment-title">Class Teacher's Comments</div>
                <div class="comment-content">{{ $studentComment->comment ?? 'No comment available.' }}</div>
                <div class="signature-line">Signature (Class Teacher)</div>
            </div>
            <div class="comment-box">
                <div class="comment-title">Principal's Comments</div>
                <div class="comment-content">
                    <span class="promotion-status">
                        @php
                            if ($percent >= 50) echo 'PROMOTED';
                            else echo 'REPEAT';
                        @endphp
                    </span>
                    {{ $principalComment ?? 'No comment available.' }}
                </div>
                <div class="signature-line">Signature / School Stamp and Date</div>
            </div>
            <div class="comment-box">
                <div class="comment-title">Parent's Name</div>
                <div class="signature-line">Parent's Name</div>
            </div>
        </div>
    </div>
</body>
</html>
