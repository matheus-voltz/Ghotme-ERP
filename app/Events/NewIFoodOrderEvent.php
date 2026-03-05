<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Budget;

class NewIFoodOrderEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $companyId;

    /**
     * Create a new event instance.
     */
    public function __construct(int $companyId, array $orderData)
    {
        $this->companyId = $companyId;
        // Passamos os dados básicos do pedido que queremos mostrar no toast/alerta
        $this->order = $orderData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Canal privado para que apenas usuários da empresa X recebam
        return [
            new PrivateChannel("company.{$this->companyId}"),
        ];
    }

    /**
     * Nome do evento que o Frontend (JS/Echo) vai escutar.
     */
    public function broadcastAs(): string
    {
        return 'ifood.new_order';
    }
}
