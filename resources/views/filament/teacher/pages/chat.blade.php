<!-- resources/views/filament/student/pages/chat.blade.php -->
<x-filament::page>
    <div x-data="{ scrollToBottom() { const c = document.getElementById('messages-container'); if (c) { c.scrollTop = c.scrollHeight; } } }"
         x-init="window.addEventListener('scrollToBottom', () => scrollToBottom());"
         class="flex h-[calc(100vh-200px)] border dark:border-gray-700 rounded-lg shadow-lg bg-white dark:bg-gray-800">
        <!-- Sidebar -->
        <div class="w-1/4 border-r dark:border-gray-700 flex flex-col bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 shadow-md rounded-l-lg">
            <!-- Search -->
            <div class="p-3 border-b dark:border-gray-700 ">
                <div class="relative flex space-x-3">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-6 pointer-events-none ">
                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input
                        wire:model.live.debounce.300ms="searchUsers"
                        type="text"
                        placeholder="Search users..."
                        class="w-full ml-10 pl-13 px-6 py-2 border dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                    >
                </div>
            </div>

            <!-- Users list -->
            <div class="flex-1 overflow-y-auto">
                @if($searchUsers)
                    <div class="p-2 font-semibold text-gray-500 dark:text-gray-400">Search Results</div>
                    @foreach($users->filter(fn($user) => str_contains(strtolower($user->name), strtolower($searchUsers))) as $user)
                        <div
                            wire:click="startNewConversation({{ $user->id }})"
                            class="p-3 border-b dark:border-gray-700 cursor-pointer hover:bg-primary-50 dark:hover:bg-primary-900/30 flex items-center transition-colors duration-150 rounded-lg mx-2 my-1 group"
                        >
                            <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-800 text-primary-700 dark:text-primary-300 flex items-center justify-center mr-3 font-medium">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-2 font-semibold text-gray-500 dark:text-gray-400">Conversations</div>
                    @foreach($conversations as $conversation)
                    <div
                        wire:click="selectConversation({{ $conversation->id }})"
                        class="p-3 border-b dark:border-gray-700 cursor-pointer hover:bg-primary-50 dark:hover:bg-primary-900/30 flex items-center transition-colors duration-150 rounded-lg mx-2 my-1 group {{ $activeConversation && $activeConversation->id == $conversation->id ? 'bg-primary-50 dark:bg-primary-900/20' : '' }}"
                    >
                        <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-800 text-primary-700 dark:text-primary-300 flex items-center justify-center mr-3 font-medium relative">
                            {{ substr($conversation->otherUser->name, 0, 1) }}
                            @if($conversation->otherUser->is_online)
                                <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 border-2 border-white dark:border-gray-800 rounded-full"></span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $conversation->otherUser->name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                @if($conversation->latestMessage)
                                    {{ $conversation->latestMessage->sender_id == auth()->id() ? 'You: ' : '' }}
                                    {{ $conversation->latestMessage->body ?: ($conversation->latestMessage->attachment ? 'Sent an attachment' : '') }}
                                @endif
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                            {{ $conversation->last_message_at ? $conversation->last_message_at->shortRelativeDiffForHumans() : '' }}
                        </div>
                        @if($conversation->latestMessage && $conversation->latestMessage->sender_id != auth()->id() && !$conversation->latestMessage->read_at)
                            <div class="w-2 h-2 rounded-full bg-primary-500 ml-2"></div>
                        @endif
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Chat area -->
        <div class="flex-1 flex flex-col bg-gradient-to-br from-gray-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-800 dark:to-blue-900 shadow-lg rounded-r-lg">
            @if($activeConversation)
                <!-- Chat header -->
                <div class="p-4 border-b dark:border-gray-700 flex items-center bg-white/80 dark:bg-gray-900/80 shadow-sm rounded-tr-lg">
                    @php
                        $otherUser = $activeConversation->users->where('id', '!=', auth()->id())->first();
                    @endphp
                    <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-800 text-primary-700 dark:text-primary-300 flex items-center justify-center mr-3 font-medium relative">
                        {{ substr($otherUser->name, 0, 1) }}
                        @if($otherUser->is_online)
                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 border-2 border-white dark:border-gray-800 rounded-full"></span>
                        @endif
                    </div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $otherUser->name }}</div>
                    @if($isTyping)
                        <div class="ml-3 px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-sm text-gray-600 dark:text-gray-300 animate-pulse">typing...</div>
                    @endif
                </div>

                <!-- Messages -->
                <div
                    class="flex-1 p-4 overflow-y-auto relative"
                    id="messages-container"
                    wire:key="messages-{{ $activeConversation?->id }}"
                >
                @php
                    $lastDate = null;
                @endphp
                @if($loadingMessages)
                <div class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-gray-800/50 z-10">
                    <x-filament::loading-indicator class="w-8 h-8" />
                </div>
                @endif
                @foreach($activeConversation->messages as $message)
                    @php
                        $msgDate = $message->created_at->format('Y-m-d');
                    @endphp
                    @if($lastDate !== $msgDate)
                        <div class="flex justify-center my-4">
                            <span class="px-4 py-1 rounded-full bg-gray-200 dark:bg-gray-700 text-xs text-gray-600 dark:text-gray-300 shadow">{{ $message->created_at->format('M d, Y') }}</span>
                        </div>
                        @php $lastDate = $msgDate; @endphp
                    @endif
                    <div class="group flex mb-4 {{ $message->sender_id == auth()->id() ? 'justify-end' : 'justify-start' }} animate-fade-in">
                        @if($message->sender_id != auth()->id())
                            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-800 text-primary-700 dark:text-primary-300 flex items-center justify-center mr-2 self-end">
                                {{ substr($otherUser->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="relative max-w-xs lg:max-w-md">
                            <div class="{{ $message->sender_id == auth()->id()
                                ? 'bg-primary-500 text-white rounded-br-2xl rounded-tl-2xl rounded-bl-md'
                                : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-bl-2xl rounded-tr-2xl rounded-br-md' }}
                                p-3 shadow-sm transition-all duration-200">
                                @if($message->attachment)
                                    <div class="mb-2">
                                        @if($message->is_image)
                                            <img src="{{ $message->attachment_url }}" class="max-w-full h-auto rounded" alt="Attachment">
                                        @else
                                            <a href="{{ $message->attachment_url }}"
                                            download
                                            class="flex items-center {{ $message->sender_id == auth()->id() ? 'text-white hover:text-blue-100' : 'text-primary-500 dark:text-primary-400 hover:underline' }}">
                                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                                </svg>
                                                Download File
                                            </a>
                                        @endif
                                    </div>
                                @endif
                                <div>{{ $message->body }}</div>
                                <div class="text-xs mt-1 flex items-center {{ $message->sender_id == auth()->id() ? 'text-blue-100' : 'text-gray-500 dark:text-gray-400' }}">
                                    {{ $message->created_at->format('h:i A') }}
                                    @if($message->sender_id == auth()->id())
                                        @if($message->read_at)
                                            <!-- Double blue check for read -->
                                            <svg class="w-4 h-4 ml-1 text-blue-400 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            <svg class="w-4 h-4 ml-[-8px] text-blue-400 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        @else
                                            <!-- Single gray check for delivered -->
                                            <svg class="w-4 h-4 ml-1 text-gray-400 animate-fade-in" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                    @endif
                                </div>
                                <!-- Reply/forward icons on hover -->
                                <div class="absolute right-2 -bottom-6 opacity-0 group-hover:opacity-100 flex gap-2 transition-opacity duration-200">
                                    <button class="p-1 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700" title="Reply">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h7V6a1 1 0 011-1h7m-8 5l-4 4m0 0l4 4"></path>
                                        </svg>
                                    </button>
                                    <button class="p-1 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700" title="Forward">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>

                <!-- Message input -->
                <div class="p-4 border-t dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg rounded-b-lg z-10 sticky bottom-0">
                    <div class="flex items-center">
                        <input
                            type="file"
                            wire:model="attachment"
                            class="hidden"
                            id="attachment-input"
                        >
                        <label for="attachment-input" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 mr-2 cursor-pointer text-gray-500 dark:text-gray-400 transition-colors duration-150">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                            </svg>
                        </label>
                        <div class="relative flex-1">
                            <input
                                wire:model="message"
                                wire:keydown.enter="sendMessage"
                                type="text"
                                placeholder="Type a message..."
                                class="w-full px-4 py-3 border dark:border-gray-600 rounded-full focus:outline-none focus:ring-2 focus:ring-primary-500 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                            >
                            <div class="absolute right-2 top-1/2 transform -translate-y-1/2">
                                <button
                                    wire:click="sendMessage"
                                    class="p-2 rounded-full bg-primary-500 hover:bg-primary-600 text-white shadow transition-colors duration-150"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    @if($attachment)
                        <div class="mt-2 flex items-center p-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300 mr-2 flex-1 truncate">{{ $attachment->getClientOriginalName() }}</span>
                            <button wire:click="$set('attachment', null)" class="text-red-500 hover:text-red-600 transition-colors duration-150">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900">
                    <svg class="w-16 h-16 mb-4 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <p class="text-xl font-medium">Select a conversation to start chatting</p>
                    <p class="text-sm mt-2">Or search for a user to start a new conversation</p>
                </div>
            @endif
        </div>
    </div>

    @script
<script>
(function() {
    // Track if we should scroll to bottom
    let shouldScrollToBottom = true;

    function scrollToBottom(behavior = 'smooth') {
        const container = document.getElementById('messages-container');
        if (container && shouldScrollToBottom) {
            setTimeout(() => {
                container.scrollTop = container.scrollHeight;
            }, 100);
        }
    }

    // Handle scroll events to detect user scrolling up
    const initScrollListener = () => {
        const container = document.getElementById('messages-container');
        if (container) {
            container.addEventListener('scroll', function() {
                shouldScrollToBottom =
                    container.scrollTop + container.clientHeight >= container.scrollHeight - 50;
            });
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        initScrollListener();
        scrollToBottom('auto');
    });

    Livewire.on('conversation-selected', (data) => {
        shouldScrollToBottom = true;
        setTimeout(() => {
            scrollToBottom('auto');
        }, 200);
    });

    document.addEventListener('livewire:initialized', () => {
        initScrollListener();
    });

    // Handle new messages
    Livewire.hook('message.processed', (message, component) => {
        window.dispatchEvent(new Event('scrollToBottom'));
    });

    Livewire.on('typing-started', (userId) => {
        setTimeout(() => {
            Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id')).set('isTyping', false);
        }, 2000);
    });
})();
</script>
@endscript
</x-filament::page>
