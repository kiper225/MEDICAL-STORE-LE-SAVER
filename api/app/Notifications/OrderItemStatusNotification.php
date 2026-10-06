<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class OrderItemStatusNotification extends Notification
{
    public function __construct(public $orderItem) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'statut_commande',
            'order_id' => $this->orderItem->order_id,
            'message' => "{$this->orderItem->product->nom} est maintenant : {$this->orderItem->statut}",
        ];
    }
}