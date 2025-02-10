<div class="p-6 text-gray-900 bg-white rounded-lg shadow-lg dark:bg-gray-800 dark:text-gray-100">
    <h1 class="mb-6 text-2xl font-bold">Exam Details</h1>

    <div class="space-y-4">
        <p><strong class="text-gray-900 dark:text-gray-100">Exam Code:</strong> <span class="text-gray-700 dark:text-gray-200">{{ $record->subject->code ?? 'N/A' }}</span></p>
        <p><strong class="text-gray-900 dark:text-gray-100">Teacher:</strong> <span class="text-gray-700 dark:text-gray-200">{{ $record->subject->teacher->name ?? 'N/A' }}</span></p>
        <p><strong class="text-gray-900 dark:text-gray-100">Date:</strong> <span class="text-gray-700 dark:text-gray-200">{{ $record->exam_date }}</span></p>
        <p><strong class="text-gray-900 dark:text-gray-100">Assessment Type:</strong> <span class="text-gray-700 dark:text-gray-200">{{ $record->assessment_type }}</span></p>
        <p><strong class="text-gray-900 dark:text-gray-100">Duration:</strong> <span class="text-gray-700 dark:text-gray-200">{{ $record->duration }} minutes</span></p>
        <p><strong class="text-gray-900 dark:text-gray-100">Total Score:</strong> <span class="text-gray-700 dark:text-gray-200">{{ $record->total_score }}</span></p>
        <p><strong class="text-gray-900 dark:text-gray-100">Instructions:</strong> <span class="text-gray-700 dark:text-gray-200">{{ strip_tags($record->instructions) }}</span></p>
    </div>

    <h2 class="mt-8 text-xl font-semibold text-gray-900 dark:text-gray-100">Questions</h2>

    @if($record->questions->isNotEmpty())
        <div class="mt-4 space-y-6">
            @foreach($record->questions as $question)
                <div class="p-6 mb-4 border-2 border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                    <div class="space-y-2">
                        <div>
                            <strong class="text-lg text-gray-900 dark:text-gray-100">Question {{ $loop->index + 1 }}:</strong>
                            <p class="mt-2 text-gray-700 dark:text-gray-200">{{ $question->question }}</p>
                        </div>

                        <div>
                            <strong class="text-sm text-gray-900 dark:text-gray-100">Type:</strong>
                            <p class="text-gray-500 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $question->question_type)) }}</p>
                        </div>

                        <div>
                            <strong class="text-sm text-gray-900 dark:text-gray-100">Options:</strong>
                            @if(!is_null($question->options))
                                <div class="mt-2 text-gray-500 list-disc list-inside dark:text-gray-200">
                                    @foreach($question->options as $key => $option)

                                        @if(is_numeric($key))
                                            {{-- No explicit key, use A, B, C... --}}
                                            <p>
                                                {{ chr(65 + $key) }}: {{ $option['option'] }}</p>
                                        @else
                                            {{-- Key exists, use it --}}
                                            <p>{{ $key }}: {{ $option }}<p>
                                        @endif
                                    @endforeach
                                            </div>
                            @else
                                <p class="text-gray-500 dark:text-gray-400">N/A</p>
                            @endif
                        </div>

                        <div>
                            <strong class="text-sm text-gray-900 dark:text-gray-100">Answer:</strong>
                            <p class="text-gray-700 dark:text-gray-200">{{ $question->answer }}</p>
                        </div>

                        <div>
                            <strong class="text-sm text-gray-900 dark:text-gray-100">Hint:</strong>
                            <p class="text-gray-500 dark:text-gray-400">{{ $question->hint ?? 'No hint available' }}</p>
                        </div>

                        @if($question->image)
                            <div class="mt-4">
                                <strong class="text-sm text-gray-900 dark:text-gray-100">Image:</strong><br>
                                <img src="{{ $question->image }}" alt="Question Image" class="h-auto max-w-full rounded-lg shadow-lg">
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-700 dark:text-gray-200">No questions available for this exam.</p>
    @endif
</div>
