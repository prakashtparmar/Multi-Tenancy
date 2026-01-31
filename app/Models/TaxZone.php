<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxZone extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'rate_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
