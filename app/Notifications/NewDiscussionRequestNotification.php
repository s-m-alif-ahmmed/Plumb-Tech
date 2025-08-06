<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDiscussionRequestNotification extends Notification
{
    use Queueable;

    protected $details;

    /**
     * Constructor to initialize notification details
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Specify the delivery channels (only database in this case)
     */
    public function via($notifiable)
    {
        return ['database']; // Store in the database
    }

    /**
     * Convert the notification to an array for database storage
     */
    public function toArray($notifiable)
    {
        // Prepare additional user details
        $userName = auth()->user()->name;
        $Description = $this->details['description'];

        $data = [
            'discussion_request_id' => $this->details['discussion_request_id'],
            'eng_id' => $this->details['eng_id'],
            'subject' => $this->details['subject'],
            'message' => $this->details['message'],
            'user_id' => auth()->id(),
            'user_name' => $userName,
            'avatar' => $this->details['avatar'],
            'description' => $Description,
            'questions_and_answers' => $this->details['questions_and_answers'],
            'images' => $this->details['images'],
        ];

        // Add user-related data to the notification
        if (isset($this->details['user_data'])) {
            $data['user_data'] = $this->details['user_data']; // This will store the user answers data
        }

        return $data;
    }
}
