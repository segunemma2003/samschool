<div>
    {{ $this->assignmentInfolist }}
    <br />
    <form wire:submit.prevent="create">
        {{ $this->form }}

        <button type="submit"
            wire:loading.attr="disabled"
            class="px-6 py-2 mt-4 font-semibold text-white transition-colors duration-300 bg-blue-600 rounded-lg dark:bg-gray-800 dark:text-gray-100 hover:bg-blue-500 dark:hover:bg-gray-700">

            <!-- Loading Spinner -->
            <span wire:loading wire:target="create">
                <svg class="w-5 h-5 animate-spin mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16M12 4v16" />
                </svg>
                Processing...
            </span>

            <!-- Button Text -->
            <span wire:loading.remove wire:target="create">
                Submit
            </span>
        </button>
    </form>
    <x-filament-actions::modals />
</div>
