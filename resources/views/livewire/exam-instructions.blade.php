<div class="flex flex-col items-center justify-center min-h-screen p-4 bg-gray-100 dark:bg-gray-900">
    <!-- Instructions Section -->
    <div class="w-full max-w-4xl p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <p class="mb-4 text-lg font-semibold text-gray-800 dark:text-gray-200">
            {{ $introduction }}
        </p>

        <ul class="space-y-3 text-gray-700 dark:text-gray-200">
            @foreach ($instructions as $number => $instruction)
                <li>{{ $number + 1 }}. {{ $instruction }}</li>
            @endforeach
        </ul>

        <!-- Centered Start Button -->
        <div class="flex justify-center mt-6">
            <button wire:click="startExam" class="px-6 py-2 text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                Start Exam
            </button>
        </div>

        <!-- Good Luck Message -->
        <div class="mt-4 text-lg font-semibold text-center text-gray-800 dark:text-gray-200">
            Good Luck!
        </div>
    </div>

    <!-- Modal for Confirming Start Exam -->
    @if ($showModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="max-w-md p-6 mx-auto bg-white rounded-lg shadow-lg dark:bg-gray-800">
                <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">Are you sure you want to start the exam?</p>
                <div class="flex justify-end gap-3 mt-6">
                    <button wire:click="cancelStart" class="px-4 py-2 mr-3 text-gray-500 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        No
                    </button>
                    <button wire:click="confirmStart" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                        Yes
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
