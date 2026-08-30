<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'amount',
        'is_mandatory',
        'is_withdrawable',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_mandatory' => 'boolean',
        'is_withdrawable' => 'boolean',
        'is_active' => 'boolean',
    ];
}
