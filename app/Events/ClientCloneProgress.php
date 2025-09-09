<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientCloneProgress implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $jobId;
    public int $percentage;
    public string $message;
    public array $data;

    public function __construct(string $jobId, int $percentage, string $message, array $data = [])
    {
        $this->jobId = $jobId;
        $this->percentage = $percentage;
        $this->message = $message;
        $this->data = $data;
    }

    public function broadcastOn()
    {
        return new Channel('client-clone.' . $this->jobId);
    }

    public function broadcastAs()
    {
        return 'progress';
    }

    public function broadcastWith()
    {
        return [
            'job_id' => $this->jobId,
            'percentage' => $this->percentage,
            'message' => $this->message,
            'data' => $this->data,
            'is_complete' => $this->percentage >= 100,
            'has_error' => $this->percentage < 0,
        ];
    }
}