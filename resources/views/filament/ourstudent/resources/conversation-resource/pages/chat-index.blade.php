<x-filament::page>
    <div class="flex h-screen-filament bg-white rounded-lg shadow overflow-hidden">
        <!-- Sidebar with conversations -->
        <div class="w-1/3 border-r dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
            <div class="h-full flex flex-col">
                <div class="px-4 py-3 border-b dark:border-gray-700 flex justify-between items-center">
                    <h2 class="text-lg font-semibold dark:text-white">Messages</h2>
                    <div>
                        <x-filament::button
                            id="new-message-btn"
                            icon="heroicon-o-plus"
                            size="sm"
                            x-data="{}"
                            x-on:click="$dispatch('open-modal', { id: 'new-conversation-modal' })"
                        >
                            New
                        </x-filament::button>
                    </div>
                </div>

                <div class="px-4 py-2 border-b dark:border-gray-700">
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="search"
                            placeholder="Search conversations"
                            wire:model.debounce.500ms="searchTerm"
                        />
                    </x-filament::input.wrapper>
                </div>

                <div class="overflow-y-auto flex-1">
                    @if(count($conversations) > 0)
                        @foreach($conversations as $conversation)
                            <div
                                wire:click="openConversation({{ $conversation['id'] }})"
                                class="flex items-center px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer {{ $currentConversationId == $conversation['id'] ? 'bg-blue-50 dark:bg-gray-700' : '' }}"
                            >
                                <div class="flex-shrink-0">
                                    @if(isset($conversation['user']['avatar']))
                                        <img src="{{ $conversation['user']['avatar'] }}" class="h-10 w-10 rounded-full">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                            <span class="text-lg text-gray-600 dark:text-gray-300">
                                                {{ substr($conversation['user']['name'] ?? 'U', 0, 1) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <div class="ml-3 flex-1">
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $conversation['user']['name'] ?? 'Unknown User' }}
                                        </div>
                                        @if(isset($conversation['last_message']))
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $conversation['last_message']['created_at'] }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <div class="text-sm text-gray-500 dark:text-gray-400 truncate w-32">
                                            @if(isset($conversation['last_message']))
                                                @if($conversation['last_message']['is_mine'])
                                                    <span class="text-xs text-gray-400">You: </span>
                                                @endif
                                                {{ $conversation['last_message']['body'] }}
                                            @else
                                                <span class="italic">No messages</span>
                                            @endif
                                        </div>

                                        @if($conversation['unread_count'] > 0)
                                            <div class="text-xs bg-blue-500 text-white rounded-full w-5 h-5 flex items-center justify-center">
                                                {{ $conversation['unread_count'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            No conversations yet
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Chat window -->
        <div class="w-2/3 flex flex-col">
            @if($currentConversation)
                <div class="border-b dark:border-gray-700 px-4 py-3 flex items-center">
                    <div class="flex-shrink-0">
                        @php
                            $otherUser = $currentConversation->users->where('id', '!=', auth()->id())->first();
                        @endphp

                        @if($otherUser && isset($otherUser->profile_photo_url))
                            <img src="{{ $otherUser->profile_photo_url }}" class="h-10 w-10 rounded-full">
                        @else
                            <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                <span class="text-lg text-gray-600 dark:text-gray-300">
                                    {{ $otherUser ? substr($otherUser->name, 0, 1) : 'U' }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="ml-3">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $otherUser ? $otherUser->name : 'Unknown User' }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            @if(count($typingUsers) > 0)
                                <span class="text-blue-500">Typing...</span>
                            @endif
                        </div>
                    </div>

                    <div class="ml-auto">
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="search"
                                placeholder="Search messages"
                                wire:model.debounce.500ms="searchTerm"
                                wire:keydown.enter="searchMessages"
                            />
                        </x-filament::input.wrapper>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-4" id="messages-container">
                    @php
                        $messages = $currentConversation->messages()->with('sender')->orderBy('created_at')->get();
                        $currentUser = auth()->user();
                    @endphp

                    @foreach($messages as $message)
                        <div class="mb-4 flex {{ $message->sender_id == $currentUser->id ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-3/4 {{ $message->sender_id == $currentUser->id ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 dark:text-white' }} rounded-lg px-4 py-2 shadow">
                                @if($message->attachment)
                                    <div class="mb-2">
                                        @php
                                            $fileUrl = Storage::url($message->attachment);
                                            $fileType = $message->attachment_type;
                                        @endphp

                                        @if(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
                                            <img src="{{ $fileUrl }}" class="max-w-full rounded" style="max-height: 200px;">
                                        @elseif(in_array($fileType, ['mp4', 'mov', 'avi']))
                                            <video controls class="max-w-full rounded" style="max-height: 200px;">
                                                <source src="{{ $fileUrl }}" type="video/{{ $fileType }}">
                                            </video>
                                        @else
                                            <div class="flex items-center p-2 bg-gray-200 dark:bg-gray-600 rounded">
                                                <svg class="w-6 h-6 text-gray-500 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <a href="{{ $fileUrl }}" target="_blank" class="ml-2 text-blue-500 dark:text-blue-300 underline">
                                                    Download {{ strtoupper($fileType) }} file
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @if($message->body)
                                    <div>{{ $message->body }}</div>
                                @endif

                                <div class="text-xs mt-1 {{ $message->sender_id == $currentUser->id ? 'text-blue-200' : 'text-gray-500 dark:text-gray-400' }} flex items-center justify-end">
                                    <span>{{ $message->created_at->format('g:i A') }}</span>

                                    @if($message->sender_id == $currentUser->id)
                                        <span class="ml-1">
                                            @if($message->is_read)
                                                <!-- Read icon -->
                                                <svg class="w-4 h-4 text-blue-200" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" />
                                                </svg>
                                            @else
                                                <!-- Sent icon -->
                                                <svg class="w-4 h-4 text-blue-200" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M4.01 6.03l7.51 3.22-7.52-1 .01-2.22m7.5 8.72L4 17.97v-2.22l7.51-1M2.01 3L2 10l15 2-15 2 .01 7L23 12 2.01 3z" />
                                                </svg>
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t dark:border-gray-700 p-4">
                    <form wire:submit.prevent="sendMessage" class="flex items-end">
                        <div class="flex-1 mr-3">
                            <div class="relative">
                                <x-filament::input.wrapper>
                                    <x-filament::input
                                        type="text"
                                        placeholder="Type your message..."
                                        wire:model="message"
                                        wire:keydown.enter.prevent="sendMessage"
                                    />
                                </x-filament::input.wrapper>

                                <div class="absolute right-2 bottom-2 flex space-x-2">
                                    <input
                                        type="file"
                                        id="file-upload"
                                        wire:model="attachment"
                                        class="hidden"
                                    />
                                    <label for="file-upload" class="cursor-pointer text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                    </label>

                                    <button type="button" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" id="emoji-button">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            @if($attachment)
                                <div class="mt-2 p-2 bg-gray-100 dark:bg-gray-700 rounded flex items-center">
                                    <span class="text-sm text-gray-700 dark:text-gray-300 truncate flex-1">
                                        {{ $attachment->getClientOriginalName() }}
                                    </span>
                                    <button
                                        type="button"
                                        wire:click="$set('attachment', null)"
                                        class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 ml-2"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <x-filament::button type="submit" icon="heroicon-o-paper-airplane">
                            Send
                        </x-filament::button>
                    </form>
                </div>
            @else
                <div class="flex-1 flex items-center justify-center text-gray-500 dark:text-gray-400">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <p class="mt-2 text-sm">Select a conversation or start a new one</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- New Conversation Modal -->
    <x-filament::modal id="new-conversation-modal" width="md">
        <x-slot name="heading">New Message</x-slot>

        <div class="p-4">
            <div class="mb-4">
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="search"
                        placeholder="Search users"
                        wire:model.debounce.500ms="userSearchTerm"
                    />
                </x-filament::input.wrapper>
            </div>

            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($users as $user)
                    <div
                        wire:click="startNewConversation({{ $user['id'] }})"
                        class="flex items-center px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer rounded-lg"
                    >
                        <div class="flex-shrink-0">
                            @if(isset($user['avatar']))
                                <img src="{{ $user['avatar'] }}" class="h-8 w-8 rounded-full">
                            @else
                                <div class="h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                    <span class="text-lg text-gray-600 dark:text-gray-300">
                                        {{ substr($user['name'] ?? 'U', 0, 1) }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $user['name'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <x-slot name="footer">
            <x-filament::button
                x-on:click="$dispatch('close-modal', { id: 'new-conversation-modal' })"
            >
                Cancel
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    <!-- Search Results Modal -->
    <x-filament::modal id="search-results-modal" width="lg">
        <x-slot name="heading">Search Results</x-slot>

        <div class="p-4" id="search-results-container">
            <!-- Results will be loaded here via JS -->
        </div>

        <x-slot name="footer">
            <x-filament::button
                x-on:click="$dispatch('close-modal', { id: 'search-results-modal' })"
            >
                Close
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@3.1.1/dist/index.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Scroll to bottom of messages container
                const scrollToBottom = () => {
                    const container = document.getElementById('messages-container');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                };

                // Scroll on load
                scrollToBottom();

                // Scroll when new messages arrive
                Livewire.on('messageAdded', scrollToBottom);

                // Emoji picker
                const button = document.getElementById('emoji-button');
                if (button) {
                    const picker = new EmojiButton();

                    picker.on('emoji', emoji => {
                        const input = document.querySelector('input[wire\\:model="message"]');
                        input.value += emoji;
                        input.dispatchEvent(new Event('input'));
                    });

                    button.addEventListener('click', () => {
                        picker.togglePicker(button);
                    });
                }

                // Typing indicator
                window.addEventListener('stopTypingBroadcast', event => {
                    setTimeout(() => {
                        @this.call('$emit', 'echo:chat,UserTyping', {
                            user_id: {{ auth()->id() }},
                            conversation_id: event.detail.conversationId,
                            is_typing: false
                        });
                    }, 2000);
                });

                // Search results
                window.addEventListener('showSearchResults', event => {
                    const results = event.detail.results;
                    const container = document.getElementById('search-results-container');
                    let html = '';

                    if (results.length === 0) {
                        html = '<div class="text-center text-gray-500 dark:text-gray-400 py-8">No results found</div>';
                    } else {
                        results.forEach(message => {
                            const isCurrentUser = message.sender_id === {{ auth()->id() }};
                            const date = new Date(message.created_at);
                            const formattedDate = date.toLocaleString();

                            html += `
                                <div class="mb-4 p-3 border rounded-lg ${isCurrentUser ? 'border-blue-200 bg-blue-50 dark:border-blue-900 dark:bg-blue-900/20' : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50'}">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="font-medium ${isCurrentUser ? 'text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300'}">
                                            ${isCurrentUser ? 'You' : message.sender.name}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            ${formattedDate}
                                        </div>
                                    </div>
                                    <div class="${isCurrentUser ? 'text-blue-900 dark:text-blue-100' : 'text-gray-900 dark:text-gray-100'}">
                                        ${message.body}
                                    </div>
                                </div>
                            `;
                        });
                    }

                    container.innerHTML = html;
                    window.dispatchEvent(new Event('open-modal', { detail: { id: 'search-results-modal' }}));
                });
            });
        </script>
    @endpush
</x-filament::page>
