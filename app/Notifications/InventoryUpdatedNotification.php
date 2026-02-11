<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Product;

class InventoryUpdatedNotification extends Notification
{
    use Queueable;

    protected $product;
    protected $quantity;
    protected $type;
    protected $reason;
    protected $oldQuantity;
    protected $newQuantity;

    /**
     * Create a new notification instance.
     */
    public function __construct(Product $product, float $quantity, string $type, string $reason, float $oldQuantity, float $newQuantity)
    {
        $this->product = $product;
        $this->quantity = $quantity;
        $this->type = $type;
        $this->reason = $reason;
        $this->oldQuantity = $oldQuantity;
        $this->newQuantity = $newQuantity;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $action = match ($this->type) {
            'add' => 'added to',
            'subtract' => 'removed from',
            'set' => 'set for',
            default => 'updated for'
        };

        return [
            'title' => 'Inventory Updated',
            'message' => "Stock {$action} {$this->product->name}. Change: {$this->quantity} ({$this->type}). Stock: {$this->oldQuantity} → {$this->newQuantity}. Reason: {$this->reason}.",
            'icon' => '<svg class="size-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>',
            'link' => null, // No link requested
        ];
    }
}
