<?php

namespace App\Http\Controllers\API;

use App\Enums\ConversationType;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Jobs\ServiceSessionEnd;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\DiscussionRequest;
use App\Models\ServiceSession;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{

    use ApiResponse;
    public function index()
    {
        $authUserId = Auth::id();
        $conversations = Conversation::whereHas('participants', function ($query) use ($authUserId) {
            $query->where('user_id', $authUserId);
        })
            ->with(['participants' => function ($query) use ($authUserId) {
                $query->where('user_id', '!=', $authUserId)
                    ->with(['user']);
            }])
            ->with(['messages' => function ($query) {
                $query->latest()->first();
            }])
            ->latest()
            ->get()
            ->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'status' => $conversation->status,
                    'type' => $conversation->type,
                    'recipient' => $conversation->participants->first()?->user,
                    'last_message' => $conversation?->messages->first(),
                ];
            });
        return $this->ok('Conversations list', $conversations);
    }

    public function store(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:discussion_requests,id',
        ]);

        try {
            $discussionRequest = DiscussionRequest::with('payment','conversation')->find($request->request_id);
            if (!$discussionRequest || $discussionRequest->user_id != Auth::id()) {
                return $this->error("Discussion request not found", 404);
            }

            if (!$discussionRequest->engineer_id){
                return $this->error("Not yet accept this request any engineer.", 403);
            }

            if ($discussionRequest->status !== Status::PENDING){
                return $this->error("This discussion request already completed or canceled.", 403);
            }

            if (!$discussionRequest->payment || $discussionRequest->payment->status !== 'completed'){
                return $this->error('Not yet paid for this  discussion request', 403);
            }

            if ($discussionRequest->conversation){
                return $this->error("This discussion request already started", 403);
            }

            DB::beginTransaction();
            $session = ServiceSession::create([
                'start_at' => now(),
                'expire_at' =>  now()->addMinutes(20),
            ]);

            $conversation = Conversation::create([
                'type' => ConversationType::PRIVATE,
                'title' => "Service Request For {$discussionRequest->service->title}",
                'service_request_id' =>  $discussionRequest->id,
                'service_session_id' => $session->id,
            ]);


           $participation = ConversationParticipant::insert([[
                'conversation_id' => $conversation->id,
                'user_id' => auth()->id(),
                'role' => 'member',
            ], [
                'conversation_id' => $conversation->id,
                'user_id' => $discussionRequest->engineer_id,
                'role' => 'member',
            ]]);
            ServiceSessionEnd::dispatch($discussionRequest)->delay(now()->addMinutes(20));
            DB::commit();

            return $this->ok('Conversation created successfully.', $conversation->load('participants.user'));
        }catch (\Exception $exception){
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $conversation = Conversation::with(['participants.user'])->findOrFail($id);
        return $this->ok('Conversation retrieved successfully', $conversation);
    }
}
