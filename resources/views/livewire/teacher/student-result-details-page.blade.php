@if(isset($errorMessage) && $errorMessage)
    <div class="p-4 my-6 bg-red-100 border border-red-300 text-red-800 rounded-lg shadow">
        <strong>Error:</strong> {{ $errorMessage }}
    </div>
@else
<div class="flex flex-col gap-4 space-y-5">
    <!-- Student Name Section -->
    <div class="flex items-center justify-between p-4 bg-gray-100 rounded-lg shadow dark:bg-gray-800">
        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">
            Student: {{ $student->name ?? 'N/A' }}
        </h2>
    </div>

    <!-- Table Section -->
    <div>
        {{ $this->table }}
    </div>

    <!-- Form Section -->
    <div>
        <form wire:submit.prevent="saveComment">
            {{ $this->form }}
            <br />
            <button
            type="submit"
            class="relative flex items-center justify-center px-6 py-2 font-medium text-white transition bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:outline-none dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-400"
            wire:loading.attr="disabled"
            wire:target="saveComment"
        >
            <!-- Loading Spinner and Text -->
            <span
                wire:loading
                wire:target="saveComment"
                class="flex items-center space-x-2"
            >

                <span class="text-white">Loading...</span>
            </span>

            <!-- Submit Text (Visible when not loading) -->
            <span wire:loading.remove wire:target="saveComment">Submit</span>
        </button>


        </form>

        <x-filament-actions::modals />
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('refreshPage', () => {
            window.location.reload();
        });
    });
</script>
@endif
