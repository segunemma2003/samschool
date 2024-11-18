<div class="p-6 text-black bg-white rounded-lg shadow-lg dark:bg-gray-800 dark:text-white">
    <h1 class="mb-6 text-2xl font-bold">Exam Details</h1>

    <div class="space-y-4">
        <p><strong>Exam Code:</strong> {{ $record->subject->code ?? 'N/A' }}</p>
        <p><strong>Teacher:</strong> {{ $record->subject->teacher->name ?? 'N/A' }}</p>
        <p><strong>Date:</strong> {{ $record->exam_date }}</p>
        <p><strong>Assessment Type:</strong> {{ $record->assessment_type }}</p>
        <p><strong>Duration:</strong> {{ $record->duration }} minutes</p>
        <p><strong>Total Score:</strong> {{ $record->total_score }}</p>
        <p><strong>Instructions:</strong> {{ strip_tags($record->instructions) }}</p>
    </div>

    <h2 class="mt-8 text-xl font-semibold">Questions</h2>

    @if($record->questions->isNotEmpty())
        <div class="mt-4 space-y-6">
            @foreach($record->questions as $question)
                <div class="p-6 mb-4 border-2 border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                    <div class="space-y-2">
                        <div>
                            <strong class="text-lg">Question {{ $loop->index + 1 }}:</strong>
                            <p class="mt-2 text-gray-700 dark:text-gray-200">{{ $question->question }}</p>
                        </div>

                        <div>
                            <strong class="text-sm">Type:</strong>
                            <p class="text-gray-500 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $question->question_type)) }}</p>
                        </div>

                        <div>
                            <strong class="text-sm">Options:</strong>
                            @if(!is_null($question->options))
                                <ul class="mt-2 text-gray-100 list-disc list-inside dark:text-gray-300">
                                    @foreach(json_decode($question->options, true) as $key => $option)
                                        <li>{{ $key }}: {{ $option }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-gray-500 dark:text-gray-400">N/A</p>
                            @endif
                        </div>

                        <div>
                            <strong class="text-sm">Answer:</strong>
                            <p class="text-gray-700 dark:text-gray-200">{{ $question->answer }}</p>
                        </div>

                        <div>
                            <strong class="text-sm">Hint:</strong>
                            <p class="text-gray-500 dark:text-gray-400">{{ $question->hint ?? 'No hint available' }}</p>
                        </div>

                        @if($question->image_url)
                            <div class="mt-4">
                                <strong class="text-sm">Image:</strong><br>
                                <img src="{{ $question->image_url }}" alt="Question Image" class="h-auto max-w-full rounded-lg shadow-lg">
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
