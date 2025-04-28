@foreach($replies as $reply)
<div class="border-l-2 border-gray-200 pl-4 mb-4">
    <div class="flex justify-between items-start">
        <div class="flex-1">
            <div class="font-medium">{{ $reply->user->name }}</div>
            <div class="text-sm text-gray-500 mb-2">{{ $reply->created_at->format('M j, Y g:i a') }}</div>
            <div class="prose max-w-none">{!! $reply->content !!}</div>
        </div>
        <div class="flex space-x-2">
            @if($reply->user_id == auth()->id())
                <button wire:click="$dispatch('open-modal', { component: 'filament.resources.communication-book.resource.relation-managers.comments.edit', arguments: { record: {{ $reply->id }} } })"
                    class="text-gray-500 hover:text-gray-700">
                    <x-heroicon-o-pencil class="h-4 w-4"/>
                </button>
                <button wire:click="mountTableAction('delete', {{ $reply->id }})"
                    class="text-gray-500 hover:text-gray-700">
                    <x-heroicon-o-trash class="h-4 w-4"/>
                </button>
            @endif
        </div>
    </div>

    @if($reply->replies_count)
        <button wire:click="$dispatch('open-modal', {
            component: 'filament.resources.communication-book.resource.relation-managers.comments.view-replies',
            arguments: {
                record: {{ $reply->id }},
                level: {{ $level + 1 }}
            }
        })"
        class="mt-2 flex items-center text-sm text-primary-600 hover:text-primary-800">
            <x-heroicon-o-chevron-down class="h-4 w-4 mr-1"/>
            Replies ({{ $reply->replies_count }})
        </button>
    @endif

    <div class="mt-2">
        <button wire:click="$dispatch('open-modal', {
            component: 'filament.resources.communication-book.resource.relation-managers.comments.reply',
            arguments: {
                record: {{ $reply->id }},
                level: {{ $level }}
            }
        })"
        class="text-sm text-gray-500 hover:text-gray-700">
            Reply
        </button>
    </div>
</div>
@endforeach
