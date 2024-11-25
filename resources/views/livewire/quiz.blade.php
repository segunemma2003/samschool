<div class="flex flex-col items-center justify-center h-screen p-4 bg-gray-100 dark:bg-gray-900">
    <div class="relative w-full max-w-4xl p-4 mx-auto bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <!-- Header Section -->
        <div class="flex items-center justify-between p-4 border-b-2 border-gray-200 dark:border-gray-700">
            <div class="space-y-2">
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">User: {{ $userName }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Subject: {{ $subject }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Details: {{ $quizTitle }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Duration: {{ $duration }} mins</p>
                <div class="text-sm font-bold text-gray-800 dark:text-gray-200">
                    Time Remaining: <span id="timer">{{ gmdate('H:i:s', $timeRemaining) }}</span>
                </div>
            </div>
            {{-- <div class="relative w-24 h-24 overflow-hidden bg-gray-300 rounded-lg shadow-inner dark:bg-gray-700">
                <video id="videoFeed" class="object-cover w-full h-full" autoplay muted></video>
                <div class="absolute inset-0 bg-black rounded-lg opacity-25"></div>
            </div> --}}
        </div>

        <!-- Conditional Rendering Based on State -->
        @if($isReviewing)
            <!-- Review Section -->
            <div class="p-4">
                <h1 class="text-xl font-bold mb-4">Quiz Review</h1>
                <div class="h-96 overflow-y-auto border border-gray-300 dark:border-gray-700 p-4 mb-4 bg-white dark:bg-gray-800">
                    @foreach ($questions as $index => $question)
                        <div class="border-b border-gray-300 dark:border-gray-700 mb-2 py-2">
                            <p class="font-semibold text-gray-800 dark:text-gray-200">
                                Question {{ $index + 1 }}: {{ isset($userAnswers[$index]) && $userAnswers[$index] !== null ? "answered" : "unanswered" }}
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between mt-4">
                    <button
                        wire:click="goBackToQuiz"
                        class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 dark:bg-blue-700 dark:hover:bg-blue-600">
                        Back to Exam
                    </button>
                    <button
                        wire:click="submit"
                        class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600 dark:bg-green-700 dark:hover:bg-green-600">
                        Final Submission
                    </button>
                </div>
            </div>

        @else

            <!-- Question Section -->
            <div class="p-6 space-y-6 rounded-lg shadow-lg bg-blue-50 dark:bg-blue-900">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                    Question {{ $currentQuestion + 1 }} of {{ count($questions) }}: {{ $questions[$currentQuestion]['question'] }}
                </h3>

                <div class="space-y-2">
                    @if($questions[$currentQuestion]['question_type'] === 'true_false')
                        <label class="block p-3 bg-white rounded-lg shadow-md dark:bg-gray-700">
                            <input type="radio" name="question{{ $currentQuestion }}" value="true" wire:model="selectedAnswer" class="mr-2"> True
                        </label>
                        <label class="block p-3 bg-white rounded-lg shadow-md dark:bg-gray-700">
                            <input type="radio" name="question{{ $currentQuestion }}" value="false" wire:model="selectedAnswer" class="mr-2"> False
                        </label>
                    @elseif($questions[$currentQuestion]['question_type'] === 'open_ended')
                        <textarea wire:model="selectedAnswer" rows="4" class="w-full p-3 border border-gray-300 rounded-lg dark:bg-gray-700 dark:text-gray-200" placeholder="Type your answer here..."></textarea>
                    @elseif($questions[$currentQuestion]['question_type']==="multiple_choice")
                        @foreach(json_decode($questions[$currentQuestion]['options'], true) as $key => $option)
                            <label class="block p-3 bg-white rounded-lg shadow-md dark:bg-gray-700">
                                <input type="radio" name="question{{ $currentQuestion }}" value="{{ $key }}" wire:model="selectedAnswer" class="mr-2"> {{ $key }}. {{ $option }}
                            </label>
                        @endforeach
                    @endif
                </div>

                <!-- Navigation Buttons -->
                <div class="flex items-center justify-between mt-6">
                    <button wire:click="previousQuestion" class="px-4 py-2 font-semibold text-gray-600 transition bg-gray-200 rounded-lg shadow dark:bg-gray-600 dark:text-gray-200 hover:bg-gray-300">
                        Previous
                    </button>
                    <button wire:click="nextQuestion" class="px-4 py-2 font-semibold text-white transition bg-blue-500 rounded-lg shadow-xl dark:bg-blue-700 hover:bg-blue-600">
                        @if($currentQuestion === count($questions) - 1)
                            Submit
                        @else
                            Next
                        @endif
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>



<!-- JavaScript for Timer and Camera Feed -->
<script>
    const examInstructionsRoute = "{{ route('exam.instructions') }}";
    document.addEventListener('livewire:init', () => {
        let timeRemaining = @json($timeRemaining);
        let timerInterval;

        // Timer Countdown
        function startTimer() {
            timerInterval = setInterval(() => {
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    Livewire.dispatch('submit');
                } else {
                    timeRemaining--;
                    updateTimerDisplay(timeRemaining);
                }
            }, 1000);
        }

        function updateTimerDisplay(seconds) {
            document.getElementById('timer').textContent = new Date(seconds * 1000).toISOString().substr(11, 8);
            Livewire.dispatch('updateTimer', [seconds]); // Dispatch to Livewire with updated time
        }


        // Start the timer when the page is loaded
        startTimer();

        // Start video feed
//         async function startCamera() {
//     const video = document.getElementById('videoFeed');
//     try {

//         // Request access to the camera
//         const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
//         video.srcObject = stream;
//     } catch (error) {
//         console.error("Error accessing camera: ", error.name, error.message);

//         // alert("Unable to access the camera. You will be redirected to the exam instruction page.");
//         // Redirect to the exam instruction page using the Laravel route
//         // window.location.href = examInstructionsRoute;
//     }
// }

        // startCamera();

        // Stop video feed and timer on quiz submission
        Livewire.on('quiz-submitted', () => {
            clearInterval(timerInterval);
            // let video = document.getElementById('videoFeed');
            // let stream = video.srcObject;
            // let tracks = stream.getTracks();
            // tracks.forEach(track => track.stop());
        });

        // Update the timer display when navigating to the next question
        Livewire.on('question-changed', (newTimeRemaining) => {
            console.log(newTimeRemaining);
            timeRemaining = newTimeRemaining[0];
            updateTimerDisplay(timeRemaining); // Update timer display with new time
        });
    });
</script>
