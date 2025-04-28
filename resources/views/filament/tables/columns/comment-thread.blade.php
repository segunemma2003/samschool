@php
    $depth = $this->getCommentDepth($record);
    $indentClass = match(min($depth, 6)) {
        1 => 'ml-4',
        2 => 'ml-8',
        3 => 'ml-12',
        4 => 'ml-16',
        5 => 'ml-20',
        6 => 'ml-24',
        default => ''
    };
@endphp

<div class="comment-thread {{ $indentClass }} p-3 rounded-lg hover:bg-gray-50 transition-colors">
    <div class="flex justify-between items-start gap-2">
        <div class="flex items-center gap-2">
            <span class="font-medium text-gray-900">
                {{ $record->user->name }}
            </span>
            <span class="text-xs text-gray-500">
                {{ $record->created_at->diffForHumans() }}
            </span>
        </div>

        @if($depth > 0)
            <span class="text-xs text-gray-400">
                Reply
            </span>
        @endif
    </div>

    <div class="prose max-w-none mt-1 pl-2 border-l-2 border-gray-100 hover:border-indigo-200">
        {!! $record->content !!}
    </div>

    @if($record->replies_count > 0)
        <div class="mt-2 text-xs text-gray-500 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            {{ $record->replies_count }} {{ Str::plural('reply', $record->replies_count) }}
        </div>
    @endif
</div>

<style>
    .comment-thread {
        transition: all 0.2s ease;
    }
    .comment-thread:hover {
        background-color: rgba(243, 244, 246, 0.5);
    }
</style>
