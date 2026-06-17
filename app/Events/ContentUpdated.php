<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use App\Models\Advertising\Content;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Content $content;
    public string $action = 'updated';

    /**
     * Create a new event instance.
     */
    public function __construct(Content $content, string $action = 'updated')
    {
        $this->content = $content;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('contents'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'data' => $this->content->toArray(),
        ];
    }
}
