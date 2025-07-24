<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center shadow-sm">
        <div class="bg-blue-200 text-blue-800 rounded-full p-2 mr-3">
            <x-heroicon-o-users class="w-6 h-6" />
        </div>
        <div>
            <div class="text-xs text-blue-700">Total Students</div>
            <div class="text-xl font-bold">{{ $totalStudents }}</div>
        </div>
    </div>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-center shadow-sm">
        <div class="bg-green-200 text-green-800 rounded-full p-2 mr-3">
            <x-heroicon-o-academic-cap class="w-6 h-6" />
        </div>
        <div>
            <div class="text-xs text-green-700">Average Mark</div>
            <div class="text-xl font-bold">{{ number_format($averageMark, 2) }}</div>
        </div>
    </div>
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 flex items-center shadow-sm">
        <div class="bg-yellow-200 text-yellow-800 rounded-full p-2 mr-3">
            <x-heroicon-o-arrow-up class="w-6 h-6" />
        </div>
        <div>
            <div class="text-xs text-yellow-700">Highest Mark</div>
            <div class="text-xl font-bold">{{ $highestMark }}</div>
        </div>
    </div>
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex items-center shadow-sm">
        <div class="bg-red-200 text-red-800 rounded-full p-2 mr-3">
            <x-heroicon-o-arrow-down class="w-6 h-6" />
        </div>
        <div>
            <div class="text-xs text-red-700">Lowest Mark</div>
            <div class="text-xl font-bold">{{ $lowestMark }}</div>
        </div>
    </div>
</div>
