<div class="filament-page">
    <h1 class="mb-4 text-2xl font-bold">Create Course Form</h1>
    <h4 class="mb-4 text-xl font-medium">Name: {{$student->name}}</h4>
    <form wire:submit.prevent="create" class="space-y-6">
        {{-- Filter Section --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="class" class="block font-medium">Class</label>
                <select wire:model.live="classId" id="class" name="class_id" required
                    class="block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-800">
                    <option value="">Select Class</option>
                    @foreach($subjects->groupBy('class_id') as $classId => $group)
                        <option value="{{ $classId }}">Class {{ $classId }}</option>
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

        <div class="mb-2">
            <input type="checkbox" wire:model.live="selectAll" id="selectAll">
            <label for="selectAll" class="font-medium">Select All</label>
        </div>

        {{-- Subjects Table --}}
        <div>
            <h2 class="mb-2 text-lg font-semibold">Subjects</h2>
            <table class="w-full border border-gray-300 table-auto dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="px-4 py-2 border border-gray-300 dark:border-gray-700">Subject</th>
                        <th class="px-4 py-2 border border-gray-300 dark:border-gray-700">Teacher</th>
                        <th class="px-4 py-2 border border-gray-300 dark:border-gray-700">Select</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subject)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">{{ $subject->subjectDepot->name }}</td>
                            <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">
                                {{ $subject->teacher->name ?? 'N/A' }}
                            </td>
                            {{-- <td class="px-4 py-2 text-center border border-gray-300 dark:border-gray-700">
                                <input type="checkbox" wire:model="selectedSubjects" value="{{ $subject->id }}">
                            </td> --}}

                            <td class="px-4 py-2 text-center border border-gray-300 dark:border-gray-700">
                                <input type="checkbox" wire:model="selectedSubjects" value="{{ $subject->id }}"
                                @if(in_array($subject->id, $selectedSubjects)) checked @endif>
                                @if(in_array($subject->id, $selectedSubjects))
                                    <span class="font-semibold text-green-500">Selected</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-center border border-gray-300 dark:border-gray-700">
                                No subjects available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Submit Button --}}
        <div class="flex justify-end">
            <button type="submit"
                class="relative px-6 py-2 font-semibold text-white transition-colors duration-200 bg-blue-600 border-2 border-blue-600 rounded-md hover:bg-blue-700 hover:border-blue-700 dark:bg-blue-500 dark:text-white dark:border-blue-500 dark:hover:bg-blue-600 dark:hover:border-blue-600"
                wire:target="create"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="create">Create</span>
                <span wire:loading wire:target="create" class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </span>
            </button>
        </div>
    </form>
</div>
