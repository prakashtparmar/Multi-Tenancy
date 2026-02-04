<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Invoice extends Model
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
        'issue_date'   => 'date',
        'due_date'     => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount'  => 'decimal:2',
    ];

    /* -------------------------------------------------------------------------- */
    /* Boot / Auto Invoice Number                                                 */
    /* -------------------------------------------------------------------------- */

    protected static function booted()
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
        });
    }

    /**
     * Invoice number format:
     * INV-YYYYMMDD-XXXX
     */
    public static function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');

        return DB::transaction(function () use ($date) {

            $lastInvoice = self::whereDate('created_at', now())
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $lastSequence = 0;

            if (
                $lastInvoice &&
                preg_match('/INV-\d{8}-(\d+)/', $lastInvoice->invoice_number, $matches)
            ) {
                $lastSequence = (int) $matches[1];
            }

            $nextSequence = str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);

            return "INV-{$date}-{$nextSequence}";
        });
    }

    /* -------------------------------------------------------------------------- */
    /* Activity Logging                                                           */
    /* -------------------------------------------------------------------------- */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'paid_amount', 'due_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /* -------------------------------------------------------------------------- */
    /* Relationships                                                             */
    /* -------------------------------------------------------------------------- */

    /**
     * Invoice → Order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Invoice → Payments
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Invoice → Customer (optional but recommended)
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /* -------------------------------------------------------------------------- */
    /* Accessors                                                                 */
    /* -------------------------------------------------------------------------- */

    /**
     * Remaining balance (total - paid)
     * Usage: $invoice->balance
     */
    protected function balance(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->total_amount - $this->paid_amount
        );
    }

    /**
     * Check if invoice is fully paid
     * Usage: $invoice->is_paid
     */
    protected function isPaid(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->paid_amount >= $this->total_amount
        );
    }
}
