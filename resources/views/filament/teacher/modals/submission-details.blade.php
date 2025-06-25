<div class="space-y-6">
    <div class="flex items-center space-x-4">
        <img class="h-16 w-16 rounded-full"
             src="{{ $student->avatar ?: 'https://ui-avatars.com/api/?name=' . urlencode($student->name) }}"
             alt="{{ $student->name }}">
        <div>
            <h3 class="text-lg font-medium">{{ $student->name }}</h3>
            <p class="text-sm text-gray-500">{{ $student->registration_number }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                {{ $submission->status === 'submitted' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                {{ ucfirst($submission->status) }}
            </span>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Submitted At</label>
            <p class="text-sm text-gray-900">{{ $submission->updated_at->format('M j, Y g:i A') }}</p>
        </div>
    </div>

    @if($submission->answer)
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Answer</label>
        <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-sm text-gray-900">{{ $submission->answer }}</p>
        </div>
    </div>
    @endif

    @if($submission->file)
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Attached File</label>
        <div class="flex items-center space-x-2">
            <x-heroicon-o-paper-clip class="h-5 w-5 text-gray-400" />
            <a href="{{ Storage::disk('s3')->url($submission->file) }}"
               target="_blank"
               class="text-blue-600 hover:text-blue-500">
                Download File
            </a>
        </div>
    </div>
    @endif

    @if($submission->total_score)
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Score</label>
            <p class="text-lg font-semibold text-gray-900">
                {{ $submission->total_score }}/{{ $assignment->weight_mark }}
            </p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Percentage</label>
            <p class="text-lg font-semibold text-gray-900">
                {{ round(($submission->total_score / $assignment->weight_mark) * 100, 1) }}%
            </p>
        </div>
    </div>
    @endif

    @if($submission->comments_score)
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Teacher Comments</label>
        <div class="bg-blue-50 p-4 rounded-lg">
            <p class="text-sm text-gray-900">{{ $submission->comments_score }}</p>
        </div>
    </div>
    @endif
</div>
