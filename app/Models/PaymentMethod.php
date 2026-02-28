<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'type',
        'account_number',
        'account_name',
        'admin_fee',
        'fee_type',
        'logo',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'admin_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function calculateFee(float $amount): float
    {
        if ($this->fee_type === 'percent') {
            return round($amount * ($this->admin_fee / 100), 2);
        }
        return (float) $this->admin_fee;
    }
}
