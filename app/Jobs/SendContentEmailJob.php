<?php

namespace App\Jobs;

use App\Notifications\contentReceiptNotification;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendContentEmailJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public contentReceiptNotification $notification;

    /**
     * Create a new job instance.
     */
    public function __construct(contentReceiptNotification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $this->notification->update([
                'status' => 'sent',
            ]);

            Mail::to($this->notification->to)
                ->send(new contentReceiptNotification($this->notification));

            $this->notification->update([
                'status' => 'received', // atau "sent" kalau mau lebih simpel
            ]);

        } catch (\Exception $e) {
            $this->notification->update([
                'status' => 'failed',
            ]);

            throw $e;
        }
    }
}
