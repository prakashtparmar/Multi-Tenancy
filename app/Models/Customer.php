<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    // protected $connection = 'mysql'; // Removed to support hybrid multi-tenancy

    protected $guarded = ['id'];

    protected $casts = [
        'crops' => 'array',
        'is_active' => 'boolean',
        'is_blacklisted' => 'boolean',
        'kyc_completed' => 'boolean',
        'kyc_verified_at' => 'datetime',
        'credit_valid_till' => 'date',
        'first_purchase_at' => 'date',
        'last_purchase_at' => 'date',
        'land_area' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'tags' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            $customer->uuid = (string) Str::uuid();

            // Auto-generate customer code if not provided
            if (empty($customer->customer_code)) {
                $latest = static::latest('id')->first();
                $nextId = $latest ? $latest->id + 1 : 1;
                $customer->customer_code = 'CUST-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);
            }

            if (auth()->check()) {
                $customer->created_by = auth()->id();
            }
        });

        static::updating(function ($customer) {
            if (auth()->check()) {
                $customer->updated_by = auth()->id();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
                ->orWhere('middle_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('mobile', 'like', "%{$term}%")
                ->orWhere('phone_number_2', 'like', "%{$term}%")
                ->orWhere('relative_phone', 'like', "%{$term}%")
                ->orWhere('customer_code', 'like', "%{$term}%")
                ->orWhere('company_name', 'like', "%{$term}%");
        });
    }

    public function interactions()
    {
        return $this->hasMany(CustomerInteraction::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }
}
