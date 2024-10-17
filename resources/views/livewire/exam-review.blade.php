<div class="p-6 space-y-6 bg-gray-100">
    <h2 class="text-lg font-bold">Review Your Answers</h2>

    @foreach ($questions as $index => $question)
        <div class="p-4 bg-white rounded-lg shadow-md">
            <h3 class="text-xl font-bold">Question {{ $index + 1 }} of {{ $totalQuestions }}:</h3>
            <p>{{ $question['text'] }}</p>

            @if ($question['type'] === 'multiple_choice' || $question['type'] === 'true_false')
                <p>Your Answer: <strong>{{ $answers[$index] ?? 'No answer' }}</strong></p>
            @elseif ($question['type'] === 'open_ended')
                <p>Your Answer: <strong>{{ $answers[$index] ?? 'No answer' }}</strong></p>
            @endif
        </div>
    @endforeach

    <!-- Final Submission Button -->
    <div class="flex justify-end p-4">
        <button wire:click="finalSubmission" class="px-4 py-2 text-white bg-green-500 rounded-lg">
            Submit Final Answers
        </button>
    </div>
</div>

