<x-filament-panels::page>

    <form wire:submit.prevent="saveRecord">
        <div class="space-y-6">
            <!-- Question Text -->
            <div>
                <label for="question" class="block text-gray-700 dark:text-gray-300">Question</label>
                <textarea
                    id="question"
                    wire:model.defer="record.question"
                    rows="3"
                    class="form-input w-full bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-700 rounded-md"
                ></textarea>
            </div>

            <!-- Question Type -->
            <div>
                <label for="type" class="block text-gray-700 dark:text-gray-300">Type</label>
                <select
                    id="type"
                    wire:model="record.type"
                    class="form-select w-full bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-700 rounded-md"
                >
                    <option value="multiple_choice">Multiple Choice</option>
                    <option value="true_false">True/False</option>
                    <option value="open_ended">Open-Ended</option>
                </select>
            </div>

            <!-- Options (Shown if Type is Multiple Choice) -->
            @if ($record->type === 'multiple_choice')
                <div>
                    <label for="options" class="block text-gray-700 dark:text-gray-300">Options</label>
                    <textarea
                        id="options"
                        wire:model.defer="record.options"
                        class="form-input w-full bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-700 rounded-md"
                    ></textarea>
                </div>
            @endif

            <!-- Answer (Shown if Type is Open-Ended) -->
            @if ($record->type === 'open_ended')
                <div>
                    <label for="answer" class="block text-gray-700 dark:text-gray-300">Answer</label>
                    <textarea
                        id="answer"
                        wire:model.defer="record.answer"
                        class="form-input w-full bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-700 rounded-md"
                    ></textarea>
                </div>
            @endif

            <!-- Correct Answer -->
            <div>
                <label for="correct_answer" class="block text-gray-700 dark:text-gray-300">Correct Answer</label>
                <input
                    type="text"
                    id="correct_answer"
                    wire:model.defer="record.correct_answer"
                    class="form-input w-full bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-700 rounded-md"
                />
            </div>

            <!-- Mark -->
            <div>
                <label for="mark" class="block text-gray-700 dark:text-gray-300">Mark</label>
                <input
                    type="number"
                    id="mark"
                    wire:model.defer="record.mark"
                    class="form-input w-full bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-700 rounded-md"
                />
            </div>

            <!-- Hint -->
            <div>
                <label for="hint" class="block text-gray-700 dark:text-gray-300">Hint</label>
                <textarea
                    id="hint"
                    wire:model.defer="record.hint"
                    class="form-input w-full bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-700 rounded-md"
                ></textarea>
            </div>

            <!-- Save Button -->
            <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white dark:bg-blue-500 rounded-md hover:bg-blue-700 dark:hover:bg-blue-600 focus:outline-none focus:ring focus:ring-blue-200 dark:focus:ring-blue-700"
            >
                Save Changes
            </button>
        </div>
    </form>
</x-filament-panels::page>
