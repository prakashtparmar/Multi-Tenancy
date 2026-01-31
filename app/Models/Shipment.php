<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Shipment extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = ['id'];
    protected $casts = [
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'weight' => 'decimal:3',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'tracking_number']);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
