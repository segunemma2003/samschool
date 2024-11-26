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
                        class="bg-blue-500 text-dark-500 dark:text-white py-2 px-4  border-1 rounded hover:bg-blue-600 dark:bg-blue-700 dark:hover:bg-blue-600">
                        Back to Exam
                    </button>
                    <button
                    wire:click="submitResult"
                     wire:loading.attr="disabled"
                    wire:loading.class="bg-gray-400 cursor-not-allowed"
                    class="bg-green-500 text-white py-2 px-4 border-1 rounded hover:bg-green-600 dark:bg-green-700 dark:hover:bg-green-600 flex items-center justify-center">
                    <span wire:loading.remove>
                        Final Submission
                    </span>
                    <span class="flex items-center" wire:loading>
                        <svg class="animate-spin h-5 w-5 text-white mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        Loading...
                    </span>
                    </button>

                </div>
            </div>

            @elseif($showSuccessMessage)
            <!-- Success Message -->
            <div class="max-w-lg p-6 text-center bg-white rounded-lg shadow-md dark:bg-gray-800">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Exam Submitted Successfully!</h1>
                <p class="mt-4 text-gray-600 dark:text-gray-400">
                    Thank you for completing the exam. Your submission has been recorded.
                </p>
                <a href="/student/exams" class="mt-6 inline-block px-4 py-2 font-semibold text-white bg-blue-500 rounded-lg shadow hover:bg-blue-600 dark:bg-blue-700">
                    Go to Exams Page
                </a>
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
                    @elseif($questions[$currentQuestion]['question_type'] === "multiple_choice")
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
                    <button wire:click="nextQuestion" class="px-4 py-2 font-semibold text-gray-600  transition bg-blue-500 rounded-lg shadow-xl dark:text-white dark:border-1 dark:bg-blue-700 hover:bg-blue-600">
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

<script>
    document.addEventListener('livewire:init', () => {
        let timeRemaining = @json($timeRemaining);
        let timerInterval;

        function startTimer() {
            timerInterval = setInterval(() => {
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    Livewire.emit('submit');
                } else {
                    timeRemaining--;
                    document.getElementById('timer').textContent = new Date(timeRemaining * 1000).toISOString().substr(11, 8);
                }
            }, 1000);
        }

        Livewire.on('quiz-submitted', () => {
            clearInterval(timerInterval);
        });

        startTimer();
    });
</script>
