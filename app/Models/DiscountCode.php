<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'min_purchase',
        'max_uses',
        'used_count',
        'expired_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'expired_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function isValid(float $purchaseAmount = 0): bool
    {
        if (!$this->is_active)
            return false;
        if ($this->expired_at && $this->expired_at->isPast())
            return false;
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses)
            return false;
        if ($purchaseAmount < $this->min_purchase)
            return false;
        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if ($this->type === 'percent') {
            return round($amount * ($this->value / 100), 2);
        }
        return min((float) $this->value, $amount);
    }
}
