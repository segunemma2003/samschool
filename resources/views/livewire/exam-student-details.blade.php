<div class="p-6 text-black bg-white rounded-lg shadow-lg dark:bg-gray-800 dark:text-white">
    <h1 class="mb-6 text-2xl font-bold">Exam Details</h1>

    <div class="space-y-4">
        <p><strong>Exam Code:</strong> {{ $quizScore->exam->subject->code ?? 'N/A' }}</p>
        <p><strong>Teacher:</strong> {{ $quizScore->exam->subject->teacher->name ?? 'N/A' }}</p>
        <p><strong>Date:</strong> {{ $quizScore->exam->exam_date ?? 'N/A' }}</p>
        <p><strong>Assessment Type:</strong> {{ $quizScore->exam->assessment_type ?? 'N/A' }}</p>
        <p><strong>Total Score:</strong> {{ $quizScore->total_score ?? 'N/A' }}</p>
        <p><strong>Instructions:</strong> {{ strip_tags($quizScore->instructions) ?? 'No instructions provided' }}</p>
    </div>

    <h2 class="mt-8 text-xl font-semibold">Student Score</h2>
    <div class="space-y-4">
        <p><strong>Student Name:</strong> {{ $studentDetails->name ?? 'N/A' }}</p>
        <p><strong>Score:</strong> {{ $quizScore->total_score ?? 'N/A' }}</p>
        <p><strong>Approved:</strong> {{ $quizScore->approved ? 'Yes' : 'No' }}</p>
    </div>

    <h2 class="mt-8 text-xl font-semibold">Questions</h2>

    @if($questions->isNotEmpty())
        <div class="mt-4 space-y-6">
            @foreach($questions as $question)
                <div class="p-6 mb-4 border-2 border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                    <div class="space-y-2">
                        <div>
                            <strong class="text-lg">Question {{ $loop->index + 1 }}:</strong>
                            <p class="mt-2 text-gray-700 dark:text-gray-200">{{ $question->question->question ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <strong class="text-sm">Type:</strong>
                            <p class="text-gray-500 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $question->question->question_type ?? 'N/A')) }}</p>
                        </div>

                        <div>
                            <strong class="text-sm">Options:</strong>
                            @if(!is_null($question->question->options))
                                <ul class="mt-2 text-gray-500 list-disc list-inside dark:text-gray-300">
                                    @foreach(json_decode($question->question->options, true) as $key => $option)
                                        <li>{{ $key }}: {{ $option }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-gray-500 dark:text-gray-400">N/A</p>
                            @endif
                        </div>
                        <div>
                            <strong class="text-sm">Student Answer:</strong>
                            <p class="text-gray-700 dark:text-gray-200">{{ $question->answer ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <strong class="text-sm">Answer:</strong>
                            <p class="text-gray-700 dark:text-gray-200">{{ $question->question->answer ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <strong class="text-sm">Answer:</strong>
                            <p class="text-gray-700 dark:text-gray-200">{{ $question->question->answer== $question->answer ? 'correct':'incorrect' }}</p>
                        </div>
                        <div>
                            <strong class="text-sm">Score Weight:</strong>
                            <p class="text-gray-700 dark:text-gray-200">{{ $question->question->marks ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <strong class="text-sm">Hint:</strong>
                            <p class="text-gray-500 dark:text-gray-400">{{ $question->question->hint ?? 'No hint available' }}</p>
                        </div>

                        @if($question->question->image)
                            <div class="mt-4">
                                <strong class="text-sm">Image:</strong><br>
                                <img src="{{ $question->question->image }}" alt="Question Image" class="h-auto max-w-full rounded-lg shadow-lg">
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p>No questions available for this exam.</p>
    @endif
</div>
