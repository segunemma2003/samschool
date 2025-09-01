<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Continuous Assessment Report</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
            line-height: 1.2;
        }

        .header {
            text-align: center;
            border: 2px solid #000;
            padding: 8px;
            margin-bottom: 5px;
            position: relative;
        }

        .school-logo {
            position: absolute;
            left: 10px;
            top: 10px;
            width: 50px;
            height: 50px;
            background: #8B4B8C;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .term-badge {
            position: absolute;
            right: 10px;
            top: 10px;
            background: #333;
            color: white;
            padding: 5px 8px;
            font-size: 10px;
            font-weight: bold;
        }

        .school-name {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }

        .school-address {
            font-size: 9px;
            margin: 2px 0;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin: 8px 0 5px 0;
        }

        .session {
            font-size: 12px;
            margin: 2px 0;
        }

        .main-container {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .left-section, .right-section {
            display: table-cell;
            vertical-align: top;
            padding: 5px;
        }

        .left-section {
            width: 60%;
        }

        .right-section {
            width: 40%;
        }

        .section-title {
            background: #333;
            color: white;
            padding: 3px 8px;
            font-weight: bold;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
        }

        th, td {
            border: 1px solid #333;
            padding: 3px 5px;
            text-align: left;
        }

        th {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 9px;
        }

        .marks-table th {
            font-size: 8px;
            padding: 2px 3px;
        }

        .marks-table td {
            text-align: center;
            padding: 2px 3px;
            font-size: 9px;
        }

        .student-info td:first-child {
            font-weight: bold;
            width: 30%;
        }

        .attendance-info td:first-child {
            font-weight: bold;
            width: 60%;
        }

        .barcode {
            text-align: center;
            font-family: "Courier New", monospace;
            font-size: 20px;
            letter-spacing: 1px;
            margin: 5px 0;
        }

        .academic-section {
            margin-top: 10px;
        }

        .grade-scale {
            display: table;
            width: 100%;
            margin: 10px 0;
        }

        .grade-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
        }

        .skills-section {
            margin-top: 10px;
        }

        .skills-table {
            width: 100%;
        }

        .skills-table th {
            background: #333;
            color: white;
            font-size: 8px;
            padding: 3px 2px;
        }

        .rating-section {
            margin: 10px 0;
            font-size: 9px;
        }

        .rating-row {
            margin: 3px 0;
        }

        .comments-section {
            margin-top: 10px;
        }

        .comment-box {
            border: 1px solid #333;
            min-height: 40px;
            padding: 8px;
            margin: 5px 0;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            width: 200px;
            display: inline-block;
            margin-left: 10px;
        }

        .promoted-box {
            border: 2px solid #333;
            padding: 8px;
            margin: 10px 0;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }

        .footer {
            text-align: center;
            font-size: 8px;
            color: #666;
            margin-top: 20px;
        }

        .pass { background-color: #d4edda; }
        .fail { background-color: #f8d7da; }
        .credit { background-color: #fff3cd; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-logo">
            @if(isset($school) && $school->school_logo)
                <img src="{{ Storage::disk('s3')->url($school->school_logo) }}" alt="School Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            @else
                LOGO
            @endif
        </div>
        <div class="term-badge">
            {{ $student->class->name ?? 'JSS 1' }}<br>
            <small>{{ strtoupper($term->name ?? 'THIRD') }}<br>TERM</small>
        </div>
        <div class="school-name">{{ $school->school_name ?? 'ROLEX COMPREHENSIVE COLLEGE' }}</div>
        <div class="school-address">{{ $school->school_address ?? '8 AFEJUKU STREET, BEHIND CIVIC CENTRE, EGBOKODO ITSEKIRI, WARRI DELTA STATE TEL:' }}</div>
        <div class="school-address">{{ $school->phone ?? '08014365530, 08091717018' }} E-MAIL: {{ $school->email ?? 'rolexschoolswarri@gmail.com' }} WEBSITE: {{ $school->website ?? 'www.rolexschools.com' }}</div>
        <div class="report-title">Continuous Assessment Report</div>
        <div class="session">{{ $academy->title ?? '2015/2016' }}</div>
    </div>

    <div class="main-container">
        <div class="left-section">
            <div class="section-title">STUDENT'S PERSONAL DATA</div>
            <table class="student-info">
                <tr><td>NAME:</td><td>{{ $student->name ?? 'Aborido Miracle' }}</td></tr>
                <tr><td>SPIN:</td><td>{{ $student->spin ?? '' }}</td></tr>
                <tr><td>ADMISSION NO.:</td><td>{{ $student->admission_no ?? 'RCC/WP/0111' }}</td></tr>
                <tr><td>SEX:</td><td>{{ $student->gender ?? 'Male' }}</td></tr>
                <tr><td>CLASS:</td><td>{{ $student->class->name ?? 'JSS1' }}</td></tr>
                <tr><td>BARCODE:</td><td class="barcode">{{ $student->admission_no ?? '|||||| ||| |||| ||| ||||' }}</td></tr>
                <tr><td>PHOTO:</td><td>
                    @if(isset($student) && $student->avatar)
                        <img src="{{ Storage::disk('s3')->url($student->avatar) }}" alt="Student Photo" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px; border: 1px solid #ccc;">
                    @else
                        <div style="width: 60px; height: 60px; background: #f0f0f0; border: 1px solid #ccc; border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #666;">No Photo</div>
                    @endif
                </td></tr>
            </table>
        </div>

        <div class="right-section">
            <div class="section-title">ATTENDANCE</div>
            <table class="attendance-info">
                <tr><td>Times Sch. Opened</td><td>{{ $studentAttendance->expected_present ?? '130' }}</td></tr>
                <tr><td>Times Present</td><td>{{ $studentAttendance->total_present ?? '108' }}</td></tr>
                <tr><td>Times Absent</td><td>{{ $studentAttendance->total_absent ?? '22' }}</td></tr>
            </table>

            <div class="section-title" style="margin-top: 15px;">TERMINAL DURATION (....) WEEKS</div>
            <table class="attendance-info">
                <tr><td>Term Begins</td><td>{{ $school->term_begin ? \Carbon\Carbon::parse($school->term_begin)->format('d M Y') : '25 Apr 2016' }}</td></tr>
                <tr><td>Term Ends</td><td>{{ $school->term_ends ? \Carbon\Carbon::parse($school->term_ends)->format('d M Y') : '22 Jul 2016' }}</td></tr>
                <tr><td>Next Term Begins</td><td>{{ $school->next_term_begins ? \Carbon\Carbon::parse($school->next_term_begins)->format('d M Y') : '12 Sep 2016' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="academic-section">
        <div class="section-title">ACADEMIC PERFORMANCE</div>
        <table class="marks-table">
            <thead>
                <tr>
                    <th rowspan="2">MARKS OBTAINED</th>
                    <th colspan="3">ANNUAL SUMMARY</th>
                    <th colspan="5">TERMINAL DURATION</th>
                    <th rowspan="2">Year Avg</th>
                    <th rowspan="2">Grade</th>
                    <th rowspan="2">Position</th>
                    <th rowspan="2">Teacher's Comment</th>
                    <th rowspan="2">Sign.</th>
                </tr>
                <tr>
                    <th>1st Term Avg</th>
                    <th>2nd Term Avg</th>
                    <th>Term Avg</th>
                    <th>Grade</th>
                    <th>Position</th>
                    <th>Class Avg</th>
                    <th>Class Lowest Score</th>
                    <th>Class Highest Score</th>
                    <th>1st Term Avg</th>
                    <th>2nd Term Avg</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Max. Obtainable Mark</td><td>40%</td><td>60%</td><td>100%</td><td>-</td><td>-</td><td>100%</td><td>100%</td><td>100%</td><td>100%</td><td>100%</td><td>100%</td><td>-</td><td>-</td><td>-</td><td>-</td></tr>
                <tr><td>English</td><td>15</td><td>23</td><td>38</td><td>F</td><td>19TH</td><td>63</td><td>36</td><td>85</td><td>-</td><td>46</td><td>42</td><td>E8</td><td>17TH</td><td class="pass">PASS</td><td>I. AYANOKUN</td></tr>
                <tr><td>Mathematics</td><td>16</td><td>16</td><td>31</td><td>F</td><td>17TH</td><td>52</td><td>25</td><td>76</td><td>-</td><td>27</td><td>31</td><td>F9</td><td>16TH</td><td class="fail">FAIL</td><td>P. APAOUKUEZE</td></tr>
                <tr><td>Agricultural Science</td><td>27</td><td>22</td><td>49</td><td>D</td><td>16TH</td><td>60</td><td>26</td><td>92</td><td>-</td><td>63</td><td>56</td><td>C5</td><td>19TH</td><td class="credit">CREDIT</td><td>I. AJUTHI</td></tr>
                <tr><td>Visual Art</td><td>19</td><td>28</td><td>47</td><td>P</td><td>18TH</td><td>69</td><td>41</td><td>87</td><td>-</td><td>41</td><td>44</td><td>E8</td><td>18TH</td><td class="pass">PASS</td><td>M. ELJAGNIU</td></tr>
                <tr><td>Basic Science</td><td>18</td><td>18</td><td>36</td><td>F</td><td>19TH</td><td>70</td><td>32</td><td>87</td><td>-</td><td>56</td><td>46</td><td>D7</td><td>19TH</td><td class="pass">PASS</td><td>S. G. OSAFIJE</td></tr>
                <tr><td>Social Studies</td><td>11</td><td>30</td><td>41</td><td>P</td><td>17TH</td><td>69</td><td>32</td><td>96</td><td>-</td><td>49</td><td>45</td><td>D7</td><td>16TH</td><td class="pass">PASS</td><td>I. AYANOKUN</td></tr>
                <tr><td>Business Studies</td><td>11</td><td>16</td><td>27</td><td>F</td><td>18TH</td><td>60</td><td>35</td><td>85</td><td>-</td><td>45</td><td>36</td><td>F9</td><td>18TH</td><td class="fail">FAIL</td><td>I. BUSARI</td></tr>
                <tr><td>Computer Studies</td><td>12</td><td>22</td><td>34</td><td>F</td><td>19TH</td><td>63</td><td>33</td><td>94</td><td>-</td><td>36</td><td>35</td><td>F9</td><td>18TH</td><td class="fail">FAIL</td><td>P. APAOUKUEZE</td></tr>
                <tr><td>Basic Technology</td><td>15</td><td>30</td><td>45</td><td>P</td><td>18TH</td><td>72</td><td>44</td><td>93</td><td>-</td><td>65</td><td>55</td><td>C5</td><td>17TH</td><td class="credit">CREDIT</td><td>E. EJEGI</td></tr>
                <tr><td>Home Economics</td><td>27</td><td>22</td><td>49</td><td>D</td><td>19TH</td><td>77</td><td>54</td><td>94</td><td>-</td><td>65</td><td>57</td><td>C5</td><td>18TH</td><td class="credit">CREDIT</td><td>I. AJUTHI</td></tr>
                <tr><td>PHE</td><td>18</td><td>-</td><td>18</td><td>F</td><td>19TH</td><td>67</td><td>40</td><td>85</td><td>-</td><td>62</td><td>40</td><td>E8</td><td>19TH</td><td class="pass">PASS</td><td>E. TENIMUAH</td></tr>
                <tr><td>Civic Education</td><td>25</td><td>30</td><td>55</td><td>C</td><td>17TH</td><td>69</td><td>48</td><td>87</td><td>-</td><td>54</td><td>55</td><td>C5</td><td>16TH</td><td class="credit">CREDIT</td><td>M. ELJAGNIU</td></tr>
                <tr><td>CRS</td><td>20</td><td>35</td><td>55</td><td>C</td><td>14TH</td><td>75</td><td>44</td><td>95</td><td>-</td><td>59</td><td>57</td><td>C5</td><td>16TH</td><td class="credit">CREDIT</td><td>E. TENIMUAH</td></tr>
                <tr><td><strong>NO. IN CLASS: 19</strong></td><td colspan="2"><strong>TOTAL TERM SCORE: 528</strong></td><td colspan="9"><strong>POSITION: 18TH - 46% BRONZE</strong></td><td></td><td></td></tr>
            </tbody>
        </table>
    </div>

    <div class="grade-scale">
        <div class="grade-item">A 70-100 (EXCELLENT)</div>
        <div class="grade-item">C 50-69 (CREDIT)</div>
        <div class="grade-item">P 40-49 (PASS)</div>
        <div class="grade-item">F 0-39 (FAIL)</div>
    </div>

    <div class="skills-section">
        <div class="section-title">SKILLS DEVELOPMENT AND BEHAVIOURAL ATTRIBUTES</div>
        <table class="skills-table">
            <tr>
                <th rowspan="2">PERSONAL DEV.</th>
                <th colspan="3">1st 2nd 3rd Term Term Term</th>
                <th rowspan="2">SENSE OF RESP.</th>
                <th colspan="3">1st 2nd 3rd Term Term Term</th>
                <th rowspan="2">SOCIAL DEV.</th>
                <th colspan="3">1st 2nd 3rd Term Term Term</th>
                <th rowspan="2">PSYCHOMOTOR SKILLS DEV.</th>
                <th colspan="3">1st 2nd 3rd Term Term Term</th>
            </tr>
            <tr>
                <th>1st</th><th>2nd</th><th>3rd</th>
                <th>1st</th><th>2nd</th><th>3rd</th>
                <th>1st</th><th>2nd</th><th>3rd</th>
                <th>1st</th><th>2nd</th><th>3rd</th>
            </tr>
            <tr>
                <td>CRITICALITY:</td><td>3</td><td>5</td><td>5</td><td>PUNCTUALITY:</td><td>3</td><td>4</td><td>5</td><td>COOPERATIVENESS:</td><td>3</td><td>4</td><td>4</td><td>READING AND WRITING SKILLS:</td><td>4</td><td>4</td><td>4</td>
            </tr>
            <tr>
                <td>HONESTY:</td><td>3</td><td>5</td><td>5</td><td>NEATNESS:</td><td>3</td><td>4</td><td>4</td><td>CONSIDERATIONS FOR OTHERS:</td><td>3</td><td>3</td><td>3</td><td>VERBAL COMMUNICATION:</td><td>4</td><td>5</td><td>5</td>
            </tr>
            <tr>
                <td>SELF-CONTROL:</td><td>4</td><td>3</td><td>3</td><td>PERSEVERANCE:</td><td>3</td><td>4</td><td>4</td><td>SOCIABILITY/TEAM PLAYER:</td><td>4</td><td>4</td><td>4</td><td>SPORT AND GAME:</td><td>4</td><td>4</td><td>4</td>
            </tr>
            <tr>
                <td>SELF-RELIANCE:</td><td>4</td><td>5</td><td>5</td><td>ATTENTIVENESS:</td><td>3</td><td>4</td><td>4</td><td>PROMPTING/USE OF INITIATIVE:</td><td>5</td><td>5</td><td>5</td><td>NOURISHINESS:</td><td>4</td><td>4</td><td>4</td>
            </tr>
            <tr>
                <td>USE OF INITIATIVE:</td><td>4</td><td>4</td><td>4</td><td>ATTENTIVENESS:</td><td>3</td><td>4</td><td>4</td><td>ACCEPTS RESPONSIBILITIES:</td><td>4</td><td>3</td><td>3</td><td>DEXTERITY/MUSICAL & ART MATERIALS:</td><td>4</td><td>4</td><td>4</td>
            </tr>
        </table>
    </div>

    <div class="rating-section">
        <div class="section-title">KEYS TO RATINGS ON OBSERVABLE BEHAVIOUR</div>
        <div class="rating-row"><strong>5.) Maintains an excellent degree of observable traits</strong> | <strong>4.) Maintains high level of observable traits</strong> | <strong>3.) Acceptable level of observable traits</strong></div>
        <div class="rating-row"><strong>2.) Shows minimal regards for observable traits</strong> | <strong>1.) Has no regard for observable traits</strong></div>
    </div>

    <div class="comments-section">
        <div class="section-title">REMARKS AND CONCLUSION</div>
        <div style="margin: 10px 0;">
            <strong>Class Teacher's Comments:</strong><span class="signature-line"></span><strong>(Signature Class Teacher)</strong>
            <div class="comment-box">Miracle demonstrates responsibility by beginning and completing tasks promptly without needing frequent reminders.</div>
        </div>

        <div style="margin: 10px 0;">
            <strong>Principal's Comments:</strong><span class="signature-line"></span><strong>(Signature / School Stamp and Date)</strong>
            <div class="comment-box">Fair academic performance.</div>
        </div>

        <div style="margin: 10px 0;">
            <strong>Parent's Name:</strong><span class="signature-line"></span>
        </div>
    </div>

    <div class="promoted-box">
        PROMOTED
    </div>

    <div class="footer">
        ● Powered by Edu-Peckle Solutions
    </div>
</body>
</html>
