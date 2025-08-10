<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result - {{ $student->name }}</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.4;
            color: #333;
            background: #ffffff;
            padding: 15px;
            font-size: 11px;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
        }

        /* Header Section */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .school-info {
            flex: 1;
        }

        .school-logo {
            width: 60px;
            height: 60px;
            margin-bottom: 5px;
        }

        .school-name {
            font-size: 18px;
            font-weight: bold;
            color: #000;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .school-address {
            font-size: 10px;
            color: #333;
            margin-bottom: 2px;
        }

        .contact-info {
            font-size: 9px;
            color: #666;
        }

        .report-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
            text-transform: uppercase;
        }

        .class-term-info {
            text-align: right;
        }

        .class-box, .term-box {
            background: #000;
            color: #fff;
            padding: 5px 10px;
            margin-bottom: 5px;
            font-size: 12px;
            font-weight: bold;
        }

        /* Student Information */
        .student-section {
            display: flex;
            margin-bottom: 15px;
            border: 1px solid #000;
        }

        .student-data {
            flex: 2;
            padding: 10px;
        }

        .student-photo {
            flex: 1;
            padding: 10px;
            text-align: center;
        }

        .photo-placeholder {
            width: 80px;
            height: 100px;
            border: 1px solid #ccc;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 8px;
            color: #666;
        }

        .data-row {
            display: flex;
            margin-bottom: 5px;
        }

        .data-label {
            font-weight: bold;
            width: 100px;
            flex-shrink: 0;
        }

        .data-value {
            flex: 1;
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
            margin-bottom: 15px;
            font-size: 12px;
            font-weight: bold;
        }

        .stat-item {
            text-align: center;
            padding: 5px 10px;
            border: 1px solid #000;
            background: #f0f0f0;
        }

        /* Attendance Section */
        .attendance-section {
            margin-bottom: 15px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            background: #f0f0f0;
            padding: 5px;
            border: 1px solid #000;
            text-transform: uppercase;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .attendance-table th,
        .attendance-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-size: 10px;
        }

        .attendance-table th {
            background: #f0f0f0;
            font-weight: bold;
        }

        /* Academic Performance Tables */
        .performance-section {
            margin-bottom: 15px;
        }

        .performance-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            font-size: 9px;
        }

        .performance-table th,
        .performance-table td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
        }

        .performance-table th {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 8px;
        }

        .subject-name {
            text-align: left;
            font-weight: bold;
            width: 80px;
        }

        .grade-f {
            color: #ff0000;
            font-weight: bold;
        }

        .grade-p {
            color: #0000ff;
            font-weight: bold;
        }

        .grade-c {
            color: #008000;
            font-weight: bold;
        }

        .grade-a {
            color: #000000;
            font-weight: bold;
        }

        /* Behavioral Attributes */
        .behavioral-section {
            margin-bottom: 15px;
        }

        .behavioral-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            font-size: 8px;
        }

        .behavioral-table th,
        .behavioral-table td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
        }

        .behavioral-table th {
            background: #f0f0f0;
            font-weight: bold;
        }

        .category-header {
            background: #e0e0e0;
            font-weight: bold;
            text-align: left;
            padding: 3px;
        }

        .attribute-name {
            text-align: left;
            padding-left: 10px;
        }

        /* Rating Keys */
        .rating-keys {
            margin-bottom: 15px;
            border: 1px solid #000;
            padding: 5px;
        }

        .rating-keys h4 {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .rating-list {
            font-size: 8px;
            line-height: 1.3;
        }

        /* Comments Section */
        .comments-section {
            margin-bottom: 15px;
        }

        .comment-box {
            border: 1px dotted #000;
            padding: 10px;
            margin-bottom: 10px;
            min-height: 60px;
        }

        .comment-title {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .comment-content {
            font-size: 9px;
            line-height: 1.3;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 20px;
            padding-top: 5px;
            font-size: 8px;
            text-align: center;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 8px;
            color: #666;
        }

        .powered-by {
            display: inline-block;
            background: #ccc;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            line-height: 20px;
            text-align: center;
            font-size: 6px;
            margin-left: 5px;
        }

        /* Print styles */
        @media print {
            body {
                padding: 10px;
            }

            .header {
                margin-bottom: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="school-info">
                @if($school && $school->school_logo)
                    <img src="{{ $school->school_logo }}" alt="School Logo" class="school-logo">
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
        </div>

        <div class="report-title">Continuous Assessment Report {{ $academy->title ?? 'ACADEMIC YEAR' }}</div>

        <!-- Student Information -->
        <div class="student-section">
            <div class="student-data">
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
                    <img src="{{ $student->photo }}" alt="Student Photo" style="width: 80px; height: 100px; object-fit: cover;">
                @else
                    <div class="photo-placeholder">STUDENT PHOTO</div>
                @endif
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
        <div class="attendance-section">
            <div class="section-title">Attendance</div>
            <table class="attendance-table">
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

        <!-- Terminal Duration -->
        <div class="attendance-section">
            <div class="section-title">Terminal Duration ({{ $term->duration ?? 'N/A' }} WEEKS)</div>
            <table class="attendance-table">
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

        <!-- Academic Performance - Current Term -->
        <div class="performance-section">
            <div class="section-title">Academic Performance - Marks Obtained ({{ $term->name ?? 'Current Term' }})</div>
            <table class="performance-table">
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

        <!-- Annual Summary -->
        <div class="performance-section">
            <div class="section-title">Academic Performance - Annual Summary</div>
            <table class="performance-table">
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

        <!-- Skills Development and Behavioral Attributes -->
        <div class="behavioral-section">
            <div class="section-title">Skills Development and Behavioral Attributes</div>
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
                    <span style="background: #ccc; padding: 2px 5px; margin-right: 5px;">
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
