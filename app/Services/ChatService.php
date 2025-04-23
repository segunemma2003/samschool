<?php
// app/Services/ChatService.php

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;

class ChatService
{
    public function startConversation(User $user1, User $user2)
    {
        $conversation = Conversation::whereHas('users', function($q) use ($user1) {
            $q->where('user_id', $user1->id);
        })->whereHas('users', function($q) use ($user2) {
            $q->where('user_id', $user2->id);
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create();
            $conversation->users()->attach([$user1->id, $user2->id]);
        }

        return $conversation;
    }
}
