<?php

namespace App\Events;

use App\Entity\Conversation;

class ConversationAvailableEvent
{

    public function __construct(private Conversation $conversation) {}

    public function getConversation()
    {
        return $this->conversation;
    }
}
