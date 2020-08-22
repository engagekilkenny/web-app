<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PhotoVerifiedByUser implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // only public properties will be broadcasted
    public $photoId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($photoId)
    {
        $this->photoId = $photoId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        // return PresenceChannel('my-channel');
        // return new PrivateChannel('my-channel');
        return ['my-channel'];
    }
}
