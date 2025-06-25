<x-filament-panels::page>
    <!-- Assignment Overview Card -->
    <div class="mb-6">
        <x-filament::card>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="text-sm text-blue-600 dark:text-blue-400 mb-1">Assignment</div>
                    <div class="font-semibold text-blue-900 dark:text-blue-100">{{ $this->record->title }}</div>
                </div>

                <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <div class="text-sm text-green-600 dark:text-green-400 mb-1">Total Marks</div>
                    <div class="font-semibold text-green-900 dark:text-green-100">{{ $this->record->weight_mark }} pts</div>
                </div>

                <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <div class="text-sm text-yellow-600 dark:text-yellow-400 mb-1">Deadline</div>
                    <div class="font-semibold text-yellow-900 dark:text-yellow-100">
                        {{ $this->record->deadline?->format('M j, Y') ?? 'No deadline' }}
                    </div>
                </div>

                <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                    <div class="text-sm text-purple-600 dark:text-purple-400 mb-1">Status</div>
                    <div class="font-semibold text-purple-900 dark:text-purple-100">
                        @if($this->record->deadline && $this->record->deadline->isPast())
                            <x-filament::badge color="danger">Overdue</x-filament::badge>
                        @else
                            <x-filament::badge color="success">Active</x-filament::badge>
                        @endif
                    </div>
                </div>
            </div>
        </x-filament::card>
    </div>

    <!-- Students Table -->
    <div>
        {{ $this->table }}
    </div>
</x-filament-panels::page>
