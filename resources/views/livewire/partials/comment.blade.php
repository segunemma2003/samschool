<div class="bg-white rounded-lg shadow p-4 @if($depth > 0) ml-{{ $depth * 4 }} @endif">
    <div class="flex justify-between items-start">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-gray-900">{{ $comment->user->name }}</span>
            <span class="text-gray-500 text-sm">{{ $comment->created_at->diffForHumans() }}</span>
        </div>

        @if($comment->user_id == auth()->id())
            <div class="flex space-x-2">
                <button
                    wire:click="startEdit({{ json_encode($comment->only(['id', 'content'])) }}"
                    class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                    Edit
                </button>
                <button
                    wire:click="deleteComment({{ $comment->id }})"
                    class="text-red-600 hover:text-red-900 text-sm font-medium">
                    Delete
                </button>
            </div>
        @endif
    </div>

    @if($editComment == $comment->id)
        <form wire:submit.prevent="updateComment" class="mt-2">
            <textarea
                wire:model="editContent"
                rows="3"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            <div class="flex justify-end mt-2 space-x-2">
                <button
                    type="button"
                    wire:click="$set('editComment', null)"
                    class="px-3 py-1 border border-gray-300 text-sm rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </button>
                <button
                    type="submit"
                    class="px-3 py-1 border border-transparent text-sm rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    Update
                </button>
            </div>
        </form>
    @else
        <div class="mt-2 text-gray-700 prose max-w-none">
            {!! $comment->content !!}
        </div>

        <div class="mt-2 flex space-x-4">
            <button
                wire:click="startReply({{ $comment->id }})"
                class="text-sm text-indigo-600 hover:text-indigo-900 font-medium flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
                Reply
            </button>

            @if($comment->replies_count > 0)
                <button
                    wire:click="toggleReplies({{ $comment->id }})"
                    class="text-sm text-gray-600 hover:text-gray-900 font-medium flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                    {{ isset($showReplies[$comment->id]) ? 'Hide' : 'Show' }} Replies ({{ $comment->replies_count }})
                </button>
            @endif
        </div>
    @endif

    <!-- Replies -->
    @if($comment->replies_count > 0 && isset($showReplies[$comment->id]))
        <div class="mt-4 space-y-4 border-l-2 border-gray-200 pl-4">
            @foreach($comment->replies as $reply)
                @include('livewire.partials.comment', ['comment' => $reply, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
