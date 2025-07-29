<?php

namespace App\Events;

use App\Models\Attempts\Attempt;
use App\Models\Deliveries\Delivery;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ScoringEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $attempt;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Attempt $attempt)
    {
        $this->attempt = $attempt;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn(): array|Channel|PrivateChannel
    {
        return new PrivateChannel('scoring.'.Delivery::idToHash($this->attempt->delivery_id));
    }
}
