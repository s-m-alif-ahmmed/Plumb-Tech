<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Events\MessageSent;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;

class ChatController extends Controller
{
    use ApiResponse;
    
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'receiver_id' => 'required|exists:users,id',
        ]);

        $chatMessage = ChatMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        broadcast(new MessageSent($chatMessage))->toOthers();

        return $this->ok('Message Sent!', $chatMessage);
    }


    public function getMessages()
    {
        $userId = auth()->id();

        $messages = ChatMessage::where('receiver_id', $userId)
            ->orWhere('sender_id', $userId)
            ->orderBy('created_at', 'asc')
            // Fetch the information of the sender and receiver
            ->with(['sender', 'receiver'])
            ->get();

        return $this->ok('Messages retrieved successfully!', $messages);
    }
}
