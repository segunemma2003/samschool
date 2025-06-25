 <!-- Assignment Instructions (Always Show) -->
        @if($this->assignment->description)
            <x-filament::card>
                <x-slot name="heading">
                    Assignment Instructions
                </x-slot>

                <div class="prose prose-sm max-w-none dark:prose-invert">
                    {!! $this->assignment->description !!}
                </div>

                @if($this->assignment->file)
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="font-semibold mb-3 flex items-center">
                            <x-heroicon-s-paper-clip class="w-5 h-5 mr-2" />
                            Assignment Materials
                        </h4>
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <x-heroicon-s-document class="w-8 h-8 text-blue-500" />
                                    <div>
                                        <p class="font-medium">{{ basename($this->assignment->file) }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Assignment material
                                        </p>
                                    </div>
                                </div>
                                @if($this->assignment->file)
                                    <x-filament::button
                                        size="sm"
                                        color="gray"
                                        href="{{ route('secure.download.assignment', ['assignment' => $this->assignment->id]) }}"
                                        target="_blank">
                                        <x-heroicon-s-arrow-down-tray class="w-4 h-4 mr-1" />
                                        Download
                                    </x-filament::button>
                                @else
                                    <x-filament::button
                                        size="sm"
                                        color="gray"
                                        disabled>
                                        <x-heroicon-s-document class="w-4 h-4 mr-1" />
                                        No Material
                                    </x-filament::button>
                                @endif
                            </div>
                        </div>
                    </div>
                <x-filament-panels::page>
    <div class="space-y-6">
        <!-- Assignment & Student Info -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Assignment Details -->
            <x-filament::card>
                <x-slot name="heading">
                    Assignment Details
                </x-slot>

                <div class="space-y-4">
                    <div>
                        <x-filament::badge color="primary" size="lg">
                            {{ $assignment->title }}
                        </x-filament::badge>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-400">Subject:</span>
                            <p class="mt-1">{{ $assignment->subject?->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-400">Class:</span>
                            <p class="mt-1">{{ $assignment->class?->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-400">Total Marks:</span>
                            <p class="mt-1">{{ $assignment->weight_mark }} points</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-400">Deadline:</span>
                            <p class="mt-1">{{ $assignment->deadline?->format('M j, Y g:i A') ?? 'No deadline' }}</p>
                        </div>
                    </div>

                    @if($assignment->description)
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-400">Description:</span>
                            <div class="mt-2 prose prose-sm max-w-none dark:prose-invert">
                                {!! $assignment->description !!}
                            </div>
                        </div>
                    @endif
                </div>
            </x-filament::card>

            <!-- Student Details -->
            <x-filament::card>
                <x-slot name="heading">
                    Student Information
                </x-slot>

                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        @if($student->avatar)
                            <img src="{{ asset('storage/' . $student->avatar) }}"
                                 alt="{{ $student->name }}"
                                 class="w-16 h-16 rounded-full object-cover">
                        @else
                            <div class="w-16 h-16 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                <x-heroicon-s-user class="w-8 h-8 text-gray-400" />
                            </div>
                        @endif
                        <div>
                            <h3 class="text-lg font-semibold">{{ $student->name }}</h3>
                            <p class="text-gray-600 dark:text-gray-400">{{ $student->registration_number }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-400">Class:</span>
                            <p class="mt-1">{{ $student->class?->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-400">Arm:</span>
                            <p class="mt-1">{{ $student->arm?->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-400">Email:</span>
                            <p class="mt-1">{{ $student->email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-400">Phone:</span>
                            <p class="mt-1">{{ $student->phone ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </x-filament::card>
        </div>

        <!-- Submission Details -->
        @if($submission)
            <x-filament::card>
                <x-slot name="heading">
                    Submission Details
                </x-slot>

                <div class="space-y-6">
                    <!-- Submission Status and Score -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Status</div>
                            <x-filament::badge :color="$this->getSubmissionStatusColor()">
                                {{ $this->getSubmissionStatus() }}
                            </x-filament::badge>
                        </div>

                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Score</div>
                            <div class="text-lg font-semibold">
                                @if($submission->total_score !== null)
                                    <x-filament::badge :color="$this->getGradeColor()" size="lg">
                                        {{ $submission->total_score }}/{{ $assignment->weight_mark }}
                                    </x-filament::badge>
                                @else
                                    <span class="text-gray-500">Not graded</span>
                                @endif
                            </div>
                        </div>

                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Percentage</div>
                            <div class="text-lg font-semibold">
                                @if($this->getPercentageScore())
                                    <x-filament::badge :color="$this->getGradeColor()" size="lg">
                                        {{ $this->getPercentageScore() }}
                                    </x-filament::badge>
                                @else
                                    <span class="text-gray-500">N/A</span>
                                @endif
                            </div>
                        </div>

                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Submitted</div>
                            <div class="text-sm font-medium">
                                {{ $submission->updated_at?->format('M j, Y') ?? 'N/A' }}
                                <br>
                                <span class="text-xs text-gray-500">
                                    {{ $submission->updated_at?->format('g:i A') ?? '' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Student Answer -->
                    @if($submission->answer)
                        <div>
                            <h4 class="text-lg font-semibold mb-3 flex items-center">
                                <x-heroicon-s-document-text class="w-5 h-5 mr-2" />
                                Student Answer
                            </h4>
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                                <div class="prose prose-sm max-w-none dark:prose-invert">
                                    {!! nl2br(e($submission->answer)) !!}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Attached File -->
                    @if($submission->file)
                        <div>
                            <h4 class="text-lg font-semibold mb-3 flex items-center">
                                <x-heroicon-s-paper-clip class="w-5 h-5 mr-2" />
                                Attached File
                            </h4>
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <x-heroicon-s-document class="w-8 h-8 text-blue-500" />
                                        <div>
                                            <p class="font-medium">{{ basename($submission->file) }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Uploaded: {{ $submission->updated_at?->format('M j, Y g:i A') }}
                                            </p>
                                        </div>
                                    </div>
                                    @if($submission->file)
                                        <x-filament::button
                                            size="sm"
                                            color="gray"
                                            href="{{ route('secure.download.submission', ['assignment' => $assignment->id, 'student' => $student->id]) }}"
                                            target="_blank">
                                            <x-heroicon-s-arrow-down-tray class="w-4 h-4 mr-1" />
                                            Download
                                        </x-filament::button>
                                    @else
                                        <x-filament::button
                                            size="sm"
                                            color="gray"
                                            disabled>
                                            <x-heroicon-s-document class="w-4 h-4 mr-1" />
                                            No File
                                        </x-filament::button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Teacher Comments -->
                    @if($submission->comments_score)
                        <div>
                            <h4 class="text-lg font-semibold mb-3 flex items-center">
                                <x-heroicon-s-chat-bubble-left-ellipsis class="w-5 h-5 mr-2" />
                                Teacher Comments
                            </h4>
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                <div class="prose prose-sm max-w-none dark:prose-invert">
                                    {{ $submission->comments_score }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </x-filament::card>
        @else
            <!-- No Submission -->
            <x-filament::card>
                <div class="text-center py-12">
                    <x-heroicon-o-exclamation-triangle class="w-16 h-16 text-yellow-500 mx-auto mb-4" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        No Submission Found
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        This student hasn't submitted their assignment yet.
                    </p>

                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 max-w-md mx-auto">
                        <div class="flex items-center space-x-2 text-yellow-800 dark:text-yellow-200">
                            <x-heroicon-s-clock class="w-5 h-5" />
                            <span class="font-medium">Assignment Status:</span>
                        </div>
                        <p class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                            @if($assignment->deadline && $assignment->deadline->isPast())
                                This assignment is overdue. Deadline was {{ $assignment->deadline->format('M j, Y g:i A') }}.
                            @else
                                Assignment is still open for submission.
                                @if($assignment->deadline)
                                    Deadline: {{ $assignment->deadline->format('M j, Y g:i A') }}
                                @endif
                            @endif
                        </p>
                    </div>
                </div>
            </x-filament::card>
        @endif

        <!-- Assignment Instructions (Always Show) -->
        @if($assignment->description)
            <x-filament::card>
                <x-slot name="heading">
                    Assignment Instructions
                </x-slot>

                <div class="prose prose-sm max-w-none dark:prose-invert">
                    {!! $assignment->description !!}
                </div>

                @if($assignment->file)
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="font-semibold mb-3 flex items-center">
                            <x-heroicon-s-paper-clip class="w-5 h-5 mr-2" />
                            Assignment Materials
                        </h4>
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <x-heroicon-s-document class="w-8 h-8 text-blue-500" />
                                    <div>
                                        <p class="font-medium">{{ basename($assignment->file) }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Assignment material
                                        </p>
                                    </div>
                                </div>
                                @if($assignment->file)
                                    <x-filament::button
                                        size="sm"
                                        color="gray"
                                        href="{{ route('secure.download.assignment', ['assignment' => $assignment->id]) }}"
                                        target="_blank">
                                        <x-heroicon-s-arrow-down-tray class="w-4 h-4 mr-1" />
                                        Download
                                    </x-filament::button>
                                @else
                                    <x-filament::button
                                        size="sm"
                                        color="gray"
                                        disabled>
                                        <x-heroicon-s-document class="w-4 h-4 mr-1" />
                                        No Material
                                    </x-filament::button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </x-filament::card>
        @endif
    </div>
</x-filament-panels::page>
