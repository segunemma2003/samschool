<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Exam</title>
    @vite(['resources/js/react/app.jsx'])
</head>
<body>

    <div id="quiz-root" data-exam="{{ json_encode($examData) }}"></div>

</body>
</html>
