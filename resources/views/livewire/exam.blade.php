<div class="p-6 space-y-6 bg-gray-100">
    <!-- Top Panel: Exam Info and Camera -->
    <div class="grid grid-cols-3 gap-4">
        <!-- Exam Info -->
        <div class="col-span-2 p-4 bg-white rounded-lg shadow-md">
            <h2 class="text-lg font-bold">Subject: Mathematics</h2>
            <p class="text-black">Class: 10</p>
            <p class="text-black">Exam Code: EXAM123</p>
            <p class="text-black">Date: {{ now()->format('Y-m-d') }}</p>
            <p class="text-black">Duration: 1 Hour</p>
            <p class="font-semibold text-black">
                Time Left: <span id="timer" class="font-bold">Loading...</span>
            </p>
            <hr />
            <br />
            <p class="text-black">Student Name: <b>Taiwo Aina</b></p>
            <p class="text-black">Student No: <b>23734843AD</b></p>
            <br />
            <hr />
            <br />
            <p class="text-black">Teacher Name: <b>Kola Aina</b></p>
        </div>


        <!-- Camera Stream -->
        <div class="p-4 bg-white rounded-lg shadow-md">
            <h2 class="font-semibold">Live Camera</h2>
            <video id="camera-stream" autoplay muted class="w-full h-auto rounded-lg"></video>
        </div>
    </div>

    <!-- Second Panel: Question and Options -->
    <div class="p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-xl font-bold text-black">Question {{ $currentQuestionIndex + 1 }} of {{ $totalQuestions }}:</h2>
        <br />
        <h3 class="text-xl font-semibold text-black">{{ $currentQuestion['text'] }}</h3>

        <!-- Show image if the question has one -->
        @if ($currentQuestion['image'])
            <div class="mt-4">
                <img src="{{ $currentQuestion['image'] }}" alt="Question Image" class="w-full h-auto rounded-lg">
            </div>
        @endif

        <!-- Dynamic rendering of questions based on type -->
        <div class="mt-4 text-black">
            @if ($currentQuestion['type'] === 'multiple_choice')
                @foreach ($currentQuestion['options'] as $option)
                    <div class="mb-2">
                        <label class="flex items-center">
                            <input type="radio" name="answer" wire:click="recordAnswer('{{ $option }}')"
                                   value="{{ $option }}" class="mr-2"
                                   @if ($answers[$currentQuestionIndex] === $option) checked @endif>
                                   {{ $option }}
                        </label>
                    </div>
                @endforeach
            @elseif ($currentQuestion['type'] === 'open_ended')
                <textarea class="w-full p-2 border border-gray-300 rounded-lg" name="answer"
                          wire:model.debounce.500ms="answers.{{ $currentQuestionIndex }}"></textarea>
            @elseif ($currentQuestion['type'] === 'true_false')
                <div class="flex space-x-4">
                    <label class="flex items-center">
                        <input type="radio" name="answer" wire:click="recordAnswer('True')" value="True" class="mr-2"
                               @if ($answers[$currentQuestionIndex] === 'True') checked @endif>
                               True
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="answer" wire:click="recordAnswer('False')" value="False" class="mr-2"
                               @if ($answers[$currentQuestionIndex] === 'False') checked @endif>
                               False
                    </label>
                </div>
            @endif
        </div>
    </div>

    <!-- Lower Panel: Navigation Buttons -->
    <div class="flex justify-between p-6 bg-white rounded-lg shadow-md">
        <button wire:click="previousQuestion" class="px-4 py-2 bg-gray-200 rounded-lg"
                @if ($currentQuestionIndex === 0) disabled @endif>
            Previous
        </button>

        @if ($currentQuestionIndex === count($questions) - 1)
            <button wire:click="submitExam" type="button" class="px-4 py-2 text-white bg-green-500 rounded-lg">
                Submit Final Answers
            </button>
        @else
            <button wire:click="nextQuestion" type="button" class="px-4 py-2 text-white bg-blue-500 rounded-lg">
                Next
            </button>
        @endif
    </div>
</div>

@livewireScripts

<!-- Add JavaScript for Camera and Timer -->
<script>
    // Camera Stream
    const video = document.getElementById('camera-stream');
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            video.srcObject = stream;
        })
        .catch(err => {
            console.error('Camera access denied:', err);
        });

    // Timer Countdown
    let examDuration = {{ $examDuration }}; // Exam duration in seconds
    const timerElement = document.getElementById('timer');

    const formatTime = (time) => {
        const hours = Math.floor(time / 3600);
        const minutes = Math.floor((time % 3600) / 60);
        const seconds = time % 60;
        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    };

    const countdown = () => {
        if (examDuration > 0) {
            examDuration--;
            timerElement.textContent = formatTime(examDuration);
        } else {
            // Handle when time runs out
            Livewire.emit('submitExam');
        }
    };

    setInterval(countdown, 1000); // Update timer every second
</script>
