<div class="p-4 space-y-6">
    <!-- Upper Form Panel -->
    <div class="p-4 bg-white rounded shadow dark:bg-gray-800">
        {{-- <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Result Sections</h3> --}}
        <form wire:submit.prevent="saveResults" class="space-y-4">
            @foreach($resultSections as $section)
                <div class="space-y-2">
                    <h4 class="font-semibold text-gray-700 text-md dark:text-gray-300">{{ $section->name }}</h4>
                    @foreach($section->resultDetails as $detail)
                        <div class="flex items-center gap-3 space-x-4">
                            <label class="w-1/3 font-medium text-gray-700 dark:text-gray-300">
                                {{ $detail->name }}
                            </label>
                            <input
                                type="text"
                                wire:model.defer="sectionValues.{{ $section->id }}.{{ $detail->id }}"
                                class="w-2/3 px-2 py-1 text-gray-800 border border-gray-300 rounded bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                                placeholder="Enter value for {{ $detail->name }}"
                            />
                        </div>
                    @endforeach
                </div>
            @endforeach

        </form>

        <div class="flex justify-end">
            <button
                {{-- wire:click="" --}}
                class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
            >
                Generate Result
            </button>
        </div>
    </div>

    <!-- Students Table -->
    <div class="p-4 bg-white rounded shadow dark:bg-gray-800">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Student Results</h3>
        <table class="w-full border border-collapse border-gray-200 dark:border-gray-600">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-2 text-gray-800 border border-gray-200 dark:border-gray-600 dark:text-gray-200">No.</th>
                    <th class="px-4 py-2 text-gray-800 border border-gray-200 dark:border-gray-600 dark:text-gray-200">Student</th>
                    @foreach($resultSections as $section)
                        @foreach($section->resultDetails as $detail)
                            <th class="px-4 py-2 text-gray-800 border border-gray-200 dark:border-gray-600 dark:text-gray-200">
                               {{ $detail->name }}
                            </th>
                        @endforeach
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($students as $index => $student)
                    <tr>
                        <td class="px-4 py-2 text-center text-gray-800 border border-gray-200 dark:border-gray-600 dark:text-gray-200">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 text-gray-800 border border-gray-200 dark:border-gray-600 dark:text-gray-200">{{ $student->student->name }}</td>
                        @foreach($resultSections as $section)
                            @foreach($section->resultDetails as $detail)
                                <td class="px-4 py-2 border border-gray-200 dark:border-gray-600">
                                    <input
                                        type="text"
                                        wire:model.defer="studentValues.{{ $student->id }}.{{ $section->id }}.{{ $detail->id }}"
                                        class="w-full px-2 py-1 text-gray-800 border border-gray-300 rounded bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                                        placeholder="Value for {{ $detail->name }}"
                                    />
                                </td>
                            @endforeach
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
            Save Results
        </button>
    </div>
</div>
