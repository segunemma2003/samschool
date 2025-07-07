<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Student and Exam Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Student Information Card -->
            <x-filament::section>
                <x-slot name="heading">
                    Student Information
                </x-slot>

                <div class="space-y-3">
                    <div class="flex items-center space-x-3">
                        @if($this->studentDetails->avatar)
                            <img src="{{ Storage::url($this->studentDetails->avatar) }}"
                                 alt="{{ $this->studentDetails->name }}"
                                 class="w-12 h-12 rounded-full object-cover">
                        @else
                            <div class="w-12 h-12 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                <span class="text-gray-600 dark:text-gray-300 font-semibold">
                                    {{ substr($this->studentDetails->name, 0, 2) }}
                                </span>
                            </div>
                        @endif
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ $this->studentDetails->name }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $this->studentDetails->registration_number ?? 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Class:</span>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $this->studentDetails->class->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Email:</span>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $this->studentDetails->email ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </x-filament::section>

            <!-- Exam Information Card -->
            <x-filament::section>
                <x-slot name="heading">
                    Exam Information
                </x-slot>

                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Subject:</span>
                        <p class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $this->quizScore->exam->subject->subjectDepot->name ?? 'N/A' }}
                        </p>
                    </div>

                    <div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Teacher:</span>
                        <p class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $this->quizScore->exam->subject->teacher->name ?? 'N/A' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Assessment Type:</span>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ ucfirst($this->quizScore->exam->assessment_type ?? 'N/A') }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Exam Date:</span>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $this->quizScore->exam->exam_date ? \Carbon\Carbon::parse($this->quizScore->exam->exam_date)->format('M j, Y') : 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <!-- Score Summary -->
        <x-filament::section>
            <x-slot name="heading">
                Score Summary
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ $this->quizScore->total_score ?? 0 }}
                    </div>
                    <div class="text-sm text-blue-600 dark:text-blue-400">Total Score</div>
                </div>

                <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ $this->quizScore->exam->total_score ?? 0 }}
                    </div>
                    <div class="text-sm text-green-600 dark:text-green-400">Maximum Score</div>
                </div>

                <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                        @php
                            $percentage = 0;
                            if ($this->quizScore->exam->total_score > 0) {
                                $percentage = round(($this->quizScore->total_score / $this->quizScore->exam->total_score) * 100, 1);
                            }
                        @endphp
                        {{ $percentage }}%
                    </div>
                    <div class="text-sm text-yellow-600 dark:text-yellow-400">Percentage</div>
                </div>

                <div class="text-center p-4 {{ $this->quizScore->approved === 'yes' ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }} rounded-lg">
                    <div class="text-2xl font-bold {{ $this->quizScore->approved === 'yes' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $this->quizScore->approved === 'yes' ? 'Approved' : 'Pending' }}
                    </div>
                    <div class="text-sm {{ $this->quizScore->approved === 'yes' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">Status</div>
                </div>
            </div>
        </x-filament::section>

        <!-- Question Details -->
        <x-filament::section>
            <x-slot name="heading">
                Question-by-Question Analysis
            </x-slot>

            @if($this->questions && $this->questions->count() > 0)
                <div class="space-y-6">
                    @foreach($this->questions as $index => $submission)
                        @php
                            $question = $submission->question;
                            $isCorrect = $submission->answer === $question->answer;
                        @endphp

                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 {{ $isCorrect ? 'bg-green-50 dark:bg-green-900/10' : 'bg-red-50 dark:bg-red-900/10' }}">
                            <!-- Question Header -->
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    Question {{ $index + 1 }}
                                </h4>

                                <div class="flex items-center space-x-2">
                                    @if($isCorrect)
                                        <x-heroicon-s-check-circle class="h-6 w-6 text-green-500" />
                                        <span class="text-green-600 dark:text-green-400 font-medium">Correct</span>
                                    @else
                                        <x-heroicon-s-x-circle class="h-6 w-6 text-red-500" />
                                        <span class="text-red-600 dark:text-red-400 font-medium">Incorrect</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Question Text -->
                            <div class="mb-4">
                                <p class="text-gray-900 dark:text-gray-100 mb-2">
                                    {{ $question->question }}
                                </p>

                                @if($question->image)
                                    <div class="mt-3">
                                        <img src="{{ Storage::disk('s3')->url($question->image) }}"
                                             alt="Question image"
                                             class="max-w-full h-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                    </div>
                                @endif
                            </div>

                            <!-- Question Details -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Student Answer:</span>
                                    <p class="font-medium {{ $isCorrect ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $submission->answer ?? 'No answer provided' }}
                                    </p>
                                </div>

                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Correct Answer:</span>
                                    <p class="font-medium text-green-600 dark:text-green-400">
                                        {{ $question->answer }}
                                    </p>
                                </div>

                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Score:</span>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $isCorrect ? $question->marks : 0 }} / {{ $question->marks }} points
                                    </p>
                                </div>
                            </div>

                            @if($question->hint)
                                <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                    <span class="text-blue-800 dark:text-blue-200 text-sm font-medium">Hint: </span>
                                    <span class="text-blue-700 dark:text-blue-300 text-sm">{{ $question->hint }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <x-heroicon-o-document-text class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No Questions Found</h3>
                    <p class="text-gray-600 dark:text-gray-400">No question submissions were found for this exam attempt.</p>
                </div>
            @endif
        </x-filament::section>

        <!-- Exam Instructions (if available) -->
        @if($this->quizScore->exam->instructions)
            <x-filament::section>
                <x-slot name="heading">
                    Exam Instructions
                </x-slot>

                <div class="prose dark:prose-invert max-w-none">
                    {!! $this->quizScore->exam->instructions !!}
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
