<?php

namespace App\Broadcasting;

use App\Models\ChatRoom;
use App\Models\User;

class ChatRoomChannel
{
    public function join(User $user, ChatRoom $chatRoom): bool
    {
        return $chatRoom->canAccess($user);
    }
}
