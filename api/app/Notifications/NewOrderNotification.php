<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    public function __construct(public $order) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'nouvelle_commande',
            'order_id' => $this->order->id,
            'message' => "Nouvelle commande #{$this->order->id} sur vos produits",
        ];
    }
}