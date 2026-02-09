<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InteractionOutcome extends Model
{
    protected $fillable = [
        'name',
        'type',
        'is_active',
        'color',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
