<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Se emite cuando cambia algo del menú de una compañía (precio, producto, promo).
 * Las pantallas (Player) suscritas al canal de la compañía recargan su menú.
 *
 * Usa ShouldBroadcastNow para emitir de forma síncrona (sin worker de colas).
 */
class MenuUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $companyId)
    {
    }

    /**
     * Canal público por compañía (los precios son información de exhibición pública).
     */
    public function broadcastOn(): array
    {
        return [new Channel("company.{$this->companyId}")];
    }

    public function broadcastAs(): string
    {
        return 'menu.updated';
    }

    public function broadcastWith(): array
    {
        return ['company_id' => $this->companyId];
    }
}
