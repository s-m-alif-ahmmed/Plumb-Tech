<?php

namespace App\Http\Controllers\API;

use App\Enums\ConversationType;
use App\Events\MessageSend;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\MessageRead;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    use ApiResponse;

    public function index($conversationId)
    {
        // Verify user is a participant
        if (!$this->isParticipant($conversationId)) {
            return $this->error('Unauthorized',403);
        }

        $messages = Message::where('conversation_id', $conversationId)
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Mark messages as read
        $this->markMessagesAsRead($conversationId);
        return $this->pagination('Messages retrieved successfully',$messages);
    }

    public function store(Request $request, $conversationId)
    {

        $request->validate([
            'message' => 'required|string',
            'attachment' => 'nullable|file',
        ]);

       $conversation = Conversation::find($conversationId);

       if (!$conversation) {
           return $this->error('Conversation not found',404);
       }

        if (!$conversation->status) {
            return $this->error('Conversation has ended',400);
        }

        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $path = Helper::fileUpload($request->file('attachment'), 'messages/attachments',getFileName($request->file('attachment')));
        }else{
            $path = null;
        }

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'attachment' => $path,
            'is_read' => false,
        ]);
        $message = $message->load('sender');
        broadcast(new MessageSend($message))->toOthers();
        return $this->ok('Message send successfully',$message);
    }

    private function isParticipant($conversationId)
    {
        return ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', Auth::id())
            ->exists();
    }

    private function findExistingOneToOneConversation($recipientId)
    {
        return Conversation::whereHas('participants', function ($query) use ($recipientId) {
            $query->where('user_id', $recipientId);
        })
            ->whereHas('participants', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->where('type', ConversationType::PRIVATE)
            ->first();
    }

    private function markMessagesAsRead($conversationId)
    {
        $unreadMessages = Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', Auth::id())
            ->whereDoesntHave('reads', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->get();

        foreach ($unreadMessages as $message) {
            MessageRead::create([
                'message_id' => $message->id,
                'user_id' => Auth::id(),
                'read_at' => now(),
            ]);
        }
    }
}
