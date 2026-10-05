<?php

namespace App\Events\Tickets;

use App\Models\Tickets\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketKanbanUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;
    public $action;
    public $globalStatusCounts;

    /**
     * Create a new event instance.
     *
     * @param  Ticket|array  $ticket
     * @param  string  $action
     * @param  array|null  $globalStatusCounts
     * @return void
     */
    public function __construct($ticket, string $action = 'updated', ?array $globalStatusCounts = null)
    {
        $this->ticket = $ticket;
        $this->action = $action;
        $this->globalStatusCounts = $globalStatusCounts ?: Ticket::selectRaw('estado_actual, count(*) as total')
            ->groupBy('estado_actual')
            ->pluck('total', 'estado_actual')
            ->toArray();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('tickets.kanban'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'TicketKanbanUpdated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'ticket' => $this->ticket,
            'action' => $this->action,
            'globalStatusCounts' => $this->globalStatusCounts,
        ];
    }
}

