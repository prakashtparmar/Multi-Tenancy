<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Village.php
class Village extends Model
{
    protected $fillable = [
        'village_name',
        'pincode',
        'post_so_name',
        'taluka_name',
        'district_name',
        'state_name',
    ];
}
