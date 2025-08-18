<!-- Modern Telegram-like Chat Component -->
<div x-data="modernChat()" class="h-full">
    <!-- Mobile Menu Toggle (hidden on desktop) -->
    <div class="lg:hidden fixed top-4 left-4 z-50">
        <button @click="toggleSidebar" class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>

    <div class="flex h-full bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
        <!-- Sidebar -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed lg:relative inset-y-0 left-0 z-40 w-80 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-xl lg:shadow-lg">

            <!-- Sidebar Header -->
            <div class="p-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-lg">Messages</h2>
                            <p class="text-blue-100 text-sm">Stay connected</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button @click="showNewChat = !showNewChat" class="p-2 hover:bg-white/20 rounded-full transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                        <button @click="toggleSidebar" class="lg:hidden p-2 hover:bg-white/20 rounded-full transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- New Chat Modal -->
            <div x-show="showNewChat"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute top-20 left-4 right-4 bg-white dark:bg-gray-700 rounded-lg shadow-xl border border-gray-200 dark:border-gray-600 p-4 z-50">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">New Conversation</h3>
                    <button @click="showNewChat = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <input type="text" placeholder="Search users..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div class="mt-3 max-h-48 overflow-y-auto">
                    <!-- User list would go here -->
                </div>
            </div>

            <!-- Search Bar -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input
                        wire:model.live.debounce.300ms="searchUsers"
                        type="text"
                        placeholder="Search conversations..."
                        class="w-full pl-12 pr-4 py-3 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200"
                    >
                </div>
            </div>

            <!-- Conversations List -->
            <div class="flex-1 overflow-y-auto bg-white dark:bg-gray-800">
                @if($conversations->count() > 0)
                    @foreach($conversations as $conversation)
                        @php
                            $otherUser = $conversation->users->where('id', '!=', auth()->id())->first();
                            $latestMessage = $conversation->messages->last();
                            $isActive = $activeConversation && $activeConversation->id == $conversation->id;
                            $unreadCount = $conversation->messages->where('sender_id', '!=', auth()->id())->whereNull('read_at')->count();
                        @endphp
                        <div
                            wire:click="selectConversation({{ $conversation->id }})"
                            @click="sidebarOpen = false"
                            class="relative p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-all duration-200 {{ $isActive ? 'bg-blue-50 dark:bg-blue-900/20 border-r-2 border-blue-500' : '' }}"
                        >
                            <div class="flex items-center space-x-3">
                                <!-- Avatar with Online Status -->
                                <div class="relative">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-semibold text-lg shadow-lg">
                                        {{ substr($otherUser->name, 0, 1) }}
                                    </div>
                                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></div>
                                    @if($unreadCount > 0)
                                        <div class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center text-white text-xs font-bold animate-pulse">
                                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 truncate">
                                            {{ $otherUser->name }}
                                        </h3>
                                        @if($latestMessage)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $latestMessage->created_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($latestMessage)
                                        <p class="text-sm text-gray-600 dark:text-gray-300 truncate mt-1">
                                            @if($latestMessage->body)
                                                {{ Str::limit($latestMessage->body, 30) }}
                                            @elseif($latestMessage->attachment)
                                                📎 Attachment
                                            @else
                                                Message
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="flex flex-col items-center justify-center h-full text-gray-500 dark:text-gray-400 p-8">
                        <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <p class="text-center font-medium">No conversations yet</p>
                        <p class="text-sm text-center mt-1">Start a new conversation to begin chatting</p>
                        <button @click="showNewChat = true" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                            Start Chat
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 flex flex-col bg-gray-50 dark:bg-gray-900">
            @if($activeConversation)
                @php
                    $otherUser = $activeConversation->users->where('id', '!=', auth()->id())->first();
                @endphp

                <!-- Chat Header -->
                <div class="p-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <button @click="toggleSidebar" class="lg:hidden p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>
                            <div class="relative">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-semibold">
                                    {{ substr($otherUser->name, 0, 1) }}
                                </div>
                                <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $otherUser->name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    @if($isTyping)
                                        <span class="text-blue-500 animate-pulse">typing...</span>
                                    @else
                                        <span class="text-green-500">● online</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </button>
                            <button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                            <button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Messages Container -->
                <div
                    class="flex-1 p-4 overflow-y-auto bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800"
                    id="messages-container"
                    wire:key="messages-{{ $activeConversation?->id }}"
                >
                    @if($loadingMessages)
                        <div class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-gray-800/50 z-10">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"></div>
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                            </div>
                        </div>
                    @endif

                    @php
                        $lastDate = null;
                    @endphp

                    @foreach($activeConversation->messages as $message)
                        @php
                            $msgDate = $message->created_at->format('Y-m-d');
                        @endphp

                        @if($lastDate !== $msgDate)
                            <div class="flex justify-center my-6">
                                <div class="px-4 py-2 rounded-full bg-white dark:bg-gray-700 text-xs text-gray-600 dark:text-gray-300 shadow-lg border border-gray-200 dark:border-gray-600">
                                    {{ $message->created_at->format('M d, Y') }}
                                </div>
                            </div>
                            @php $lastDate = $msgDate; @endphp
                        @endif

                        <div class="group flex mb-4 {{ $message->sender_id == auth()->id() ? 'justify-end' : 'justify-start' }} animate-fade-in">
                            @if($message->sender_id != auth()->id())
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-semibold text-sm mr-3 self-end shadow-lg">
                                    {{ substr($otherUser->name, 0, 1) }}
                                </div>
                            @endif

                            <div class="relative max-w-xs lg:max-w-md xl:max-w-lg">
                                <div class="{{ $message->sender_id == auth()->id()
                                    ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-2xl rounded-br-md shadow-lg'
                                    : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-2xl rounded-bl-md shadow-lg border border-gray-200 dark:border-gray-600' }}
                                    p-4 transition-all duration-200 hover:shadow-xl">

                                    @if($message->attachment)
                                        <div class="mb-3">
                                            @if($message->is_image)
                                                <img src="{{ $message->attachment_url }}" class="max-w-full h-auto rounded-lg shadow-md" alt="Attachment">
                                            @else
                                                <a href="{{ $message->attachment_url }}"
                                                download
                                                class="flex items-center p-3 bg-gray-100 dark:bg-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors {{ $message->sender_id == auth()->id() ? 'text-white bg-white/20 hover:bg-white/30' : 'text-gray-700 dark:text-gray-300' }}">
                                                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                                    </svg>
                                                    <div>
                                                        <div class="font-medium">Download File</div>
                                                        <div class="text-sm opacity-75">Click to download</div>
                                                    </div>
                                                </a>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="text-sm leading-relaxed">{{ $message->body }}</div>

                                    <div class="flex items-center justify-between mt-2 text-xs {{ $message->sender_id == auth()->id() ? 'text-blue-100' : 'text-gray-500 dark:text-gray-400' }}">
                                        <span>{{ $message->created_at->format('h:i A') }}</span>
                                        @if($message->sender_id == auth()->id())
                                            @if($message->read_at)
                                                <div class="flex items-center space-x-1">
                                                    <svg class="w-4 h-4 text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <svg class="w-4 h-4 text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                            @else
                                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <!-- Message Actions (on hover) -->
                                <div class="absolute right-2 -bottom-8 opacity-0 group-hover:opacity-100 flex gap-1 transition-all duration-200">
                                    <button class="p-2 rounded-full bg-white dark:bg-gray-700 shadow-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors" title="Reply">
                                        <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h7V6a1 1 0 011-1h7m-8 5l-4 4m0 0l4 4"></path>
                                        </svg>
                                    </button>
                                    <button class="p-2 rounded-full bg-white dark:bg-gray-700 shadow-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors" title="Forward">
                                        <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Message Input - Enhanced -->
                <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-lg">
                    <div class="flex items-end space-x-3">
                        <!-- Voice Message Button -->
                        <button class="p-3 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-gray-600 dark:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                            </svg>
                        </button>

                        <!-- Attachment Button -->
                        <div class="relative" x-data="{ showMenu: false }">
                            <input
                                type="file"
                                wire:model="attachment"
                                class="hidden"
                                id="attachment-input"
                            >
                            <button
                                @click="showMenu = !showMenu"
                                class="p-3 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-gray-600 dark:text-gray-300"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                            </button>

                            <!-- Attachment Menu -->
                            <div
                                x-show="showMenu"
                                @click.away="showMenu = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute bottom-full left-0 mb-2 w-48 bg-white dark:bg-gray-700 rounded-lg shadow-xl border border-gray-200 dark:border-gray-600 p-2 z-50"
                            >
                                <label for="attachment-input" class="flex items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded cursor-pointer transition-colors">
                                    <svg class="w-5 h-5 mr-3 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-300">File</span>
                                </label>
                                <button class="flex items-center w-full p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded cursor-pointer transition-colors">
                                    <svg class="w-5 h-5 mr-3 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-300">Photo</span>
                                </button>
                            </div>
                        </div>

                        <!-- Message Input -->
                        <div class="flex-1 relative">
                            <textarea
                                wire:model="message"
                                wire:keydown.enter.prevent="sendMessage"
                                placeholder="Type a message..."
                                rows="1"
                                class="w-full px-4 py-3 pr-12 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 resize-none transition-all duration-200"
                                style="min-height: 48px; max-height: 120px;"
                            ></textarea>

                            <!-- Emoji Button -->
                            <button @click="showEmojiPicker = !showEmojiPicker" class="absolute right-12 bottom-2 p-2 text-gray-500 hover:text-gray-700 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </button>

                            <!-- Send Button -->
                            <button
                                wire:click="sendMessage"
                                class="absolute right-2 bottom-2 p-2 bg-blue-500 hover:bg-blue-600 text-white rounded-full transition-colors duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                                :class="{ 'opacity-50 cursor-not-allowed': !$wire.message.trim() }"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Emoji Picker -->
                    <div x-show="showEmojiPicker"
                         @click.away="showEmojiPicker = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute bottom-full right-0 mb-2 bg-white dark:bg-gray-700 rounded-lg shadow-xl border border-gray-200 dark:border-gray-600 p-3 z-50">
                        <div class="grid grid-cols-8 gap-2">
                            @foreach(['😀', '😂', '😍', '🥰', '😎', '🤔', '👍', '❤️', '🔥', '💯', '🎉', '✨', '🌟', '💪', '👏', '🙏'] as $emoji)
                                <button @click="$wire.message += '{{ $emoji }}'; showEmojiPicker = false" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded transition-colors text-lg">
                                    {{ $emoji }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Attachment Preview -->
                    @if($attachment)
                        <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                    <span class="text-sm text-blue-700 dark:text-blue-300">{{ $attachment->getClientOriginalName() }}</span>
                                </div>
                                <button wire:click="$set('attachment', null)" class="text-blue-500 hover:text-blue-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <!-- Welcome Screen -->
                <div class="flex-1 flex items-center justify-center">
                    <div class="text-center">
                        <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Welcome to Chat</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-6">Select a conversation to start messaging</p>
                        <div class="flex items-center justify-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex items-center space-x-1">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <span>Real-time messaging</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <span>File sharing</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                <span>Secure & private</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function modernChat() {
            return {
                sidebarOpen: window.innerWidth >= 1024,
                showNewChat: false,
                showEmojiPicker: false,

                toggleSidebar() {
                    this.sidebarOpen = !this.sidebarOpen;
                },

                init() {
                    // Handle window resize
                    window.addEventListener('resize', () => {
                        if (window.innerWidth >= 1024) {
                            this.sidebarOpen = true;
                        }
                    });

                    // Auto-scroll to bottom
                    this.$watch('$wire.activeConversation', () => {
                        this.$nextTick(() => {
                            const container = document.getElementById('messages-container');
                            if (container) {
                                container.scrollTop = container.scrollHeight;
                            }
                        });
                    });
                }
            }
        }
    </script>

    <style>
        .animate-fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }

        .animate-bounce {
            animation: bounce 1s infinite;
        }

        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% {
                transform: translate3d(0,0,0);
            }
            40%, 43% {
                transform: translate3d(0, -30px, 0);
            }
            70% {
                transform: translate3d(0, -15px, 0);
            }
            90% {
                transform: translate3d(0, -4px, 0);
            }
        }

        /* Mobile optimizations */
        @media (max-width: 1023px) {
            .sidebar {
                position: fixed;
                z-index: 40;
            }
        }
    </style>
</div>
