<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Exam Overview Card -->
        <div class="mb-6">
            <x-filament::section>
                <x-slot name="heading">
                    Exam Students Overview
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="text-sm text-blue-600 dark:text-blue-400 mb-1">Subject</div>
                        <div class="font-semibold text-blue-900 dark:text-blue-100">
                            {{ $this->record->subject->subjectDepot->name ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div class="text-sm text-green-600 dark:text-green-400 mb-1">Teacher</div>
                        <div class="font-semibold text-green-900 dark:text-green-100">
                            {{ $this->record->subject->teacher->name ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <div class="text-sm text-yellow-600 dark:text-yellow-400 mb-1">Assessment Type</div>
                        <div class="font-semibold text-yellow-900 dark:text-yellow-100">
                            {{ ucfirst($this->record->assessment_type) }}
                        </div>
                    </div>

                    <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                        <div class="text-sm text-purple-600 dark:text-purple-400 mb-1">Exam Date</div>
                        <div class="font-semibold text-purple-900 dark:text-purple-100">
                            {{ $this->record->exam_date ? \Carbon\Carbon::parse($this->record->exam_date)->format('M j, Y') : 'N/A' }}
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <!-- Students Table -->
        <div>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
