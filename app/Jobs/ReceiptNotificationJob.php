<?php

namespace App\Jobs;

use App\Models\Advertising\ContentReceipt;
use App\Notifications\contentReceiptNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReceiptNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        protected ContentReceipt $contentReceipt
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('ReceiptNotificationJob started', ['receipt_id' => $this->contentReceipt->id]);

        try {
            $recipient = $this->contentReceipt->to_user;

            if (! $recipient) {
                Log::warning('ReceiptNotificationJob: recipient not found', ['receipt_id' => $this->contentReceipt->id, 'to' => $this->contentReceipt->to]);
                return;
            }

            $attachments = [];
            if ($this->contentReceipt->content && $this->contentReceipt->content->relationLoaded('media_files')) {
                $attachments = $this->contentReceipt->content->media_files->map(function ($media) {
                    return [
                        'file_name' => $media->file_name ?: basename($media->file_path),
                        'file_path' => $media->file_path,
                    ];
                })->toArray();
            } elseif ($this->contentReceipt->content) {
                $attachments = $this->contentReceipt->content->media_files()->get()->map(function ($media) {
                    return [
                        'file_name' => $media->file_name ?: basename($media->file_path),
                        'file_path' => $media->file_path,
                    ];
                })->toArray();
            }

            $payload = [
                'content_receipt_id' => $this->contentReceipt->id,
                'content_id' => $this->contentReceipt->content_id,
                'title' => $this->contentReceipt->title,
                'description' => $this->contentReceipt->description,
                'from' => $this->contentReceipt->from,
                'attachments' => $attachments,
            ];

            $recipient->notify(new contentReceiptNotification($payload));

            Log::info('ReceiptNotificationJob finished', ['receipt_id' => $this->contentReceipt->id, 'recipient' => $recipient->id ?? null]);
        } catch (Throwable $e) {
            Log::error('ReceiptNotificationJob failed', [
                'receipt_id' => $this->contentReceipt->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // rethrow to allow the queue system to mark job as failed/retry according to config
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('ReceiptNotificationJob::failed called', [
            'receipt_id' => $this->contentReceipt->id ?? null,
            'exception' => $exception->getMessage(),
        ]);
    }
}
