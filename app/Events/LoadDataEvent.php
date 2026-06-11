<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoadDataEvent implements ShouldBroadcastNow
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  private $data;

  public function __construct($data = [])
  {
    $this->data = $data;
  }

  /**
   * Get the channels the event should broadcast on.
   *
   * @return \Illuminate\Broadcasting\Channel|string
   */
  public function broadcastOn()
  {
      return new Channel('load-data-channel');
  }

  public function broadcastWith()
  {
    return $this->data;
  }

  public function broadcastAs()
  {
    return 'load-data-event';
  }
}
