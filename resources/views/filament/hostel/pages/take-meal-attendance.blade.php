<x-filament::page>
    <div class="space-y-6">
        <x-filament::card>
            <div class="px-4 py-5 sm:px-6 border-b">
                <h3 class="text-lg font-medium leading-6 text-gray-900">
                    Meal Attendance: {{ $this->record->menu_name }} ({{ $this->record->meal_date->format('M d, Y') }} - {{ ucfirst($this->record->meal_type) }})
                </h3>
            </div>

            <div class="px-4 py-5 sm:p-6">
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-500">Menu Description:</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ $this->record->menu_description }}</p>
                </div>

                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Student</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Class</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Room</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Attended</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Special Requirements</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($this->students as $student)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                        {{ $student->name }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $student->class->name }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $student->hostelAssignments->first()->room->room_number ?? '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <input type="checkbox"
                                               wire:model="attendance.{{ $student->id }}"
                                               class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <input type="text"
                                               wire:model="specialRequirements.{{ $student->id }}"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end">
                    <button wire:click="saveAttendance"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Save Attendance
                    </button>
                </div>
            </div>
        </x-filament::card>
    </div>
</x-filament::page>
