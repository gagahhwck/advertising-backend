<?php

namespace App\Notifications;

use App\Jobs\SendEmailMessageJob;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class contentReceiptNotification extends Notification
{
    use Queueable;

    protected array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $data = ['database'];
        if (config('setting.email_notification') == 'TRUE') {
            $attachments = $this->payload['attachments'] ?? [];

            SendEmailMessageJob::dispatch($notifiable->id, $this->payload['description'] ?? '',
                '<h4 style="font-size: 16px; margin-top: 0;">'.($this->payload['title'] ?? '').'</h4>
                <p>' . nl2br($this->payload['description'] ?? '') . '</p>
                <p style="color: #cccccc; font-size: 12px;">Please do not reply to this automated message!</p>',
                array_filter([
                    'file' => !empty($attachments) ? $attachments : null,
                ]));
        }

        return $data;
    }

    // attachments are provided in the payload under 'attachments' key

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Event Content Notification')
            ->line('You have a new content.')
            ->line('Title: ' . ($this->payload['title'] ?? ''))
            ->line('Description: ' . ($this->payload['description'] ?? ''));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->payload;
    }
}
