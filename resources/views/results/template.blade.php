@php
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

    // Helper function to get principal comment based on total percentage
    function getPrincipalComment($totalPercentage) {
        return match (true) {
            $totalPercentage >= 51 => 'PROMOTED',
            $totalPercentage >= 41 => 'PROMOTED ON TRIAL',
            default => 'ADVISED TO REPEAT'
        };
    }
@endphp
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
            width: 80px;
            text-align: center;
        }

        .header-center {
            text-align: center;
            padding: 0 8px; /* Reduced padding */
            width: auto;
        }

        .header-right {
            width: 80px;
            text-align: center;
        }

        .school-logo {
            width: 60px; /* Increased size */
            height: 60px;
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

        .student-picture {
            width: 60px; /* Match school logo size */
            height: 60px;
            border-radius: 50%;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            border: 2px solid #ddd;
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

        /* Academic Performance Table - Updated with larger font */
        .academic-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 12px; /* Increased to 12px as requested */
            table-layout: fixed;
        }

        .academic-table th,
        .academic-table td {
            border: 1px solid #000;
            padding: 3px; /* Increased padding for larger font */
            text-align: center;
            font-size: 12px; /* Increased to 12px */
            line-height: 1.2;
            word-wrap: break-word;
            overflow: hidden;
        }

        .academic-table th {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 12px; /* Increased to 12px */
            padding: 4px 3px;
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

        /* Rating Table for side-by-side layout */
        .rating-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 8px;
            font-weight: bold;
        }

        .rating-table th,
        .rating-table td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
        }

        .rating-table th {
            background: #f0f0f0;
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
                                  <div class="student-picture">
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

        <!-- Student Information and Attendance Side by Side -->
        <div class="student-section" style="display: flex; gap: 10px; margin-bottom: 10px;">
            <!-- Student Personal Data - Left Side -->
            <div class="student-data" style="flex: 1;">
                <div class="section-header">STUDENT'S PERSONAL DATA</div>
                <table class="info-table">
                    <tr>
                        <td class="info-label">Name:</td>
                        <td>{{ $student->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Admission No:</td>
                        <td>{{ $student->registration_number ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Class:</td>
                        <td>{{ $student->class->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Arm:</td>
                        <td>{{ $student->arm->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Gender:</td>
                        <td>{{ ucfirst($student->gender ?? 'N/A') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Date of Birth:</td>
                        <td>{{ $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Guardian Name:</td>
                        <td>{{ $student->guardian->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Guardian Phone:</td>
                        <td>{{ $student->guardian->phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Address:</td>
                        <td>{{ $student->address ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>

            <!-- Attendance and Terminal Duration - Right Side -->
            <div class="attendance-data" style="flex: 1;">
                <div class="section-header">ATTENDANCE</div>
                <table class="duration-table">
                    <tr>
                        <th>Times Sch. Opened</th>
                        <th>Times Present</th>
                        <th>Times Absent</th>
                    </tr>
                    <tr>
                        <td>{{ $studentAttendance->expected_present ?? 0 }}</td>
                        <td>{{ $studentAttendance->total_present ?? 0 }}</td>
                        <td class="text-red">{{ $studentAttendance->total_absent ?? 0 }}</td>
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

        {{-- Academic Performance Table --}}
        <div class="academic-performance">
            <h3>ACADEMIC PERFORMANCE</h3>

            {{-- Debug: Show data structure --}}
            @if(config('app.debug'))
                <div style="background: #f0f0f0; padding: 5px; margin: 5px 0; font-size: 8px;">
                    <strong>Debug Info:</strong><br>
                    Subjects Count: {{ count($subjects ?? []) }}<br>
                    CalculatedData Subjects Count: {{ count($calculatedData['subjects'] ?? []) }}<br>
                    @if(isset($subjects) && count($subjects) > 0)
                        First Subject: {{ json_encode($subjects[0]) }}<br>
                    @endif
                    @if(isset($calculatedData['subjects']) && count($calculatedData['subjects']) > 0)
                        First Calculated Subject: {{ json_encode($calculatedData['subjects'][0]) }}<br>
                    @endif
                </div>
            @endif

            <table class="performance-table">
                <thead>
                    @php
                        // Get subjects data from the correct source
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

                        // Get all unique score types from subjects data
                        $allScoreTypes = [];
                        if (count($subjectsData) > 0) {
                            foreach ($subjectsData as $subject) {
                                if (isset($subject['scores'])) {
                                    // Handle both array and object formats
                                    if (is_array($subject['scores'])) {
                                        if (isset($subject['scores'][0])) {
                                            // Array of score objects
                                            foreach ($subject['scores'] as $score) {
                                                if (isset($score['code']) && isset($score['calc_pattern']) && $score['calc_pattern'] === 'input') {
                                                    $allScoreTypes[$score['code']] = strtoupper($score['code']);
                                                }
                                            }
                                        } else {
                                            // Associative array (new format)
                                            foreach ($subject['scores'] as $key => $value) {
                                                $allScoreTypes[$key] = strtoupper($key);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $scoreTypes = array_keys($allScoreTypes);

                        // Ensure we always have at least one score type to prevent table structure issues
                        if (empty($scoreTypes)) {
                            $scoreTypes = ['SCORE'];
                            $allScoreTypes = ['SCORE' => 'SCORE'];
                        }

                        $totalColumns = count($scoreTypes) + 2; // +2 for SUBJECT and REMARK columns
                    @endphp
                    <tr>
                        <th>SUBJECT</th>
                        @foreach($scoreTypes as $scoreType)
                            <th>{{ strtoupper($scoreType) }}</th>
                        @endforeach
                        <th>REMARK</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($subjectsData) > 0)
                        @foreach($subjectsData as $subject)
                            @php
                                $subjectTotal = $subject['total'] ?? 0;
                                $subjectGrade = $subject['grade'] ?? 'F9';
                                $gradeClass = getGradeClass($subjectGrade);
                                $gradeRemark = getGradeRemark($subjectGrade);
                            @endphp
                            <tr>
                                <td class="subject-name">{{ Str::limit($subject['subject_name'] ?? 'Subject', 15) }}</td>

                                {{-- Display scores in dynamic columns --}}
                                @foreach($scoreTypes as $scoreType)
                                    <td class="text-center">
                                        @if(isset($subject['scores']))
                                            @php
                                                $scoreValue = 'N/A';

                                                if (is_array($subject['scores'])) {
                                                    if (isset($subject['scores'][0])) {
                                                        // Array of score objects
                                                        foreach ($subject['scores'] as $score) {
                                                            if (isset($score['code']) && $score['code'] === $scoreType) {
                                                                $scoreValue = $score['score'] ?? 'N/A';
                                                                break;
                                                            }
                                                        }
                                                    } else {
                                                        // Associative array (new format)
                                                        $scoreValue = $subject['scores'][$scoreType] ?? 'N/A';
                                                    }
                                                }
                                            @endphp
                                            {{ $scoreValue }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                @endforeach

                                <td class="text-center">{{ $gradeRemark }}</td>
                            </tr>
                        @endforeach

                        {{-- Summary row with simplified structure --}}
                        <tr class="summary-row">
                            <td class="subject-name">NO. IN CLASS: {{ $summary['total_students'] ?? 'N/A' }}</td>
                            <td colspan="{{ count($scoreTypes) }}">TOTAL: {{ $studentResult->total_score ?? $studentResult->calculation_total ?? $totalScore ?? 'N/A' }}</td>
                            <td>POS: {{ $summary['position'] ?? 'N/A' }} - {{ $percent ?? 'N/A' }}%</td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="{{ $totalColumns }}">No subjects data available</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

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

        <!-- Rating Key and Psychomotor Side by Side -->
        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
            <!-- Rating Key - Left Side -->
            <div style="flex: 1;">
                <div class="section-header">KEYS TO RATINGS ON OBSERVABLE BEHAVIOUR</div>
                <table class="rating-table">
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

            <!-- Skills Development - Right Side -->
            @if(isset($psychomotorCategory) && $psychomotorCategory && $psychomotorCategory->count() > 0)
                <div style="flex: 1;">
                    <div class="section-header">SKILLS DEVELOPMENT AND BEHAVIORAL ATTRIBUTES</div>
                    <table class="skills-table">
                        <thead>
                            <tr>
                                @foreach($psychomotorCategory as $category)
                                    <th>{{ $category->name }}</th>
                                    <th>Rating</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Get the maximum number of skills across all categories
                                $maxSkills = 0;
                                foreach($psychomotorCategory as $category) {
                                    if ($category->psychomotors) {
                                        $skillCount = $category->psychomotors->count();
                                        if($skillCount > $maxSkills) {
                                            $maxSkills = $skillCount;
                                        }
                                    }
                                }
                            @endphp

                            @if($maxSkills > 0)
                                @for($i = 0; $i < $maxSkills; $i++)
                                    <tr>
                                        @foreach($psychomotorCategory as $category)
                                            @php
                                                $skill = null;
                                                $skillName = '';
                                                $rating = 'N/A';

                                                if ($category->psychomotors && $category->psychomotors->count() > $i) {
                                                    $skill = $category->psychomotors->get($i);
                                                    $skillName = $skill ? $skill->skill : '';
                                                }

                                                // Get student rating for this skill for current term only
                                                if ($skill && isset($psychomotorData) && $psychomotorData && $psychomotorData->count() > 0) {
                                                    try {
                                                        $studentRating = $psychomotorData->firstWhere('psychomotor_id', $skill->id);
                                                        if ($studentRating && isset($studentRating->rating)) {
                                                            $rating = $studentRating->rating;
                                                        }
                                                    } catch (\Exception $e) {
                                                        $rating = 'N/A';
                                                    }
                                                }
                                            @endphp

                                            <td class="skills-category">{{ $skillName ? $skillName . ':' : '&nbsp;' }}</td>
                                            <td class="text-center">{{ $rating }}</td>
                                        @endforeach
                                    </tr>
                                @endfor
                            @else
                                <tr>
                                    <td colspan="{{ max(1, $psychomotorCategory->count() * 2) }}" style="text-align: center;">No skills data available</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Psychomotor Section --}}
        @if($psychomotorCategory && $psychomotorCategory->count() > 0 && $psychomotorData && $psychomotorData->count() > 0)
        <div class="psychomotor-section">
            <h3>PSYCHOMOTOR DEVELOPMENT</h3>
            <table class="psychomotor-table">
                <thead>
                    <tr>
                        <th>SKILLS</th>
                        @foreach($psychomotorCategory as $category)
                            <th>{{ strtoupper($category->name) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($psychomotorCategory as $category)
                        @if($category->psychomotors && $category->psychomotors->count() > 0)
                            @foreach($category->psychomotors as $psychomotor)
                                <tr>
                                    <td>{{ $psychomotor->name ?? 'N/A' }}</td>
                                    @foreach($psychomotorCategory as $cat)
                                        <td class="text-center">
                                            @php
                                                $rating = $psychomotorData->firstWhere('psychomotor_id', $psychomotor->id);
                                                $ratingValue = $rating ? $rating->rating : 'N/A';
                                            @endphp
                                            {{ $ratingValue }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Comments Section -->
        <div class="comments-section">
            <div class="section-header">REMARKS AND CONCLUSION</div>

            <div class="comments-layout">
                <div class="comments-left">
                    <div class="comment-label">Class Teacher's Comments:</div>
                    <div class="comment-box">
                        {{ $studentResult->teacher_comment ?? 'Student demonstrates good academic potential. Keep up the good work and continue to strive for excellence.' }}
                    </div>

                    <div class="comment-label">Principal's Comments:</div>
                    <div class="comment-box">
                        @php
                            // Calculate total percentage from student result
                            $totalPercentage = 0;
                            if (isset($studentResult) && $studentResult) {
                                $totalScore = $studentResult->total_score ?? $studentResult->calculation_total ?? 0;
                                $maxPossibleScore = 100; // Assuming 100 is the maximum possible score
                                $totalPercentage = ($totalScore / $maxPossibleScore) * 100;
                            }
                        @endphp
                        <strong>{{ getPrincipalComment($totalPercentage) }}</strong>
                    </div>

                    <div class="comment-label">Parent's Name:</div>
                    <div style="border-bottom: 1px solid #000; height: 15px; margin: 3px 0;"></div>
                </div>

                <div class="comments-right">
                    <div class="comment-label">Signature (Class Teacher)</div>
                    <div class="signature-line"></div>

                    <div class="comment-label">Principal's Signature</div>
                    <div class="signature-line">
                        @if($school->principal_sign)
                            <img src="{{ Storage::disk('s3')->url($school->principal_sign) }}" alt="Principal Signature" style="max-width: 100%; max-height: 30px; object-fit: contain;">
                        @endif
                    </div>
                    <div style="text-align: center; font-size: 7px; margin-top: 3px;">
                        {{ now()->format('d/m/Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
