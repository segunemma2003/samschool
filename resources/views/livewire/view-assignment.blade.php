<div>

    {{ $this->assignmentInfolist}}
    <br />
    <form wire:submit="create">

        {{ $this->form }}

        <div class="flex flex-col space-y-3">
        <div wire:loading wire:target="create" class="mt-2 text-sm text-gray-500">
            Processing...
        </div>
        <button type="submit"
        wire:loading.attr="disabled"
        wire:target="create"
        class="px-6 py-2 mt-4 font-semibold text-white transition-colors duration-300 bg-blue-600 rounded-lg dark:bg-gray-800 dark:text-gray-100 hover:bg-blue-500 dark:hover:bg-gray-700">
        Submit
    </button>
        </div>
    </form>

    <x-filament-actions::modals />
</div>
