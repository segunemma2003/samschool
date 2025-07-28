@if(isset($errorMessage) && $errorMessage)
    <div class="p-4 my-6 bg-red-100 border border-red-300 text-red-800 rounded-lg shadow">
        <strong>Error:</strong> {{ $errorMessage }}
    </div>
@else
<div class="flex flex-col gap-4 space-y-5">
    <!-- Student Name Section -->
    <div class="flex items-center justify-between p-4 bg-gray-100 rounded-lg shadow dark:bg-gray-800">
        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">
            Student: {{ $student->name ?? 'N/A' }}
        </h2>
        <div class="flex gap-2">
            <!-- Temporary debug button - remove after testing -->
            <button
                wire:click="debugFilters"
                class="px-3 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600"
            >
                Debug Filters
            </button>
            <!-- Test filter change button -->
            <button
                wire:click="testFilterChange"
                class="px-3 py-1 text-xs bg-green-500 text-white rounded hover:bg-green-600"
            >
                Test Filter Change
            </button>
            <!-- Refresh component button -->
            <button
                wire:click="refreshComponent"
                class="px-3 py-1 text-xs bg-purple-500 text-white rounded hover:bg-purple-600"
            >
                Refresh Data
            </button>
            <!-- Refresh active values button -->
            <button
                wire:click="refreshActiveValuesFromUI"
                class="px-3 py-1 text-xs bg-orange-500 text-white rounded hover:bg-orange-600"
            >
                Refresh Active Values
            </button>
            <!-- Force refresh table filters button -->
            <button
                wire:click="forceTableRefresh"
                class="px-3 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600"
            >
                Reset Filters
            </button>
            <!-- Set filter state button -->
            <button
                wire:click="setFilterState"
                class="px-3 py-1 text-xs bg-indigo-500 text-white rounded hover:bg-indigo-600"
            >
                Set Filter State
            </button>
        </div>
    </div>

    <!-- Custom Filter Controls -->
    <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
        <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">Filter Results</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Term Filter -->
            <div>
                <label for="term-filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Term
                </label>
                <select
                    id="term-filter"
                    wire:model.live="termId"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                >
                    <option value="">Select Term</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ $termId == $term->id ? 'selected' : '' }}>
                            {{ $term->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Academic Year Filter -->
            <div>
                <label for="academic-filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Academic Year
                </label>
                <select
                    id="academic-filter"
                    wire:model.live="academic"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                >
                    <option value="">Select Academic Year</option>
                    @foreach($academicYears as $academicYear)
                        <option value="{{ $academicYear->id }}" {{ $academic == $academicYear->id ? 'selected' : '' }}>
                            {{ $academicYear->title }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Current Filter Status -->
        <div class="mt-4 p-3 bg-gray-50 rounded-md dark:bg-gray-700">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                <strong>Current Filters:</strong>
                Term: {{ $terms->find($termId)?->name ?? 'All' }} |
                Academic Year: {{ $academicYears->find($academic)?->title ?? 'All' }}
            </p>
        </div>

        <!-- Scoreboard Structure Information -->
        @if(isset($scoreboardStructure) && !empty($scoreboardStructure['all_sections']))
        <div class="mt-4 p-3 bg-blue-50 rounded-md dark:bg-blue-900/20">
            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">Scoreboard Structure</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                @if(!empty($scoreboardStructure['input_sections']))
                <div>
                    <span class="font-medium text-blue-700 dark:text-blue-300">Input Scores:</span>
                    <div class="mt-1 space-y-1">
                        @foreach($scoreboardStructure['input_sections'] as $section)
                            <div class="flex justify-between">
                                <span class="text-blue-600 dark:text-blue-400">{{ $section['code'] }}</span>
                                <span class="text-blue-500 dark:text-blue-500">{{ $section['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(!empty($scoreboardStructure['total_sections']))
                <div>
                    <span class="font-medium text-green-700 dark:text-green-300">Total Scores:</span>
                    <div class="mt-1 space-y-1">
                        @foreach($scoreboardStructure['total_sections'] as $section)
                            <div class="flex justify-between">
                                <span class="text-green-600 dark:text-green-400">{{ $section['code'] }}</span>
                                <span class="text-green-500 dark:text-green-500">{{ $section['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(!empty($scoreboardStructure['calculated_sections']))
                <div>
                    <span class="font-medium text-purple-700 dark:text-purple-300">Calculated:</span>
                    <div class="mt-1 space-y-1">
                        @foreach($scoreboardStructure['calculated_sections'] as $section)
                            <div class="flex justify-between">
                                <span class="text-purple-600 dark:text-purple-400">{{ $section['code'] }}</span>
                                <span class="text-purple-500 dark:text-purple-500">{{ $section['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Table Section -->
    <div>
        {{ $this->table }}
    </div>

    <!-- Form Section -->
    <div>
        <form wire:submit.prevent="saveComment">
            {{ $this->form }}
            <br />
            <button
            type="submit"
            class="relative flex items-center justify-center px-6 py-2 font-medium text-white transition bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:outline-none dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-400"
            wire:loading.attr="disabled"
            wire:target="saveComment"
        >
            <!-- Loading Spinner and Text -->
            <span
                wire:loading
                wire:target="saveComment"
                class="flex items-center space-x-2"
            >

                <span class="text-white">Loading...</span>
            </span>

            <!-- Submit Text (Visible when not loading) -->
            <span wire:loading.remove wire:target="saveComment">Submit</span>
        </button>


        </form>

        <x-filament-actions::modals />
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('refreshPage', () => {
            window.location.reload();
        });
    });
</script>
@endif
