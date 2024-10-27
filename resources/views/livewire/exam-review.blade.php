<div class="flex flex-col items-center justify-center h-screen p-4 bg-gray-100 dark:bg-gray-900">
    <div class="relative w-full max-w-4xl p-4 mx-auto bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h2 class="text-2xl font-bold text-center">Quiz Review</h2>
        <div class="mt-4">
            @foreach($questions as $index => $question)
                <div class="mb-4">
                    <h3 class="font-semibold">{{ $index + 1 }}. {{ $question['question'] }}</h3>
                    <p>Your answer: {{ $userAnswers[$index] ?? 'Not answered' }}</p>
                    {{-- @if(isset($question['correct']))
                        <p>Correct answer: {{ $question['correct'] }}</p>
                    @endif --}}
                </div>
            @endforeach
        </div>
        <a href="{{ route('exam.page') }}" class="px-4 py-2 mt-4 font-semibold text-white bg-blue-500 rounded-lg">Start New Quiz</a>
    </div>
</div>
