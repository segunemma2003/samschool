<!-- resources/views/filament/pages/chat-page.blade.php -->

<x-filament::page>
    <div class="flex h-[calc(100vh-10rem)] border rounded-lg shadow">
        <!-- Left sidebar - Conversations -->
        <div class="w-1/3 border-r bg-gray-50 overflow-y-auto">
            <div class="p-4 border-b bg-white">
                <x-filament::input.wrapper>
                    <x-filament::input placeholder="Search conversations..." />
                </x-filament::input.wrapper>
            </div>

            <div class="divide-y">
                @foreach($conversations as $conversation)
                    <div
                        wire:click="$set('selectedConversation', {{ $conversation->id }})"
                        class="p-4 hover:bg-gray-100 cursor-pointer flex items-center {{ $selectedConversation == $conversation->id ? 'bg-blue-50' : '' }}"
                    >
                        <div class="flex-shrink-0 mr-3">
                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                {{ strtoupper(substr($conversation->otherUser->name, 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $conversation->otherUser->name }}
                            </p>
                            <p class="text-sm text-gray-500 truncate">
                                {{ $conversation->latestMessage->content ?? '' }}
                            </p>
                        </div>
                        @if($conversation->unread_count)
                            <span class="ml-2 bg-blue-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                {{ $conversation->unread_count }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right side - Chat area -->
        <div class="flex-1 flex flex-col bg-white">
            @if($selectedConversation)
                <div class="p-4 border-b flex items-center">
                    <div class="flex-shrink-0 mr-3">
                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                            {{ strtoupper(substr($selectedConversation->otherUser->name, 0, 1)) }}
                        </div>
                    </div>
                    <div>
                        <p class="font-medium">{{ $selectedConversation->otherUser->name }}</p>
                        <p class="text-xs text-gray-500">Online</p>
                    </div>
                </div>

                <div class="flex-1 p-4 overflow-y-auto" id="messages-container">
                    @foreach($selectedConversation->messages as $message)
                    <div class="mb-4 {{ $message->user_id == auth()->id() ? 'text-right' : 'text-left' }}">
                        <div class="inline-block max-w-xs px-4 py-2 rounded-lg {{ $message->user_id == auth()->id() ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                            {{ $message->content }}
                        </div>
                        <div class="flex items-center justify-end mt-1 space-x-1">
                            <p class="text-xs text-gray-500">
                                {{ $message->created_at->diffForHumans() }}
                            </p>
                            @if($message->user_id == auth()->id())
                                @if($message->read_at)
                                    <span class="text-xs text-blue-500" title="Read at {{ $message->read_at->format('g:i A') }}">
                                        ✓✓
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400" title="Delivered">
                                        ✓
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="p-4 border-t">
                    @if($otherUserTyping)
                        <div class="px-4 py-2 text-sm text-gray-500 italic">
                            {{ $selectedConversation->otherUser->name }} is typing...
                        </div>
                    @endif
                    <div class="p-4 border-t">
                        @if(!empty($attachments))
                            <div class="mb-2 flex flex-wrap gap-2">
                                @foreach($attachments as $index => $file)
                                    <div class="relative border rounded p-2 bg-gray-50">
                                        <div class="flex items-center">
                                            <span class="mr-2">
                                                @if(str_starts_with($file->getMimeType(), 'image/'))
                                                    <img src="{{ $file->temporaryUrl() }}" class="h-10 w-10 object-cover">
                                                @else
                                                    <x-heroicon-o-document class="h-5 w-5 text-gray-400" />
                                                @endif
                                            </span>
                                            <span class="text-sm truncate max-w-xs">{{ $file->getClientOriginalName() }}</span>
                                            <button
                                                wire:click="removeAttachment({{ $index }})"
                                                class="ml-2 text-red-500 hover:text-red-700"
                                            >
                                                <x-heroicon-o-x-mark class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <form wire:submit.prevent="sendMessage">
                            <div class="flex items-center">
                                <div class="flex items-center space-x-2 mr-2">
                                    <!-- File attachment button -->
                                    <label class="cursor-pointer text-gray-500 hover:text-gray-700">
                                        <x-heroicon-o-paper-clip class="h-5 w-5" />
                                        <input
                                            type="file"
                                            wire:model="attachments"
                                            multiple
                                            class="hidden"
                                        >
                                    </label>
                                    <!-- Emoji picker will go here -->
                                </div>

                                <x-filament::input
                                    wire:model="message"
                                    placeholder="Type a message..."
                                    class="flex-1 mr-2"
                                />
                                <x-filament::button type="submit" icon="heroicon-o-paper-airplane" />
                            </div>
                        </form>
                    </div>

                    <!-- In the message display -->
                    @if($message->type == 'file')
                        <div class="inline-block px-4 py-2 rounded-lg {{ $message->user_id == auth()->id() ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                            @foreach($message->attachments as $attachment)
                                <div class="mb-2 last:mb-0">
                                    <a
                                        href="{{ Storage::url($attachment->path) }}"
                                        target="_blank"
                                        class="flex items-center hover:underline"
                                    >
                                        @if(str_starts_with($attachment->mime_type, 'image/'))
                                            <img
                                                src="{{ Storage::url($attachment->path) }}"
                                                class="h-16 w-16 object-cover mr-2"
                                            >
                                        @else
                                            <x-heroicon-o-document class="h-5 w-5 mr-2" />
                                        @endif
                                        <span>{{ $attachment->name }}</span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                    <form wire:submit.prevent="sendMessage">
                        <div class="flex items-center">
                            <x-filament::input
                                wire:model="message"
                                placeholder="Type a message..."
                                class="flex-1 mr-2"
                            />
                            <x-filament::button type="submit" icon="heroicon-o-paper-airplane" />
                        </div>
                    </form>
                    @endif
                </div>
            @else
                <div class="flex-1 flex items-center justify-center text-gray-500">
                    Select a conversation to start chatting
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            // Auto-scroll to bottom of messages
            function scrollToBottom() {
                const container = document.getElementById('messages-container');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            }

            // Call it initially and after Livewire updates
            document.addEventListener('DOMContentLoaded', scrollToBottom);
            window.addEventListener('message-sent', scrollToBottom);
        </script>
    @endpush
</x-filament::page>
