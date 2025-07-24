<div class="p-4 space-y-6">
    <!-- Lively Header -->
    <div class="flex items-center gap-4 mb-2">
        <div class="bg-blue-100 text-blue-700 rounded-full p-2">
            <x-heroicon-o-academic-cap class="w-8 h-8" />
        </div>
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Enter & Review Student Scores</h1>
            <div class="text-sm text-gray-500 dark:text-gray-300">Input, review, and save marks for each student. Auto-calculated fields are shown as read-only.</div>
        </div>
    </div>

    <!-- Upper Form Panel -->
    <div class="p-4 bg-white rounded shadow dark:bg-gray-800">
        <div class="flex items-center justify-between p-4 bg-gray-100 rounded-lg shadow dark:bg-gray-800">
            <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">
                Teacher: {{ $subject->teacher->name ?? 'N/A' }}
            </h2>
            <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">
                Subject: {{ $subject->subjectDepot->name ?? 'N/A' }}
            </h2>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="term" class="block font-medium">Academy year</label>
                <select wire:model.live="academic" id="academic" name="academic" required
                    class="block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-800">
                    <option value="">Select Academic Year</option>
                    @foreach($academicYears as $academy)
                        <option value="{{ $academy->id }}">{{ $academy->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="term" class="block font-medium">Term</label>
                <select wire:model.live="termId" id="term" name="term_id" required
                    class="block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-800">
                    <option value="">Select Term</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="p-4 bg-white rounded-2xl shadow-lg dark:bg-gray-900 overflow-x-auto">
        <h3 class="text-lg font-bold text-gray-700 dark:text-gray-200 mb-2">Student Results</h3>
        <table class="w-full border-separate border-spacing-0 font-sans">
            <thead>
                <tr>
                    <th colspan="{{ 2 + count($resTitle) }}" class="bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600 dark:from-cyan-800 dark:via-cyan-900 dark:to-blue-900 text-white text-lg font-bold rounded-t-2xl shadow-md px-4 py-3 text-left tracking-wide border-b border-blue-200 dark:border-cyan-900">
                        Student Scores Table
                    </th>
                </tr>
                <tr class="bg-blue-100 dark:bg-cyan-900">
                    <th class="px-4 py-2 text-gray-700 border-b border-blue-200 dark:border-cyan-800 dark:text-gray-200 font-semibold">No.</th>
                    <th class="px-4 py-2 text-gray-700 border-b border-blue-200 dark:border-cyan-800 dark:text-gray-200 font-semibold">Student</th>
                        @foreach($resTitle as $detail)
                            <th class="px-4 py-2 text-gray-700 border-b border-blue-200 dark:border-cyan-800 dark:text-gray-200 font-semibold">
                               {{ $detail->name }}
                            </th>
                        @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($students as $index => $student)
                    <tr class="{{ $index % 2 === 0 ? 'bg-blue-50 dark:bg-gray-800' : 'bg-white dark:bg-gray-900' }} hover:bg-blue-100 dark:hover:bg-cyan-800 transition rounded-b-xl">
                        <td class="px-4 py-3 text-center text-gray-700 border-b border-blue-100 dark:border-cyan-900 dark:text-gray-200">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 text-gray-700 border-b border-blue-100 dark:border-cyan-900 dark:text-gray-200 flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gradient-to-br from-blue-200 to-blue-400 dark:from-cyan-700 dark:to-cyan-900 text-blue-800 dark:text-cyan-100 font-bold shadow">
                                {{ strtoupper(substr($student->student->name, 0, 1)) }}
                            </span>
                            <span>{{ $student->student->name }}</span>
                        </td>
                            @foreach($this->resTitle as $detail)
                                <td class="px-4 py-3 border-b border-blue-100 dark:border-cyan-900">
                                    @if ($detail->calc_pattern == 'total')
                                    <input
                                        type="number"
                                        wire:model="studentValues.{{ $student->id }}.{{ $detail->id }}"
                                        class="w-full px-2 py-1 text-gray-800 bg-gray-100 border border-gray-300 rounded-lg dark:bg-gray-800 dark:text-gray-300 dark:border-cyan-800 cursor-not-allowed shadow-sm"
                                        readonly
                                        title="Auto-calculated total"
                                    />
                                    @elseif($detail->calc_pattern == 'grade_level')
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full shadow {{
                                        ($studentValues[$student->id][$detail->id] ?? '') === 'A1' ? 'bg-green-200 text-green-800 dark:bg-green-700 dark:text-green-100' :
                                        (in_array($studentValues[$student->id][$detail->id] ?? '', ['B2','B3','C4','C5','C6']) ? 'bg-yellow-200 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100' :
                                        (($studentValues[$student->id][$detail->id] ?? '') ? 'bg-red-200 text-red-800 dark:bg-red-700 dark:text-red-100' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300'))
                                    }}" title="Grade Level">
                                        {{ $studentValues[$student->id][$detail->id] ?? '' }}
                                    </span>
                                    @else
                                        <input
                                            type="{{ $detail->type == 'numeric' ? 'number' : 'text' }}"
                                            wire:model.lazy="studentValues.{{ $student->id }}.{{ $detail->id }}"
                                            class="w-full px-2 py-1 text-gray-700 border border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-cyan-800 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-cyan-400 dark:focus:border-cyan-400 shadow-sm"
                                            placeholder="Value for {{ $detail->name }}"
                                            {{ $detail->type == 'numeric' ? 'max='.$detail->score_weight : '' }}
                                            oninput="{{ $detail->type == 'numeric' ? 'this.value = Math.min(this.value, this.max)' : '' }}"
                                            pattern="{{ $detail->type == 'text' ? '[A-Za-z0-9 ]*' : '' }}"
                                            {{ $detail->calc_pattern != 'input' ? 'disabled' : '' }}
                                            title="{{ $detail->calc_pattern != 'input' ? 'Auto-calculated or read-only' : 'Enter value' }}"
                                        />
                                    @endif
                                </td>
                            @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Save Button & Notification -->
    <div class="flex justify-end">
        <button
            wire:click="saveResults"
            class="flex items-center gap-2 min-w-[180px] whitespace-nowrap px-6 py-2 text-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-400 rounded shadow hover:from-blue-700 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 dark:focus:ring-cyan-400 dark:focus:ring-offset-gray-900 transition"
        >
            <span wire:loading.remove class="whitespace-nowrap">💾 Save Results</span>
            <span wire:loading class="flex items-center gap-2 whitespace-nowrap">
                <svg
                    class="w-5 h-5 text-white animate-spin"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 118 8 8 8 0 01-8-8z"
                    ></path>
                </svg>
                <span>Saving...</span>
            </span>
        </button>
        <!-- Success notification remains unchanged -->
        <div wire:loading.remove class="ml-4" x-data="{ show: false }" x-show.transition.opacity.duration.500ms="show" x-init="window.livewire.on('resultsSaved', () => { show = true; setTimeout(() => show = false, 2000); })">
            <span class="inline-block px-3 py-1 text-green-800 bg-green-100 dark:bg-green-700 dark:text-green-100 rounded-full shadow">Results saved!</span>
        </div>
    </div>
</div>
