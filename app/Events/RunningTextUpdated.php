<?php

namespace App\Events;

use App\Models\Advertising\RunningText;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RunningTextUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public RunningText $running_text;
    public string $action = 'updated';

    /**
     * Create a new event instance.
     */
    public function __construct(RunningText $running_text, string $action = 'updated')
    {
        $this->running_text = $running_text;
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
            new Channel('running_text'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'data' => $this->running_text->toArray(),
        ];
    }
}
