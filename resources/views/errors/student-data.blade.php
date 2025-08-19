<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Data Issue</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
        }
        .error-icon {
            text-align: center;
            font-size: 48px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        .error-title {
            color: #e74c3c;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }
        .error-message {
            color: #333;
            font-size: 16px;
            margin-bottom: 20px;
            text-align: center;
        }
        .solution {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
        }
        .solution h3 {
            color: #007bff;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .student-info {
            background-color: #e8f5e8;
            border: 1px solid #28a745;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .student-info h4 {
            color: #28a745;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .back-button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
        .steps {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .steps h4 {
            color: #856404;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .steps ol {
            margin: 0;
            padding-left: 20px;
        }
        .steps li {
            margin-bottom: 5px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">⚠️</div>
        <h1 class="error-title">Student Data Configuration Issue</h1>
        <p class="error-message">{{ $error }}</p>

        <div class="student-info">
            <h4>Student Information</h4>
            <p><strong>Student ID:</strong> {{ $student_id }}</p>
            <p><strong>Student Name:</strong> {{ $student_name }}</p>
            @if(isset($class_name))
                <p><strong>Class:</strong> {{ $class_name }}</p>
            @endif
        </div>

        <div class="solution">
            <h3>Solution</h3>
            <p>{{ $solution }}</p>
        </div>

        <div class="steps">
            <h4>How to Fix This Issue:</h4>
            <ol>
                @if(!isset($class_name))
                    <li>Go to the Admin Panel</li>
                    <li>Navigate to <strong>Students</strong> section</li>
                    <li>Find and edit student: <strong>{{ $student_name }}</strong></li>
                    <li>Assign the student to a class</li>
                    <li>Save the changes</li>
                @else
                    <li>Go to the Admin Panel</li>
                    <li>Navigate to <strong>Classes</strong> section</li>
                    <li>Find and edit class: <strong>{{ $class_name }}</strong></li>
                    <li>Assign the class to a group</li>
                    <li>Save the changes</li>
                @endif
            </ol>
        </div>

        <div style="text-align: center;">
            <a href="javascript:history.back()" class="back-button">Go Back</a>
        </div>
    </div>
</body>
</html>
