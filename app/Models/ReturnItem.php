<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    public function returnOrder()
    {
        return $this->belongsTo(OrderReturn::class, 'return_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
