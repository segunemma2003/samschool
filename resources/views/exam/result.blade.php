<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card</title>
    @vite(['resources/js/results/app.jsx'])
</head>
<body>

    <div id="result-root"></div>
<script>
     window.student = @json($student);
     window.scoreData = @json($scoreData);
     window.totalHeadings = @json($totalHeadings);
     window.percent = @json($percent);
     window.groupedHeadings = @json($groupedHeadings);
     window.headings = @json($headings);
     window.psychomotorAffective = @json($psychomotorAffective);
     window.psychomotorNormal = @json($psychomotorNormal);
     window.termAndAcademy = @json($termAndAcademy);
     window.relatedData = @json($relatedData);
     window.resultData = @json($resultData);
</script>
</body>
</html>
