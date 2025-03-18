<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Exam</title>
    @vite(['resources/js/react/app.jsx'])
</head>
<body>

    <div id="quiz-root"></div>
<script>
     window.student = @json($student);
     window.exam = @json($exam);
     window.course = @json($course);
     window.questions = @json($questions);
     window.answers = @json($answers);
     window.quizScore = @json($quizScore);
     window.term = @json($term);
     window.academy = @json($academy)
</script>
</body>
</html>
