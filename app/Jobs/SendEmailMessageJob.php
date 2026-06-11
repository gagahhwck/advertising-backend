<?php

namespace App\Jobs;

use App\Models\Email\Message;
use App\Models\Email\Recipient;
use App\Models\SSO\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEmailMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id, $subject, $message, $props;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $subject, $message, $props = [])
    {
        $this->user_id = $user_id;
        $this->subject = $subject;
        $this->message = $message;
        $this->props = $props;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->user_id);
        if (!$user) {
            Log::error('User not found for email notification', ['user_id' => $this->user_id]);
            return;
        }
        $bodyHtml = view('message', [
            'subject'   => $this->subject,
            'body'      => $this->message
        ])->render();
        $response = send_email([
            'to'        => [
                ['email' => $user->email, 'name'  => $user->name]
            ],
            'subject'   => $this->subject,
            'bodyHtml'  => $bodyHtml,
            ...$this->props
        ]);

        if ($response->status() != 200) {
            Log::error('Failed to send email notification', [
                'user_id' => $this->user_id,
                'response' => $response->body()
            ]);
        } else {
            Message::updateOrCreate(['transmission_id' => $response->json()['transmissionId']], [
                'transmission_id'   => $response->json()['transmissionId'],
                'subject'           => $this->subject,
                'sender'            => config('mail.from.address'),
                'application_code'  => 'Sipres',
                'body'              => $bodyHtml,
            ]);

            Recipient::updateOrCreate([
                'transmission_id'   => $response->json()['transmissionId'],
                'email'             => $user->email,
                'type'              => 'TO'
            ], [
                'transmission_id'   => $response->json()['transmissionId'],
                'email'             => $user->email,
                'type'              => 'TO'
            ]);
        }
    }
}
