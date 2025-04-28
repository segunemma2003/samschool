<div class="space-y-6">
    <!-- Comment Form -->
    <div class="bg-white rounded-lg shadow p-4">
        <form wire:submit.prevent="postComment">
            <div class="mb-3">
                <label for="newComment" class="sr-only">Your Comment</label>
                <textarea
                    wire:model="newComment"
                    id="newComment"
                    rows="3"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Write your comment..."></textarea>
                @error('newComment') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="flex justify-end">
                @if($replyTo)
                    <button
                        type="button"
                        wire:click="$set('replyTo', null)"
                        class="mr-2 inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel Reply
                    </button>
                @endif
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ $replyTo ? 'Post Reply' : 'Post Comment' }}
                </button>
            </div>
        </form>
    </div>

    <!-- Comments Thread -->
    <div class="space-y-4">
        @foreach($comments as $comment)
            @include('livewire.partials.comment', ['comment' => $comment, 'depth' => 0])
        @endforeach
    </div>
</div>
