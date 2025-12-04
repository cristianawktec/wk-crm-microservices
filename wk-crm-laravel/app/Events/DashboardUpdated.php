<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DashboardUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $data;
    public string $tipo;
    public string $mensagem;

    /**
     * Create a new event instance.
     */
    public function __construct(array $data, string $tipo = 'atualização', string $mensagem = 'Dados atualizados')
    {
        $this->data = $data;
        $this->tipo = $tipo;
        $this->mensagem = $mensagem;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('dashboard-updates')
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'data' => $this->data,
            'tipo' => $this->tipo,
            'mensagem' => $this->mensagem,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'dashboard.updated';
    }
}
