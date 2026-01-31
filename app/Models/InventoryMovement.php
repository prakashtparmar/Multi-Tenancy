<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $table = 'inventory_movements';
    protected $guarded = ['id'];
    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    public function stock()
    {
        return $this->belongsTo(InventoryStock::class, 'stock_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
