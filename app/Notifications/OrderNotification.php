<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class OrderNotification extends Notification
{
    use Queueable;

    protected $order;
    protected $action;
    protected $details;

    /**
     * Create a new notification instance.
     * @param Order $order
     * @param string $action 'created', 'updated', 'confirmed', 'processed', 'shipped', 'cancelled', etc.
     * @param string|null $details
     */
    public function __construct(Order $order, string $action, ?string $details = null)
    {
        $this->order = $order;
        $this->action = $action;
        $this->details = $details;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $orderNumber = $this->order->order_number;
        $userName = auth()->user()->name ?? 'System';

        $actionLabel = match ($this->action) {
            'created' => 'Created',
            'updated' => 'Edited',
            'confirm' => 'Confirmed',
            'process' => 'Processing Started',
            'ready_to_ship' => 'Marked Ready to Ship',
            'ship' => 'Shipped',
            'in_transit' => 'Marked In Transit',
            'deliver' => 'Delivered',
            'cancel' => 'Cancelled',
            default => ucfirst($this->action),
        };

        $title = "Order #{$orderNumber} {$actionLabel}";
        $message = "Order #{$orderNumber} was {$this->action} by {$userName}.";

        if ($this->details) {
            $message .= " Details: {$this->details}";
        }

        $icon = $this->getIcon();

        return [
            'title' => $title,
            'message' => $message,
            'order_id' => $this->order->id,
            'action' => $this->action,
            'icon' => $icon,
        ];
    }

    protected function getIcon(): string
    {
        return match ($this->action) {
            'created' => '<svg class="size-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>',
            'updated' => '<svg class="size-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>',
            'cancel' => '<svg class="size-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>',
            'ship' => '<svg class="size-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" /></svg>',
            'deliver' => '<svg class="size-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>',
            default => '<svg class="size-5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
        };
    }
}
