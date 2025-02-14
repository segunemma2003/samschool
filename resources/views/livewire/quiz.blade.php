<div class="flex flex-col items-center justify-center h-screen p-4 bg-gray-100 dark:bg-gray-900">
    <div class="relative w-full max-w-4xl p-4 mx-auto bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <div class="flex items-center justify-between p-4 border-b-2 border-gray-200 dark:border-gray-700">
            <div class="space-y-2">
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">User: {{ $userName }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Subject: {{ $subject }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Details: {{ $quizTitle }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Duration: {{ $duration }} mins</p>
                <div class="text-sm font-bold text-gray-800 dark:text-gray-200">
                    {{-- Time Remaining: <span id="timer" wire:ignore>{{ gmdate('H:i:s', $timeRemaining) }}</span> --}}
                    Time Remaining: <span id="timer">{{ gmdate('H:i:s', max(0, $timeRemaining)) }}</span>
                </div>
            </div>
            <div id="videoContainer" class="bg-black rounded-lg overflow-hidden">
                <video id="cameraPreview" class="w-full h-full object-cover" autoplay muted style="max-height: 240px; max-width: 320px;"></video>
            </div>
        </div>

        @if($isReviewing)
            <div class="p-4">
                <h1 class="text-xl font-bold mb-4">Quiz Review</h1>
                <div class="h-96 overflow-y-auto border border-gray-300 dark:border-gray-700 p-4 mb-4 bg-white dark:bg-gray-800">
                    @foreach ($questions as $index => $question)
                        <div class="border-b border-gray-300 dark:border-gray-700 mb-2 py-2">
                            <p class="font-semibold text-gray-800 dark:text-gray-200">
                                Question {{ $index + 1 }}:
                                @if(isset($userAnswers[$index]))
                                    {{ $userAnswers[$index] !== null ? "Answered" : "Unanswered" }}
                                @else
                                    Unanswered
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between mt-4">
                    <button wire:click="goBackToQuiz" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 dark:bg-blue-700 dark:hover:bg-blue-600">
                        Back to Exam
                    </button>
                    <button wire:click="submitResult"
                        wire:loading.attr="disabled"
                        wire:loading.class="bg-gray-400 cursor-not-allowed"
                        class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600 dark:bg-green-700 dark:hover:bg-green-600 flex items-center justify-center">
                    <span wire:loading.remove>Final Submission</span>
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
            <div class="p-6 space-y-6 rounded-lg shadow-lg bg-blue-50 dark:bg-blue-900">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                    Question {{ $currentQuestion + 1 }} of {{ count($questions) }}: {{ $questions[$currentQuestion]['question'] }}
                </h3>
                @if(!is_null($questions[$currentQuestion]['image']))

                    <div class="w-12 h-12">
                        <img src="{{Storage::disk('cloudinary')->url($questions[$currentQuestion]['image'])}}"
                        class="object-cover rounded-md"
                        style=" max-width: 400px; max-height: 300px;" />
                        />
                    </div>
                @endif

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
                        @foreach($questions[$currentQuestion]['options'] as $key => $option)

                            <label class="block p-3 bg-white rounded-lg shadow-md dark:bg-gray-700">
                                @if(is_numeric($key))
                                    <div class="flex space-x-3">
                                        <input type="radio" name="question{{ $currentQuestion }}" value="{{ $option['option'] }}" wire:model="selectedAnswer" class="mr-2">
                                        <div class=" flex flex-col space-y-4">
                                            <div>{{ chr(65 + $key) }}: {{ $option['option'] }}</div>
                                            {{-- {{dd($option)}} --}}
                                            @if(isset($option['image']) && !is_null($option['image']))
                                            <div class="w-12 h-12">
                                                <img src="{{ Storage::disk('cloudinary')->url($option['image']) }}"
                                                     class="object-cover rounded-md"
                                                     style=" max-width: 200px; max-height: 200px;" />
                                            </div>
                                            @endif
                                        </div>

                                    </div>
                                @else
                                    <input type="radio" name="question{{ $currentQuestion }}" value="{{ $key }}" wire:model="selectedAnswer" class="mr-2"> {{ $key }}. {{ $option }}
                                @endif
                            </label>
                        @endforeach
                    @endif
                </div>

                <div class="flex items-center justify-between mt-6">
                    <button wire:click="previousQuestion"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 font-semibold text-gray-600 transition bg-gray-200 rounded-lg shadow dark:bg-gray-600 dark:text-gray-200 hover:bg-gray-300 @if($loadingPrevious) opacity-50 cursor-not-allowed @endif"
                            @if($currentQuestion === 0) disabled @endif>
                        <span wire:loading.remove wire:target="previousQuestion">Previous</span>
                        <span wire:loading wire:target="previousQuestion">Loading...</span>
                    </button>
                    <button wire:click="nextQuestion"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 font-semibold text-gray-600 transition bg-blue-500 rounded-lg shadow-xl dark:text-white dark:border-1 dark:bg-blue-700 hover:bg-blue-600 @if($loadingNext) opacity-50 cursor-not-allowed @endif"
                            @if($currentQuestion === count($questions) - 1) wire:click="submitQuiz" @endif>
                        <span wire:loading.remove wire:target="nextQuestion">
                            @if($currentQuestion === count($questions) - 1) Submit @else Next @endif
                        </span>
                        <span wire:loading wire:target="nextQuestion">Loading...</span>
                    </button>
                </div>
            </div>
        @endif

    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        // let timeRemaining = @json($timeRemaining);
        let timerInterval;
        let mediaRecorder;
        let recordedChunks = [];
        let videoPreview = document.getElementById('cameraPreview');


        const videoContainer = document.getElementById('videoContainer');
    // const videoPreview = document.getElementById('cameraPreview');

    function resizeVideo() {
        const desiredWidth = 320; // Or calculate dynamically
        const desiredHeight = 240; // Or calculate dynamically based on aspect ratio

        videoContainer.style.width = desiredWidth + 'px';
        videoContainer.style.height = desiredHeight + 'px';
        videoContainer.style.maxWidth = desiredWidth + 'px';
        videoContainer.style.maxHeight = desiredHeight + 'px';
    }

    // Set initial size (important!)
    resizeVideo();

    window.addEventListener('resize', resizeVideo);

        async function initializeCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                videoPreview.srcObject = stream;
                mediaRecorder = new MediaRecorder(stream);

                mediaRecorder.ondataavailable = event => {
                    if (event.data.size > 0) {
                        recordedChunks.push(event.data);
                    }
                };

                mediaRecorder.onstop = () => {
                    const blob = new Blob(recordedChunks, { type: 'video/webm' });
                    Livewire.dispatch('recordingStopped', blob);
                    recordedChunks = [];
                };

                mediaRecorder.start(1000);
                Livewire.set('isRecording', true); // Set isRecording to true
            } catch (error) {
                console.error('Camera access error:', error);
                Livewire.set('isRecording', false); // Set isRecording to false on error
                // Handle error, maybe show a message to the user
            }
        }


        function startTimer() {
        // Get the current timeRemaining from the element

        let timeRemaining = localStorage.getItem('timer') ?? parseInt(document.getElementById('timer').textContent.split(':').reduce((acc, time) => (60 * acc) + parseInt(time), 0));
        console.log(timeRemaining);
        timerInterval = setInterval(() => {
            if (timeRemaining <= 0) {
                clearInterval(timerInterval);
                stopRecording();
                Livewire.emit('submit');
            } else {
                timeRemaining--;
                document.getElementById('timer').textContent = new Date(timeRemaining * 1000).toISOString().substr(11, 8);
                localStorage.setItem('timer', timeRemaining)
                Livewire.emit('updateTimer', timeRemaining);
            }
        }, 1000);
    }

        // function startTimer() {
        //     timerInterval = setInterval(() => {
        //         if (timeRemaining <= 0) {
        //             clearInterval(timerInterval);
        //             stopRecording();
        //             Livewire.emit('submit');
        //         } else {
        //             timeRemaining--;
        //             document.getElementById('timer').textContent = new Date(timeRemaining * 1000).toISOString().substr(11, 8);
        //             Livewire.emit('updateTimer', timeRemaining);
        //         }
        //     }, 1000);
        // }

        function stopRecording() {
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
                Livewire.set('isRecording', false); // Set isRecording to false when stopped
            }
        }

        initializeCamera(); // Call initializeCamera
        startTimer(); // Call startTimer

        Livewire.on('quiz-submitted', () => {
            clearInterval(timerInterval);
            stopRecording();
            localStorage.removeItem('timer')
        });

        window.addEventListener('beforeunload', () => {
            Livewire.dispatch('saveState', timeRemaining, @this.currentQuestion, @this.userAnswers);
        });

        Livewire.on('restoreState', (timeRemaining, currentQuestion, userAnswers) => {
            timeRemaining = timeRemaining;
            @this.currentQuestion = currentQuestion;
            @this.userAnswers = userAnswers;
            startTimer();
        });

        Livewire.on('startTimer', (initialTime) => {
            timeRemaining = initialTime;
            startTimer();
        });

        Livewire.on('question-changed', (timeRemaining) => {
            timeRemaining = timeRemaining;
        });

        Livewire.on('done', () => {
            localStorage.removeItem('examState');
        });

    });
</script>
