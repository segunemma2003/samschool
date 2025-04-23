<!-- resources/views/filament/student/pages/chat.blade.php -->
<x-filament::page>
    <div class="flex h-[calc(100vh-200px)] border dark:border-gray-700 rounded-lg shadow-sm bg-white dark:bg-gray-800">
        <!-- Sidebar -->
        <div class="w-1/4 border-r dark:border-gray-700 flex flex-col">
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
                            class="p-3 border-b dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center transition-colors duration-150"
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
                        class="p-3 border-b dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center transition-colors duration-150 {{ $activeConversation && $activeConversation->id == $conversation->id ? 'bg-primary-50 dark:bg-primary-900/20' : '' }}"
                    >
                        <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-800 text-primary-700 dark:text-primary-300 flex items-center justify-center mr-3 font-medium">
                            {{ substr($conversation->otherUser->name, 0, 1) }}
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
        <div class="flex-1 flex flex-col">

            @if($activeConversation)
                <!-- Chat header -->
                <div class="p-4 border-b dark:border-gray-700 flex items-center">
                    @php
                        $otherUser = $activeConversation->users->where('id', '!=', auth()->id())->first();
                    @endphp
                    <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-800 text-primary-700 dark:text-primary-300 flex items-center justify-center mr-3 font-medium">
                        {{ substr($otherUser->name, 0, 1) }}
                    </div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $otherUser->name }}</div>
                    @if($isTyping)
                        <div class="ml-3 px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-sm text-gray-600 dark:text-gray-300 animate-pulse">typing...</div>
                    @endif
                </div>

                <!-- Messages -->
                <div
                    class="flex-1 p-4 overflow-y-auto bg-gray-50 dark:bg-gray-900 relative"
                    id="messages-container"
                    wire:key="messages-{{ $activeConversation?->id }}"
                >

                @if($loadingMessages)
                <div class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-gray-800/50 z-10">
                    <x-filament::loading-indicator class="w-8 h-8" />
                </div>
            @endif
                    @foreach($activeConversation->messages as $message)
                    <div class="flex mb-4 {{ $message->sender_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
                        @if($message->sender_id != auth()->id())
                            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-800 text-primary-700 dark:text-primary-300 flex items-center justify-center mr-2 self-end">
                                {{ substr($otherUser->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="{{ $message->sender_id == auth()->id()
                            ? 'bg-primary-500 text-white'
                            : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100' }}
                            rounded-lg p-3 max-w-xs lg:max-w-md shadow-sm">
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
                                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        @else
                                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Message input -->
                <div class="p-4 border-t dark:border-gray-700 bg-white dark:bg-gray-800">
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
                                    class="p-2 rounded-full bg-primary-500 hover:bg-primary-600 text-white transition-colors duration-150"
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
            // Add a small delay to ensure DOM is fully updated
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
                // If user scrolls up, pause auto-scrolling
                shouldScrollToBottom =
                    container.scrollTop + container.clientHeight >= container.scrollHeight - 50;
            });
        }
    };

    // Initialize scroll listeners
    document.addEventListener('DOMContentLoaded', () => {
        initScrollListener();
        scrollToBottom('auto');
    });

    // Handle conversation changes - critical for initial load of messages
    Livewire.on('conversation-selected', (data) => {
        console.log("Conversation selected, scrolling to bottom...");
        shouldScrollToBottom = true;
        // Use a slightly longer timeout to ensure messages are loaded
        setTimeout(() => {
            scrollToBottom('auto');
        }, 200);
    });

    // This is essential for when the component re-renders
    document.addEventListener('livewire:initialized', () => {
        initScrollListener();
    });

    // Handle new messages
    Livewire.hook('message.processed', (message, component) => {
        if (component.fingerprint.name === 'ourstudent.pages.chat') {
            scrollToBottom('smooth');
        }
    });

    // Typing indicator
    Livewire.on('typing-started', (userId) => {
        setTimeout(() => {
            Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id')).set('isTyping', false);
        }, 2000);
    });
})();
</script>
@endscript
</x-filament::page>
