<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class InstallationAssignedNotification extends Notification
{
    public function __construct(public $installation) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'installation_assignee',
            'installation_id' => $this->installation->id,
            'message' => "Une installation vous a été assignée pour le {$this->installation->date_prevue}",
        ];
    }
}