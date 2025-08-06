<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return $this->error('Unauthorized: User not authenticated', 401);
            }

            // Fetch unread notifications with user avatar
            $unreadNotifications = $user->unreadNotifications->map(function ($notification) {
                // Decode notification data if it's a string
                $notificationData = is_string($notification->data) ? json_decode($notification->data, true) : $notification->data;

                // Debugging: Check notification data
                Log::info('Notification Data: ', $notificationData);

                // Fetch the sender (user who created the problem or discussion request)
                $sender = \App\Models\User::find($notificationData['user_id'] ?? null); // Assuming 'user_id' stores the user_id
                $avatar = null;

                // Check if sender exists and has avatar
                if ($sender && $sender->avatar) {
                    $avatar = $sender->avatar; // Set avatar if exists
                }

                // Use eng_id to fetch the user (engineer)
                $engineer = null;
                if (isset($notificationData['eng_id'])) {
                    $engineer = \App\Models\User::find($notificationData['eng_id']);
                }

                // Check if engineer exists and has avatar
                $engineerAvatar = null;
                if ($engineer && $engineer->avatar) {
                    $engineerAvatar = $engineer->avatar;
                }

                // Build the response data
                return [
                    'message' => $notificationData['message'] ?? '',
                    'subject' => $notificationData['subject'] ?? '',
                    'user_data' => [
                        'discussion_request_id' => $notificationData['discussion_request_id'] ?? null, // ✅ Discussion Request ID
                        'user_id' => $notificationData['user_id'] ?? '',
                        'user_name' => $notificationData['user_name'] ?? '', // Fetch the sender's name
                        'avatar' => $notificationData['avatar'] ?? '',
                        'description' => $notificationData['description'] ?? '', // Problem description
                        'answers' => $notificationData['questions_and_answers'] ?? [], // Questions and answers
                        'images' => $notificationData['images'] ?? [] // images
                    ],
                    'time' => $notification->created_at->diffForHumans(),
                    'engineer' => [
                        'eng_id' => $notificationData['eng_id'] ?? '',
                        'name' => $engineer ? $engineer->name : '', // Engineer's name
                    ]
                ];
            });

            return $this->ok('Notifications retrieved successfully', $unreadNotifications);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}