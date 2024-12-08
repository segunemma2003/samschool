<div class="min-h-screen flex flex-col p-6">
    {{-- <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Psychomotor Details for Student</h3> --}}

    <!-- Loading indicator -->
    <div wire:loading class="flex justify-center items-center mb-6">
        <div class="animate-spin rounded-full h-8 w-8 border-t-4 border-blue-500 dark:border-blue-300"></div>
        <span class="ml-2 text-blue-500 dark:text-blue-300">Loading...</span>
    </div>

    <!-- Form -->
    <div wire:loading.remove>
        <form wire:submit.prevent="save" class="space-y-6 flex-grow">
            <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-gray-800 flex flex-col">
                <table class="min-w-full table-auto divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-8 py-4 text-lg font-medium text-gray-500 uppercase dark:text-gray-400">
                                #
                            </th>
                            <th scope="col" class="px-8 py-4 text-lg font-medium text-gray-500 uppercase dark:text-gray-400">
                                Psychomotor
                            </th>
                            <th scope="col" class="px-8 py-4 text-lg font-medium text-gray-500 uppercase dark:text-gray-400">
                                Rating
                            </th>
                            <th scope="col" class="px-8 py-4 text-lg font-medium text-gray-500 uppercase dark:text-gray-400">
                                Comment
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @foreach ($psychomotors as $index => $psychomotor)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $index + 1 }} <!-- Numbering -->
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-200" style="width: 60%;">
                                    {{ $psychomotor->skill }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400" style="width: 20%;">
                                    <select wire:model="ratings.{{ $psychomotor->id }}.rating" class="w-full p-2 rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="" disabled selected>Select a rating</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400" style="width: 20%;">
                                    <textarea wire:model="ratings.{{ $psychomotor->id }}.comment" rows="2" class="w-full p-2 rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Add a comment..."></textarea>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Save Button -->
            <div class="mt-6 flex justify-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500" wire:loading.attr="disabled">
                    <span wire:loading.remove>Save</span>
                    <span wire:loading>Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>
