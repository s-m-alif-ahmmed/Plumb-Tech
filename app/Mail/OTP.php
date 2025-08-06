<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Joaopaulolndev\FilamentGeneralSettings\Models\GeneralSetting;
use Joaopaulolndev\FilamentGeneralSettings\Services\MailSettingsService;

class OTP extends Mailable
{
    use Queueable, SerializesModels;

    public int $otp;
    public User $user;
    public string $header_message;
    public mixed $mailType;
    public ?GeneralSetting $setting;
    /**
     * Create a new message instance.
     */
    public function __construct($otp,User $user, string $message,$mailType = 'verify') {
        $this->otp = $otp;
        $this->user = $user;
        $this->header_message= $message;
        $this->mailType = $mailType;
        $this->setting = GeneralSetting::first();

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        return new Envelope(
            subject: $this->header_message,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
            view: $this->mailType === 'verify' ? 'mail.otp' : 'mail.forget-password',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array {
        return [];
    }
}
