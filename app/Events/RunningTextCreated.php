<?php

namespace App\Events;

use App\Models\Advertising\RunningText;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RunningTextCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public RunningText $running_text;
    public string $action = 'created';

    /**
     * Create a new event instance.
     */
    public function __construct(RunningText $running_text)
    {
        $this->running_text = $running_text;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('running-text'),
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
