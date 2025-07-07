<div class="space-y-6">
    <!-- Question Summary -->
    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Question</h3>
        <p class="text-gray-700 dark:text-gray-300 text-sm">{{ Str::limit($question->question, 150) }}</p>

        <div class="mt-3 flex space-x-4">
            <span class="text-xs">
                <strong>Correct Answer:</strong>
                <span class="text-green-600 dark:text-green-400">{{ $question->answer }}</span>
            </span>
            <span class="text-xs">
                <strong>Marks:</strong> {{ $question->marks }}
            </span>
        </div>
    </div>

    <!-- Response Statistics -->
    @php
        $total = $responses->count();
        $correct = $responses->where('answer', $question->answer)->count();
        $incorrect = $total - $correct;
        $percentage = $total > 0 ? round(($correct / $total) * 100, 1) : 0;
    @endphp

    <div class="grid grid-cols-4 gap-4">
        <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $total }}</div>
            <div class="text-xs text-blue-600 dark:text-blue-400">Total Responses</div>
        </div>

        <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $correct }}</div>
            <div class="text-xs text-green-600 dark:text-green-400">Correct</div>
        </div>

        <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
            <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $incorrect }}</div>
            <div class="text-xs text-red-600 dark:text-red-400">Incorrect</div>
        </div>

        <div class="text-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $percentage }}%</div>
            <div class="text-xs text-yellow-600 dark:text-yellow-400">Success Rate</div>
        </div>
    </div>

    <!-- Individual Responses -->
    @if($responses->count() > 0)
        <div>
            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Individual Responses</h4>

            <div class="max-h-96 overflow-y-auto space-y-3">
                @foreach($responses as $response)
                    @php
                        $isCorrect = $response->answer === $question->answer;
                    @endphp

                    <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg
                        {{ $isCorrect ? 'bg-green-50 dark:bg-green-900/10 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800' }}">

                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                @if($isCorrect)
                                    <x-heroicon-s-check-circle class="h-5 w-5 text-green-500" />
                                @else
                                    <x-heroicon-s-x-circle class="h-5 w-5 text-red-500" />
                                @endif
                            </div>

                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ $response->quizScore->student->name ?? 'Unknown Student' }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Answer: <span class="font-mono">{{ $response->answer ?? 'No answer' }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="text-sm font-medium {{ $isCorrect ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $isCorrect ? '+' . $question->marks : '0' }} marks
                            </p>
                            @if($response->created_at)
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $response->created_at->format('M j, g:i A') }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="text-center py-8">
            <x-heroicon-o-inbox class="h-12 w-12 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No responses yet</h3>
            <p class="text-gray-600 dark:text-gray-400">No students have answered this question.</p>
        </div>
    @endif

    <!-- Answer Distribution (for multiple choice) -->
    @if($question->question_type === 'multiple_choice' && $responses->count() > 0)
        <div>
            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Answer Distribution</h4>

            @php
                $answerCounts = $responses->groupBy('answer')->map->count()->sortByDesc(function($count) {
                    return $count;
                });
            @endphp

            <div class="space-y-2">
                @foreach($answerCounts as $answer => $count)
                    @php
                        $percentage = round(($count / $total) * 100, 1);
                        $isCorrect = $answer === $question->answer;
                    @endphp

                    <div class="flex items-center space-x-3">
                        <div class="w-16 text-sm font-mono {{ $isCorrect ? 'text-green-600 dark:text-green-400 font-bold' : 'text-gray-600 dark:text-gray-400' }}">
                            {{ $answer ?: 'Empty' }}
                        </div>

                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-4 relative">
                            <div class="h-4 rounded-full {{ $isCorrect ? 'bg-green-500' : 'bg-blue-500' }}"
                                 style="width: {{ $percentage }}%"></div>
                        </div>

                        <div class="w-16 text-sm text-right {{ $isCorrect ? 'text-green-600 dark:text-green-400 font-bold' : 'text-gray-600 dark:text-gray-400' }}">
                            {{ $count }} ({{ $percentage }}%)
                        </div>

                        @if($isCorrect)
                            <x-heroicon-s-check-circle class="h-4 w-4 text-green-500" />
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
