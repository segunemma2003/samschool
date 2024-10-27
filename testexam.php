<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi-Choice Question Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="w-full max-w-4xl mx-auto p-4 bg-white shadow-lg rounded-lg relative">
        <!-- Header Section -->
        <div class="flex justify-between items-center p-4 border-b-2 border-gray-200">
            <!-- User Info on Left -->
            <div class="flex items-center space-x-6">
                <div class="space-y-2">
                    <h2 class="text-xl font-bold text-gray-800">User: John Doe</h2>
                    <p class="text-sm text-gray-600">Subject: Mathematics</p>
                    <p class="text-sm text-gray-600">Details: Quiz 1</p>
                    <p class="text-sm text-gray-600">Duration: 30 mins</p>
                </div>
            </div>
            <!-- Camera Feed on Right -->
            <div class="w-24 h-24 bg-gray-300 rounded-lg overflow-hidden shadow-inner relative">
                <video class="w-full h-full object-cover" autoplay muted></video>
                <div class="absolute inset-0 bg-black opacity-25 rounded-lg"></div> <!-- Placeholder effect -->
            </div>
        </div>

        <!-- Question Section -->
        <div class="p-6">
            <div class="bg-blue-50 p-6 rounded-lg shadow-lg space-y-6">
                <h3 class="text-lg font-semibold text-gray-800">Question 1: What is the capital of France?</h3>

                <!-- Question Type Options -->
                <!-- Multi-Choice Options -->
                <div class="space-y-2">
                    <label class="block bg-white p-3 rounded-lg shadow-md">
                        <input type="radio" name="question1" value="A" class="mr-2"> A. Berlin
                    </label>
                    <label class="block bg-white p-3 rounded-lg shadow-md">
                        <input type="radio" name="question1" value="B" class="mr-2"> B. Paris
                    </label>
                    <label class="block bg-white p-3 rounded-lg shadow-md">
                        <input type="radio" name="question1" value="C" class="mr-2"> C. Madrid
                    </label>
                    <label class="block bg-white p-3 rounded-lg shadow-md">
                        <input type="radio" name="question1" value="D" class="mr-2"> D. Rome
                    </label>
                </div>

                <!-- Navigation Buttons and Timer -->
                <div class="flex justify-between items-center mt-6">
                    <button class="px-4 py-2 bg-gray-200 text-gray-600 font-semibold rounded-lg shadow hover:bg-gray-300 transition">
                        Previous
                    </button>
                    <div class="text-gray-800 text-sm font-bold">
                        Time Remaining: <span id="timer">29:59</span>
                    </div>
                    <button class="px-4 py-2 bg-blue-500 text-white font-semibold rounded-lg shadow hover:bg-blue-600 transition">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Timer -->
    <script>
        // Timer Countdown
        let timerMinutes = 29;
        let timerSeconds = 59;
        const timerDisplay = document.getElementById('timer');
        setInterval(() => {
            if (timerSeconds === 0) {
                if (timerMinutes === 0) {
                    clearInterval();
                    alert('Time is up!');
                } else {
                    timerMinutes--;
                    timerSeconds = 59;
                }
            } else {
                timerSeconds--;
            }
            timerDisplay.textContent = `${timerMinutes.toString().padStart(2, '0')}:${timerSeconds.toString().padStart(2, '0')}`;
        }, 1000);
    </script>
</body>

</html>