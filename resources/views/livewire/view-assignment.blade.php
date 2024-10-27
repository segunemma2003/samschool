<div>

    {{ $this->assignmentInfolist}}
    <br />
    <form wire:submit="create">
        {{ $this->form }}

        <button type="submit"
        class="px-6 py-2 mt-4 font-semibold text-white transition-colors duration-300 bg-blue-600 rounded-lg dark:bg-gray-800 dark:text-gray-100 hover:bg-blue-500 dark:hover:bg-gray-700">
        Submit
    </button>
    </form>

    <x-filament-actions::modals />
</div>
