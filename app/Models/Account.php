<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'is_cash_bank',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_cash_bank' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
