<div class="space-y-6">
    <!-- Question Text -->
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Question</h3>
        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-gray-900 dark:text-gray-100">{{ $question->question }}</p>
        </div>
    </div>

    <!-- Question Type and Marks -->
    <div class="grid grid-cols-2 gap-4">
        <div>
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Question Type</h4>
            <x-filament::badge
                :color="match($question->question_type) {
                    'multiple_choice' => 'success',
                    'true_false' => 'warning',
                    'open_ended' => 'info',
                    default => 'gray'
                }"
            >
                {{ match($question->question_type) {
                    'multiple_choice' => 'Multiple Choice',
                    'true_false' => 'True/False',
                    'open_ended' => 'Open Ended',
                    default => ucfirst(str_replace('_', ' ', $question->question_type))
                } }}
            </x-filament::badge>
        </div>

        <div>
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Marks</h4>
            <x-filament::badge color="primary">{{ $question->marks }} marks</x-filament::badge>
        </div>
    </div>

    <!-- Options (for multiple choice) -->
    @if($question->question_type === 'multiple_choice' && $question->options)
        <div>
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Options</h4>
            <div class="space-y-2">
                @foreach($question->options as $key => $option)
                    <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        @if(is_numeric($key))
                            <span class="font-medium text-gray-600 dark:text-gray-400 mr-3">
                                {{ chr(65 + $key) }}.
                            </span>
                            <span class="text-gray-900 dark:text-gray-100">
                                {{ is_array($option) ? $option['option'] : $option }}
                            </span>
                            @if(is_array($option) && isset($option['image']) && $option['image'])
                                <div class="ml-3">
                                    <img src="{{ Storage::disk('s3')->url($option['image']) }}"
                                         alt="Option image"
                                         class="h-16 w-16 object-cover rounded">
                                </div>
                            @endif
                        @else
                            <span class="font-medium text-gray-600 dark:text-gray-400 mr-3">{{ $key }}.</span>
                            <span class="text-gray-900 dark:text-gray-100">{{ $option }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Correct Answer -->
    <div>
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Correct Answer</h4>
        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
            <p class="text-green-800 dark:text-green-200 font-medium">{{ $question->answer }}</p>
        </div>
    </div>

    <!-- Hint -->
    @if($question->hint)
        <div>
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hint</h4>
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <p class="text-blue-800 dark:text-blue-200">{{ $question->hint }}</p>
            </div>
        </div>
    @endif

    <!-- Question Image -->
    @if($question->image)
        <div>
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Question Image</h4>
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <img src="{{ Storage::disk('s3')->url($question->image) }}"
                     alt="Question image"
                     class="w-full h-auto max-h-96 object-contain">
            </div>
        </div>
    @endif

    <!-- Metadata -->
    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
        <div>
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Created</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $question->created_at->format('M j, Y g:i A') }}</p>
        </div>
        <div>
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Updated</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $question->updated_at->format('M j, Y g:i A') }}</p>
        </div>
    </div>
</div>
