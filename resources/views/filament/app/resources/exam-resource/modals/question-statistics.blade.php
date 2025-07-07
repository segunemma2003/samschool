<div class="space-y-6">
    <!-- Statistics Overview -->
    @if(isset($statistics))
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $statistics['total_questions'] ?? 0 }}</div>
                <div class="text-xs text-blue-600 dark:text-blue-400">Total Questions</div>
            </div>

            <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $statistics['answered'] ?? 0 }}</div>
                <div class="text-xs text-green-600 dark:text-green-400">Answered</div>
            </div>

            <div class="text-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $statistics['avg_score'] ?? '0' }}%</div>
                <div class="text-xs text-yellow-600 dark:text-yellow-400">Average Score</div>
            </div>

            <div class="text-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $statistics['completion_rate'] ?? '0' }}%</div>
                <div class="text-xs text-purple-600 dark:text-purple-400">Completion Rate</div>
            </div>
        </div>
    @endif

    <!-- Question Breakdown -->
    @if(isset($questionBreakdown) && count($questionBreakdown) > 0)
        <div>
            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Question Performance</h4>

            <div class="space-y-3">
                @foreach($questionBreakdown as $question)
                    <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div class="flex justify-between items-start mb-2">
                            <h5 class="font-medium text-gray-900 dark:text-gray-100">
                                Question {{ $loop->iteration }}
                            </h5>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $question['correct_answers'] }}/{{ $question['total_answers'] }} correct
                            </span>
                        </div>

                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                            {{ Str::limit($question['question_text'], 100) }}
                        </p>

                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $question['success_rate'] }}%"></div>
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                            {{ $question['success_rate'] }}% success rate
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(!isset($statistics) && !isset($questionBreakdown))
        <div class="text-center py-8">
            <div class="text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="text-lg font-medium mb-2">No Statistics Available</h3>
                <p>No student responses found for this exam.</p>
            </div>
        </div>
    @endif
</div>
