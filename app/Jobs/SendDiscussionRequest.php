<?php

namespace App\Jobs;

use App\Models\RequestAcceptDenied;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class SendDiscussionRequest implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */

    public string $title;
    public string $body;
    public int $discussion_request_id;
    public Collection $userList;

    public function __construct(string $title, string $body, int $discussion_request_id, Collection $userList)
    {
        $this->title = $title;
        $this->body = $body;
        $this->discussion_request_id = $discussion_request_id;
        $this->userList = $userList;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
//            $factory = (new Factory)->withServiceAccount(storage_path(config('services.firebase.service_account_file_path')));
//            $messaging = $factory->createMessaging();
            foreach ($this->userList as $user) {
                //store request to engineer
                RequestAcceptDenied::create([
                    'request_id' => $this->discussion_request_id,
                    'engineer_id' => $user->id,
                    'status' => 'pending',
                ]);

                //send notification to the user device
//                foreach ($user?->firebaseTokens ?? [] as $firebaseToken) {
//                    if ($firebaseToken->token){
//                        $notification = Notification::create($this->title,$this->body);
//                        $message = CloudMessage::withTarget('token', $firebaseToken->token)->withNotification($notification)->withData([
//                            'request_id' => $this->discussion_request_id,
//                            'accept_url' => route('request.accept', $this->discussion_request_id),
//                            'decline_url' => route('request.decline', $this->discussion_request_id),
//                        ]);
//                        $messaging->send($message);
//                    }
//                }
            }

        }catch (MessagingException|FirebaseException $e) {
            Log::error($e->getMessage());
        }
    }
}
