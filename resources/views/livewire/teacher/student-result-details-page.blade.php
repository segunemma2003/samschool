{{-- Single Root Element for Livewire Component --}}
<div wire:key="student-result-{{ $record }}" class="student-result-container">
    @if(isset($errorMessage) && $errorMessage)
        <div class="p-4 mb-6 bg-red-100 border border-red-300 text-red-800 rounded-lg">
            <strong>Error:</strong> {{ $errorMessage }}
        </div>
    @else
        <div class="space-y-6">
            {{-- Student Header --}}
            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ $student->name ?? 'Student Not Found' }}
                </h1>
                @if($student?->class)
                    <p class="text-gray-600 dark:text-gray-400">
                        Class: {{ $student->class->name }}
                    </p>
                @endif
            </div>

            {{-- Summary Cards --}}
            @if($total !== null && $totalSubject > 0)
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-white rounded-lg shadow text-center dark:bg-gray-800">
                        <div class="text-2xl font-bold text-blue-600">{{ number_format($total, 1) }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Total Score</div>
                    </div>
                    <div class="p-4 bg-white rounded-lg shadow text-center dark:bg-gray-800">
                        <div class="text-2xl font-bold text-green-600">{{ $totalSubject }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Subjects</div>
                    </div>
                    <div class="p-4 bg-white rounded-lg shadow text-center dark:bg-gray-800">
                        <div class="text-2xl font-bold text-purple-600">{{ number_format($average, 1) }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Average</div>
                    </div>
                    <div class="p-4 bg-white rounded-lg shadow text-center dark:bg-gray-800">
                        <div class="text-lg font-bold {{ $remarks === 'EXCELLENT' ? 'text-green-600' : ($remarks === 'FAIL' ? 'text-red-600' : 'text-yellow-600') }}">
                            {{ $remarks ?? 'N/A' }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Grade</div>
                    </div>
                </div>
            @endif

            {{-- SINGLE RESULTS TABLE --}}
            <div class="bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Academic Results
                    </h2>
                </div>
                <div class="p-4">
                    {{ $this->table }}
                </div>
            </div>

            {{-- SINGLE COMMENT FORM --}}
            <div class="bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Teacher Comments
                    </h2>
                </div>
                <div class="p-4">
                    <form wire:submit.prevent="saveComment" wire:key="comment-form-{{ $record }}">
                        {{ $this->form }}
                        <div class="mt-6">
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="saveComment"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 disabled:opacity-50"
                            >
                                <svg wire:loading wire:target="saveComment" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="saveComment">Save Comment</span>
                                <span wire:loading wire:target="saveComment">Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
