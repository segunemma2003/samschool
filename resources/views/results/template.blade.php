<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Report Card - {{ $student->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4 portrait;
            margin: 12mm 8mm 12mm 8mm; /* top, right, bottom, left */
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px; /* Reduced base font size */
            line-height: 1.1;
            color: #000;
            background: white;
            margin: 0;
            padding: 0;
        }

        .report-container {
            width: 100%;
            max-width: 190mm; /* Reduced to prevent cutting */
            margin: 0 auto;
            padding: 3mm; /* Reduced padding */
            background: white;
        }

        /* Header Section - Optimized */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 8px; /* Reduced margin */
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            table-layout: fixed;
        }

        .header-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .header-left {
            width: 65px;
            text-align: center;
        }

        .school-logo {
            width: 50px; /* Reduced size */
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(45deg, #8B5CF6, #F59E0B);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px; /* Reduced */
            margin: 0 auto;
        }

        .header-center {
            text-align: center;
            padding: 0 8px; /* Reduced padding */
        }

        .school-name {
            font-size: 16px; /* Reduced */
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .school-address {
            font-size: 8px; /* Reduced */
            margin-bottom: 4px;
            line-height: 1.2;
        }

        .report-title {
            font-size: 12px; /* Reduced */
            font-weight: bold;
            border: 2px solid #000;
            padding: 3px 6px; /* Reduced */
            display: inline-block;
            margin-top: 2px;
        }

        .header-right {
            width: 70px;
            text-align: center;
        }

        .student-photo-header {
            width: 60px;
            height: 60px;
            border: 2px solid #000;
            border-radius: 8px;
            overflow: hidden;
            margin: 0 auto;
        }

        .term-box {
            border: 2px solid #000;
            padding: 5px; /* Reduced */
            text-align: center;
            min-width: 65px; /* Reduced */
        }

        .term-label {
            font-size: 9px; /* Reduced */
            font-weight: bold;
        }

        .term-name {
            font-size: 10px; /* Reduced */
            font-weight: bold;
            margin-top: 2px;
            line-height: 1.1;
        }

        /* Student Info Section - Optimized */
        .student-section {
            display: table;
            width: 100%;
            margin-bottom: 8px; /* Reduced */
            table-layout: fixed;
        }

        .student-data {
            display: table-cell;
            width: 65%; /* Adjusted - increased since we removed photo cell */
            vertical-align: top;
            padding-right: 5px;
        }

        .attendance-terminal-section {
            display: table-cell;
            width: 35%; /* Adjusted */
            vertical-align: top;
        }

        .section-header {
            background: #f0f0f0;
            padding: 3px; /* Reduced */
            font-weight: bold;
            text-align: center;
            border: 1px solid #000;
            font-size: 9px; /* Reduced */
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            border: 1px solid #000;
            padding: 2px 3px; /* Reduced */
            font-size: 8px; /* Reduced */
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            width: 35%;
            background: #f8f8f8;
        }

        .barcode {
            height: 18px; /* Reduced */
            background: repeating-linear-gradient(90deg, #000 0px, #000 1px, transparent 1px, transparent 2px);
        }

        /* Duration Table - Same width as attendance */
        .duration-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px; /* Reduced */
            margin-top: 5px; /* Space between attendance and terminal duration */
        }

        .duration-table th,
        .duration-table td {
            border: 1px solid #000;
            padding: 2px; /* Reduced */
            text-align: center;
            font-size: 7px; /* Reduced */
        }

        .duration-table th {
            background: #f0f0f0;
            font-weight: bold;
        }

        /* Academic Performance Table - Main optimization area */
        .academic-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0; /* Reduced */
            font-size: 7px; /* Significantly reduced */
            table-layout: fixed; /* Important for preventing overflow */
        }

        .academic-table th,
        .academic-table td {
            border: 1px solid #000;
            padding: 1px; /* Minimized */
            text-align: center;
            font-size: 6px; /* Very small to fit */
            line-height: 1.1;
            word-wrap: break-word;
            overflow: hidden;
        }

        .academic-table th {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 5px; /* Very small headers */
            padding: 2px 1px;
        }

        /* Column widths to prevent overflow */
        .academic-table .subject-col {
            width: 18%; /* Subject name */
        }

        .academic-table .score-col {
            width: 6%; /* Score columns */
        }

        .academic-table .grade-col {
            width: 5%; /* Grade column */
        }

        .academic-table .position-col {
            width: 6%; /* Position column */
        }

        .academic-table .average-col {
            width: 7%; /* Average columns */
        }

        .academic-table .comment-col {
            width: 12%; /* Teacher comment */
        }

        .academic-table .sign-col {
            width: 8%; /* Signature */
        }

        .subject-name {
            text-align: left !important;
            font-weight: bold;
            padding-left: 2px !important;
            font-size: 6px !important;
        }

        .max-mark {
            background: #f5f5f5;
            font-weight: bold;
        }

        .fail-mark {
            color: #dc2626;
            font-weight: bold;
        }

        .pass-mark {
            color: #16a34a;
        }

        .credit-mark {
            color: #2563eb;
        }

        /* Grade Scale - Compact */
        .grade-scale {
            text-align: center;
            margin: 5px 0; /* Reduced */
            font-size: 8px; /* Reduced */
            font-weight: bold;
        }

        .grade-scale table {
            width: 100%;
            border-collapse: collapse;
        }

        .grade-scale th {
            border: 1px solid #000;
            padding: 2px; /* Reduced */
            background: #f0f0f0;
            font-size: 7px; /* Reduced */
        }

        /* Skills Section - Compact */
        .skills-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6px; /* Very small */
            margin: 5px 0; /* Reduced */
            table-layout: fixed;
        }

        .skills-table th,
        .skills-table td {
            border: 1px solid #000;
            padding: 1px; /* Minimized */
            text-align: center;
            font-size: 5px; /* Very small */
            line-height: 1.1;
        }

        .skills-table th {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 4px; /* Very small headers */
        }

        .skills-category {
            text-align: left !important;
            font-weight: bold;
            padding-left: 2px !important;
            font-size: 5px !important;
            width: 20%; /* Fixed width */
        }

        /* Rating Scale - Compact */
        .rating-scale {
            margin: 5px 0; /* Reduced */
            font-size: 6px; /* Reduced */
        }

        .rating-scale table {
            width: 100%;
            border-collapse: collapse;
        }

        .rating-scale th,
        .rating-scale td {
            border: 1px solid #000;
            padding: 2px 3px; /* Reduced */
            text-align: center;
            font-size: 5px; /* Reduced */
        }

        .rating-scale th {
            background: #f0f0f0;
            font-weight: bold;
        }

        /* Comments Section - Compact */
        .comments-section {
            margin: 8px 0; /* Reduced */
        }

        .comment-box {
            border: 2px dashed #000;
            padding: 5px; /* Reduced */
            margin: 3px 0; /* Reduced */
            min-height: 25px; /* Reduced */
            font-size: 8px; /* Reduced */
        }

        .comment-label {
            font-weight: bold;
            margin-bottom: 2px;
            font-size: 8px; /* Reduced */
        }

        .comments-layout {
            display: table;
            width: 100%;
        }

        .comments-left {
            display: table-cell;
            width: 65%;
            vertical-align: top;
            padding-right: 5px;
        }

        .comments-right {
            display: table-cell;
            width: 35%;
            vertical-align: top;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            height: 20px; /* Reduced */
            margin: 5px 8px 3px 8px; /* Reduced */
        }

        /* Summary Stats */
        .summary-row {
            background: #f0f0f0;
            font-weight: bold;
        }

        /* Text utilities */
        .text-red {
            color: #dc2626;
        }

        .text-green {
            color: #16a34a;
        }

        .text-blue {
            color: #2563eb;
        }

        /* Print Optimizations */
        @media print {
            body {
                margin: 0;
                padding: 0;
                font-size: 9px; /* Smaller for print */
            }

            .report-container {
                padding: 2mm;
                max-width: 190mm;
            }

            .header {
                page-break-inside: avoid;
            }

            .academic-table {
                page-break-inside: avoid;
                font-size: 6px; /* Even smaller for print */
            }

            .skills-table {
                page-break-inside: avoid;
                font-size: 5px;
            }
        }

        /* Responsive adjustments for very narrow content */
        @media (max-width: 190mm) {
            .academic-table {
                font-size: 5px;
            }

            .academic-table th,
            .academic-table td {
                padding: 0.5px;
                font-size: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <!-- Header -->
        <div class="header">
            <div class="header-cell header-left">
                <div class="school-logo">
                    @if($school->school_logo)
                        <img src="{{ Storage::disk('s3')->url($school->school_logo) }}" alt="School Logo" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    @else
                        {{ strtoupper(substr($school->school_name ?? 'SCHOOL', 0, 2)) }}
                    @endif
                </div>
            </div>
            <div class="header-cell header-center">
                <div class="school-name">{{ strtoupper($school->school_name ?? 'SCHOOL NAME') }}</div>
                <div class="school-address">
                    {{ $school->school_address ?? 'SCHOOL ADDRESS' }}<br>
                    TEL: {{ $school->school_phone ?? 'PHONE NUMBER' }}<br>
                    EMAIL: {{ $school->email ?? 'EMAIL' }} WEBSITE: {{ $school->school_website ?? 'WEBSITE' }}
                </div>
                <div class="report-title">Continuous Assessment Report {{ $academy->title ?? 'ACADEMIC YEAR' }}</div>
            </div>
            <div class="header-cell header-right">
                <div class="student-photo-header">
                    @if($student->avatar)
                        <img src="{{ Storage::disk('s3')->url($student->avatar) }}" alt="Student Photo" style="width: 100%; height: 100%; border-radius: 8px; object-fit: cover;">
                    @else
                        <div style="width: 100%; height: 100%; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 8px; color: #666;">
                            NO PHOTO
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Student Information -->
        <div class="student-section">
            <div class="student-data">
                <div class="section-header">STUDENT'S PERSONAL DATA</div>
                <table class="info-table">
                    <tr>
                        <td class="info-label">Name:</td>
                        <td>{{ $student->name ?? 'N/A' }}</td>
                        <td class="info-label">Admission No:</td>
                        <td>{{ $student->registration_number ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Class:</td>
                        <td>{{ $student->class->name ?? 'N/A' }}</td>
                        <td class="info-label">Arm:</td>
                        <td>{{ $student->arm->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Gender:</td>
                        <td>{{ ucfirst($student->gender ?? 'N/A') }}</td>
                        <td class="info-label">Date of Birth:</td>
                        <td>{{ $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Guardian Name:</td>
                        <td>{{ $student->guardian->name ?? 'N/A' }}</td>
                        <td class="info-label">Guardian Phone:</td>
                        <td>{{ $student->guardian->phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Address:</td>
                        <td colspan="3">{{ $student->address ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Attendance and Terminal Duration -->
        <div class="attendance-section">
            <div class="attendance-data">
                <div class="section-header">ATTENDANCE</div>
                <table class="duration-table">
                    <tr>
                        <th>Times Sch. Opened</th>
                        <th>Times Present</th>
                        <th>Times Absent</th>
                    </tr>
                    <tr>
                        <td>{{ $studentAttendance->expected_present ?? 130 }}</td>
                        <td>{{ $studentAttendance->total_present ?? 108 }}</td>
                        <td class="text-red">{{ $studentAttendance->total_absent ?? 22 }}</td>
                    </tr>
                </table>

                <!-- Terminal Duration Section -->
                <div class="section-header" style="margin-top: 5px;">TERMINAL DURATION</div>
                <table class="duration-table">
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

        <!-- Academic Performance - Optimized Table -->
        <div class="section-header" style="margin-top: 8px;">ACADEMIC PERFORMANCE</div>

        <table class="academic-table">
            <colgroup>
                <col class="subject-col">
                <col class="score-col">
                <col class="score-col">
                <col class="score-col">
                <col class="grade-col">
                @if($showPosition)
                <col class="position-col">
                @endif
                <col class="average-col">
                <col class="average-col">
                <col class="average-col">
                <col class="comment-col">
                <col class="sign-col">
            </colgroup>
            <thead>
                <tr>
                    <th rowspan="2"></th>
                    <th colspan="3">MARKS OBTAINED</th>
                    <th colspan="{{ $showPosition ? '5' : '4' }}">ANNUAL SUMMARY</th>
                    <th rowspan="2">Teacher's<br>Comment</th>
                    <th rowspan="2">Sign.</th>
                </tr>
                <tr>
                    <th>CA</th>
                    <th>Exam</th>
                    <th>Total</th>
                    <th>Grade</th>
                    @if($showPosition)
                    <th>Pos.</th>
                    @endif
                    <th>Class<br>Avg</th>
                    <th>High</th>
                    <th>Low</th>
                </tr>
                <tr class="max-mark">
                    <td class="subject-name">Max. Obtainable Mark</td>
                    <td>40%</td>
                    <td>60%</td>
                    <td>100%</td>
                    <td></td>
                    @if($showPosition)
                    <td></td>
                    @endif
                    <td>100%</td>
                    <td>100%</td>
                    <td>100%</td>
                    <td></td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                @php
                    // Get student results from StudentResult model
                    $studentResult = \App\Models\StudentResult::where('student_id', $student->id)
                        ->where('term_id', $term->id)
                        ->where('academic_year_id', $academy->id)
                        ->first();

                    // Get calculated_data (already an array due to model cast)
                    $calculatedData = $studentResult ? $studentResult->calculated_data : null;
                    $summary = $calculatedData['summary'] ?? [];
                    $subjects = $calculatedData['subjects'] ?? [];

                    // Extract summary data
                    $totalScore = $summary['total_score'] ?? 0;
                    $totalSubjects = $summary['total_subjects'] ?? 0;
                    $overallGrade = $summary['grade'] ?? 'F9';
                    $averageScore = $summary['average'] ?? 0;
                    $remarks = $summary['remarks'] ?? 'NO COMMENT';

                    // Calculate percentage
                    $percentage = $totalSubjects > 0 ? round($averageScore, 1) : 0;

                    // Get position from summary data
                    $position = $summary['position'] ?? 'N/A';

                    // Helper function to get grade colors
                    function getGradeClass($grade) {
                        $gradeNumber = (int) filter_var($grade, FILTER_SANITIZE_NUMBER_INT);
                        return match (true) {
                            $gradeNumber <= 2 => 'text-green', // A1, A2
                            $gradeNumber <= 4 => 'text-blue',  // B3, B4
                            $gradeNumber <= 6 => 'pass-mark',  // C5, C6
                            default => 'fail-mark'            // D7, E8, F9
                        };
                    }

                    // Helper function to get grade remarks
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

                @foreach($subjects as $subject)
                    @php
                        $subjectTotal = $subject['total'] ?? 0;
                        $subjectGrade = $subject['grade'] ?? 'F9';
                        $subjectScores = $subject['scores'] ?? [];

                        // Use the exact CA and Exam scores that were calculated and saved
                        $caScore = $subject['ca_score'] ?? 0;
                        $examScore = $subject['exam_score'] ?? 0;

                        // Fallback calculation if ca_score and exam_score are not available
                        if ($caScore == 0 && $examScore == 0) {
                            foreach($subjectScores as $score) {
                                if (stripos($score['type'] ?? '', 'ca') !== false ||
                                    stripos($score['type'] ?? '', 'test') !== false ||
                                    stripos($score['type'] ?? '', 'assignment') !== false) {
                                    $caScore += $score['score'] ?? 0;
                                } elseif (stripos($score['type'] ?? '', 'exam') !== false) {
                                    $examScore += $score['score'] ?? 0;
                                }
                            }

                            // If still no specific breakdown, assume 40/60 split
                            if ($caScore == 0 && $examScore == 0 && $subjectTotal > 0) {
                                $caScore = round($subjectTotal * 0.4);
                                $examScore = round($subjectTotal * 0.6);
                            }
                        }

                        $gradeClass = getGradeClass($subjectGrade);
                        $gradeRemark = getGradeRemark($subjectGrade);
                    @endphp
                    <tr>
                        <td class="subject-name">{{ Str::limit($subject['subject_name'] ?? 'Subject', 15) }}</td>
                        <td class="{{ $caScore < 20 ? 'text-red' : '' }}">{{ $caScore }}</td>
                        <td class="{{ $examScore < 30 ? 'text-red' : '' }}">{{ $examScore }}</td>
                        <td class="{{ $subjectTotal < 40 ? 'text-red' : '' }}">{{ $subjectTotal }}</td>
                        <td class="{{ $gradeClass }}">{{ $subjectGrade }}</td>
                        @if($showPosition)
                        <td>{{ $subject['position'] ?? '-' }}</td>
                        @endif
                        <td>{{ $subject['class_average'] ?? '-' }}</td>
                        <td>{{ $subject['highest_score'] ?? '-' }}</td>
                        <td>{{ $subject['lowest_score'] ?? '-' }}</td>
                        <td>{{ $gradeRemark }}</td>
                        <td>{{ Str::limit($subject['teacher_name'] ?? 'TEACHER', 8) }}</td>
                    </tr>
                @endforeach

                <tr class="summary-row">
                    <td class="subject-name">NO. IN CLASS: {{ $summary['total_students'] ?? $totalStudents ?? 'N/A' }}</td>
                    <td colspan="3">TOTAL: {{ $totalScore }}</td>
                    <td colspan="{{ $showPosition ? '5' : '4' }}">POS: {{ $position }} - {{ $percentage }}%</td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <!-- Grade Scale -->
        <div class="grade-scale">
            <table>
                <tr>
                    <th>A 70-100 (EXCELLENT)</th>
                    <th>C 50-69 (CREDIT)</th>
                    <th>P 40-49 (PASS)</th>
                    <th>F 0-39 (FAIL)</th>
                </tr>
            </table>
        </div>

        <!-- Skills Development - Only if data exists -->
        @if($psychomotorCategory && $psychomotorCategory->count() > 0)
            <div class="section-header">SKILLS DEVELOPMENT AND BEHAVIORAL ATTRIBUTES</div>

            <table class="skills-table">
                <thead>
                    <tr>
                        <th rowspan="2">PERSONAL DEV.</th>
                        <th colspan="3">1st 2nd 3rd Term</th>
                        <th rowspan="2">SENSE OF RESP.</th>
                        <th colspan="3">1st 2nd 3rd Term</th>
                        <th rowspan="2">SOCIAL DEV.</th>
                        <th colspan="3">1st 2nd 3rd Term</th>
                        <th rowspan="2">PSYCHOMOTOR DEV.</th>
                        <th colspan="3">1st 2nd 3rd Term</th>
                    </tr>
                    <tr>
                        <th>1</th><th>2</th><th>3</th>
                        <th>1</th><th>2</th><th>3</th>
                        <th>1</th><th>2</th><th>3</th>
                        <th>1</th><th>2</th><th>3</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $skillCategories = [
                            'PERSONAL DEV.' => ['OBEDIENCE', 'HONESTY', 'SELF-CONTROL', 'SELF-RELIANCE', 'USE OF INITIATIVE'],
                            'SENSE OF RESP.' => ['PUNCTUALITY', 'NEATNESS', 'PERSEVERANCE', 'ATTENDANCE', 'ATTENTIVENESS'],
                            'SOCIAL DEV.' => ['COURTESY', 'CONSIDERATIONS', 'SOCIABILITY', 'PROMPTNESS', 'RESPONSIBILITY'],
                            'PSYCHOMOTOR DEV.' => ['READING/WRITING', 'COMMUNICATION', 'SPORT/GAME', 'INQUISITIVENESS', 'DEXTERITY']
                        ];
                        $maxRows = 5;
                    @endphp

                    @for($i = 0; $i < $maxRows; $i++)
                        <tr>
                            @foreach(['PERSONAL DEV.', 'SENSE OF RESP.', 'SOCIAL DEV.', 'PSYCHOMOTOR DEV.'] as $category)
                                <td class="skills-category">{{ $skillCategories[$category][$i] ?? '' }}:</td>
                                @php
                                    // Get real psychomotor data for this category and skill
                                    $categoryData = $psychomotorCategory->where('name', $category)->first();
                                    $skillName = $skillCategories[$category][$i] ?? '';
                                    $rating = 'N/A';

                                    if ($categoryData && $skillName) {
                                        $psychomotorSkill = $categoryData->psychomotors->where('name', $skillName)->first();
                                        if ($psychomotorSkill) {
                                            $studentRating = $psychomotorData->get($categoryData->id)?->where('psychomotor_id', $psychomotorSkill->id)->first();
                                            $rating = $studentRating ? $studentRating->rating : 'N/A';
                                        }
                                    }
                                @endphp
                                <td>{{ $rating }}</td>
                                <td>{{ $rating }}</td>
                                <td>{{ $rating }}</td>
                            @endforeach
                        </tr>
                    @endfor
                </tbody>
            </table>
        @endif

        <!-- Rating Scale -->
        <div class="rating-scale">
            <div class="section-header">KEYS TO RATINGS ON OBSERVABLE BEHAVIOUR</div>
            <table>
                <tr>
                    <th>5.) Excellent degree of traits</th>
                    <th>4.) High level of traits</th>
                    <th>3.) Acceptable level</th>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center;">2.) Minimal regards for traits</td>
                    <td>1.) No regard for traits</td>
                </tr>
            </table>
        </div>

        <!-- Comments Section -->
        <div class="comments-section">
            <div class="section-header">REMARKS AND CONCLUSION</div>

            <div class="comments-layout">
                <div class="comments-left">
                    <div class="comment-label">Class Teacher's Comments:</div>
                    <div class="comment-box">
                        {{ $studentComment->teacher_comment ?? $remarks ?? 'Student demonstrates good academic potential. Keep up the good work and continue to strive for excellence.' }}
                    </div>

                    <div class="comment-label">Principal's Comments:</div>
                    <div class="comment-box">
                        <strong>{{ $studentComment->promotion_status ?? 'PROMOTED' }}</strong>
                        @php
                            // Generate principal comment based on performance
                            $principalComment = match (true) {
                                $percentage >= 80 => 'Excellent performance. Keep up the outstanding work!',
                                $percentage >= 70 => 'Very good performance. Continue to strive for excellence.',
                                $percentage >= 60 => 'Good performance. There is room for improvement.',
                                $percentage >= 50 => 'Fair performance. More effort is needed.',
                                $percentage >= 40 => 'Below average performance. Significant improvement required.',
                                default => $remarks ?? 'Poor performance. Serious attention needed.'
                            };
                        @endphp
                        {{ $principalComment }}
                    </div>

                    <div class="comment-label">Parent's Name:</div>
                    <div style="border-bottom: 1px solid #000; height: 15px; margin: 3px 0;"></div>
                </div>

                <div class="comments-right">
                    <div class="comment-label">Signature (Class Teacher)</div>
                    <div class="signature-line"></div>

                    <div class="comment-label">Signature / School Stamp and Date</div>
                    <div class="signature-line"></div>
                    <div style="text-align: center; font-size: 7px; margin-top: 3px;">
                        {{ now()->format('d/m/Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
