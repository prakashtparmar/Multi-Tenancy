<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'rate' => 'decimal:2',
        'breakdown' => 'array',
    ];

    public function taxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class);
    }
}
