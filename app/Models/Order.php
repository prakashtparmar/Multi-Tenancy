<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * Guarded attributes
     */
    protected $guarded = ['id'];

    /**
     * Casts
     */
    protected $casts = [
        'placed_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'is_future_order' => 'boolean',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'verification_status' => 'string',
    ];

    /* -------------------------------------------------------------------------- */
    /* Boot / Order Number                                                        */
    /* -------------------------------------------------------------------------- */

    protected static function booted()
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * Order number format:
     * ORD-YYYYMMDD-XXXX
     */
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');

        return DB::transaction(function () use ($date) {

            $lastOrder = self::whereDate('created_at', now())
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $lastSequence = 0;

            if (
                $lastOrder &&
                preg_match('/ORD-\d{8}-(\d+)/', $lastOrder->order_number, $matches)
            ) {
                $lastSequence = (int) $matches[1];
            }

            $nextSequence = str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);

            return "ORD-{$date}-{$nextSequence}";
        });
    }

    /* -------------------------------------------------------------------------- */
    /* Activity Log                                                               */
    /* -------------------------------------------------------------------------- */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'payment_status', 'shipping_status'])
            ->logOnlyDirty();
    }

    /* -------------------------------------------------------------------------- */
    /* Relationships                                                             */
    /* -------------------------------------------------------------------------- */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(OrderVerification::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    /* -------------------------------------------------------------------------- */
    /* Scopes                                                                     */
    /* -------------------------------------------------------------------------- */

    public function scopeUnverified($query)
    {
        return $query->whereIn('verification_status', ['unverified', 'pending_followup']);
    }

    /**
     * Billing address snapshot (customer address at order time)
     */
    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'billing_address_id');
    }

    /**
     * Shipping address snapshot (customer address at order time)
     */
    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'shipping_address_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /* -------------------------------------------------------------------------- */
    /* Helpers (VERY IMPORTANT for Print Invoice)                                 */
    /* -------------------------------------------------------------------------- */

    /**
     * Get latest invoice safely
     */
    public function latestInvoice(): ?Invoice
    {
        return $this->invoices()->latest()->first();
    }

    /**
     * Check if order has any invoice
     */
    public function hasInvoice(): bool
    {
        return $this->invoices()->exists();
    }
}
