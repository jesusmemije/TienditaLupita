<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = ['name', 'phone', 'notes'];

    protected $appends = ['total_purchased', 'current_due'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getTotalPurchasedAttribute(): float
    {
        if (array_key_exists('total_purchased', $this->attributes)) {
            return (float) ($this->attributes['total_purchased'] ?? 0);
        }

        return (float) $this->orders()
            ->whereIn('status', ['delivered_paid', 'delivered_partial'])
            ->sum('total_amount');
    }

    public function getCurrentDueAttribute(): float
    {
        if (array_key_exists('current_due', $this->attributes)) {
            return (float) ($this->attributes['current_due'] ?? 0);
        }

        return (float) $this->orders()->sum('due_amount');
    }
}
