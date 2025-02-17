<div class="p-4 space-y-6">
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
    <div class="p-4 bg-white rounded shadow dark:bg-gray-800">
        <h3 class="text-lg font-bold text-gray-700 dark:text-gray-200">Student Results</h3>
        <table class="w-full border border-collapse border-gray-200 dark:border-gray-600">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-2 text-gray-700 border border-gray-700 dark:border-gray-600 dark:text-gray-200">No.</th>
                    <th class="px-4 py-2 text-gray-700 border border-gray-700 dark:border-gray-600 dark:text-gray-200">Student</th>
                    {{-- @foreach($resultSections as $section) --}}
                        @foreach($resultSections->resultDetails as $detail)
                            <th class="px-4 py-2 text-gray-700 border border-gray-700 dark:border-gray-600 dark:text-gray-200">
                               {{ $detail->name }}
                            </th>
                        @endforeach
                    {{-- @endforeach --}}
                </tr>
            </thead>
            <tbody>
                @foreach($students as $index => $student)
                    <tr>
                        <td class="px-4 py-2 text-center text-gray-700 border border-gray-200 dark:border-gray-600 dark:text-gray-200">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 text-gray-700 border border-gray-200 dark:border-gray-600 dark:text-gray-200">{{ $student->student->name }}</td>

                            @foreach($resultSections->resultDetails as $detail)
                                <td class="px-4 py-2 border border-gray-200 dark:border-gray-600">
                                    @if ($detail->calc_pattern == 'total')
                                    <input
                                        type="number"
                                        wire:model="studentValues.{{ $student->id }}.{{ $detail->id }}"
                                        class="w-full px-2 py-1 text-gray-800 bg-gray-100 border border-gray-300 rounded dark:bg-gray-800 dark:text-gray-300"
                                        readonly
                                    />
                                    @elseif($detail->calc_pattern == 'grade_level')
                                    <input
                                        type="text"
                                        wire:model="studentValues.{{ $student->id }}.{{ $detail->id }}"
                                        class="w-full px-2 py-1 text-gray-700 bg-gray-100 border border-gray-300 rounded dark:bg-gray-800 dark:text-gray-300"
                                        readonly
                                    />
                                    @else
                                        <input
                                            type="{{ $detail->type == 'numeric' ? 'number' : 'text' }}"
                                            wire:model.lazy="studentValues.{{ $student->id }}.{{ $detail->id }}"
                                            class="w-full px-2 py-1 text-gray-700 border border-gray-300 rounded bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                                            placeholder="Value for {{ $detail->name }}"
                                            {{ $detail->type == 'numeric' ? 'max='.$detail->score_weight : '' }}
                                            {{-- wire:change="calculateTotal({{$student->id}})" --}}
                                          oninput="{{ $detail->type == 'numeric' ? 'this.value = Math.min(this.value, this.max)' : '' }}"
                                            pattern="{{ $detail->type == 'text' ? '[A-Za-z0-9 ]*' : '' }}"
                                            {{ $detail->calc_pattern != 'input' ? 'disabled' : '' }}
                                        />
                                    @endif
                                </td>
                            @endforeach

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Save Button -->
    <div class="flex justify-end">
        <button
            wire:click="saveResults"
            class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
        >
        <span wire:loading.remove>Save Results</span>
        <span wire:loading class="flex items-center">
            <svg
                class="w-5 h-5 mr-2 text-white animate-spin"
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
    </div>
</div>
