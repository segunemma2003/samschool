<?php

namespace App\Filament\Teacher\Pages;

use App\Events\MessageSent;
use App\Events\UserTyping;
use Filament\Pages\Page;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;


class Chat extends Page
{
    use WithFileUploads;

    protected static string $view = 'filament.teacher.pages.chat';


    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $navigationLabel = 'Chat';

    protected static ?int $navigationSort = 3;

    // This will automatically register the route
    protected static string $routePath = 'chat';
    public $conversations;
    public $activeConversation;
    public $users;
    public $searchUsers = '';
    public $message = '';
    public $attachment;
    public $isTyping = false;
    public $loadingMessages = false;
    public $lastTypingTime = 0;




    protected function getListeners()
{
    $conversationId = $this->activeConversation?->id ?? '0';

    return [
        "echo-private:conversation.{$conversationId},MessageSent" => 'messageReceived',
        "echo-private:user.".Auth::id().",UserTyping" => 'userTyping',
        'conversationSelected' => 'refreshListeners'
    ];



}

public function refreshListeners()
{
    $this->getListeners();
}

public function getConversationEventName()
{
    return $this->activeConversation?->id ?? '0';
}
    // In your Chat component

        protected $rules = [
            'attachment' => [
                'nullable',
                'file',
                'max:10240', // 10MB
                'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt'
            ],
        ];

        public function updatedAttachment()
        {
            $this->validateOnly('attachment');
        }

        protected function getAttachmentType($file)
        {
            $mime = $file->getMimeType();

            if (str_contains($mime, 'image/')) return 'image';
            if (str_contains($mime, 'video/')) return 'video';
            if ($mime === 'application/pdf') return 'pdf';
            if (in_array($file->extension(), ['doc', 'docx'])) return 'word';
            if (in_array($file->extension(), ['xls', 'xlsx'])) return 'excel';

            return 'file';
        }

    public function mount()
    {
        $id = Auth::id();
        $user = User::whereId($id)->first();
        $this->conversations = $user->conversations()->with(['otherUser'])
        ->orderByDesc('last_message_at')
        ->get();
        $this->users = User::where('id', '!=', $id)->get();
    }

    public function selectConversation($conversationId)
    {
        try {
        $this->loadingMessages = true;
        $this->activeConversation = Conversation::with([
            'messages' => function($query) {
                $query->orderBy('created_at', 'asc'); // Changed to ascending order
            },
            'users'
        ])->find($conversationId);

        // dd( $this->activeConversation);

        $this->markMessagesAsRead();
        $this->loadingMessages = false;

        // Force scroll to bottom after conversation change
        // $this->dispatch('scroll-to-bottom');
        $this->dispatch('conversation-selected', conversationId: $conversationId);
        }catch (\Exception $e) {
            $this->dispatch('error', message: 'Failed to load conversation');
            $this->loadingMessages = false;
        }
    }

    public function loadMoreMessages()
{
    $this->activeConversation->load([
        'messages' => function($query) {
            $query->orderBy('created_at', 'desc')
                  ->paginate(20);
        }
    ]);
}


    public function startNewConversation($userId)
    {
        $otherUser = User::find($userId);

        // Check if conversation already exists
        $id = Auth::id();
        $user = User::whereId($id)->first();
        $conversation = $user->conversations()
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create();
            $conversation->users()->attach([$id, $userId]);
        }

        $this->selectConversation($conversation->id);
        $this->conversations = $user->conversations()->with(['users'])->get();

    }

    public function sendMessage()
{
    $this->validate(['message' => 'required_without:attachment']);

    if (!$this->activeConversation) return;

    $message = new Message();
    $message->body = $this->message;
    $message->sender_id = Auth::id();

    if ($this->attachment) {
        $path = $this->attachment->store(
            'chat-attachments/'.now()->format('Y/m/d'),
            's3'
        );
        $message->attachment = $path;
    }

    $this->activeConversation->messages()->save($message);
    $this->activeConversation->touchLastMessage();
    $this->message = '';
    $this->attachment = null;

    broadcast(new MessageSent($message))->toOthers();
}

    public function markMessagesAsRead()
    {
        if (!$this->activeConversation) return;
        $id = Auth::id();
        $user = User::whereId($id)->first();
        $this->activeConversation->messages()
            ->where('sender_id', '!=', $id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        $this->activeConversation->messages()
            ->where('sender_id', '!=', $id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    // #[On('echo-private:conversation.{activeConversation.id},MessageSent')]
    public function messageReceived($payload)
    {
        if ($this->activeConversation && $this->activeConversation->id == $payload['message']['conversation_id']) {
            $this->activeConversation->refresh();
            $id = Auth::id();
        $user = User::whereId($id)->first();
        $this->conversations = $user->conversations()
            ->with(['otherUser'])
            ->orderByDesc('last_message_at')
            ->get();

        // Force mark as read
        $this->markMessagesAsRead();

        // Force scroll to bottom
        $this->dispatch('scroll-to-bottom');
        }
    }

    #[On('echo-private:user.*,UserTyping')]
    public function userTyping($payload)
    {
        $authUserId = Auth::id();
        if ($this->activeConversation &&
            in_array($payload['userId'], $this->activeConversation->users->pluck('id')->toArray())) {
            $this->isTyping = true;
            $this->dispatch('typing-started', userId: $payload['userId']);
        }
    }

    public function updatedMessage()
    {
        $id = Auth::id();
        $user = User::whereId($id)->first();
        $this->lastTypingTime = time();
        broadcast(new UserTyping($id, $this->activeConversation->id))->toOthers();
    }
}
