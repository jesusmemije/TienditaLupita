<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = ['name', 'internal_name', 'phone', 'notes'];

    protected $appends = ['total_paid', 'current_due'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getTotalPaidAttribute(): float
    {
        if (array_key_exists('total_paid', $this->attributes)) {
            return (float) ($this->attributes['total_paid'] ?? 0);
        }

        return (float) $this->orders()
            ->whereIn('status', ['delivered_paid', 'delivered_partial'])
            ->sum('paid_amount');
    }

    public function getCurrentDueAttribute(): float
    {
        if (array_key_exists('current_due', $this->attributes)
            && array_key_exists('pending_orders_total', $this->attributes)) {
            return (float) ($this->attributes['current_due'] ?? 0)
                + (float) ($this->attributes['pending_orders_total'] ?? 0);
        }

        return (float) $this->orders()
            ->selectRaw(
                'COALESCE(SUM(due_amount), 0) + COALESCE(SUM(CASE WHEN status = ? THEN total_amount ELSE 0 END), 0) AS outstanding_total',
                ['pending_delivery'],
            )
            ->value('outstanding_total');
    }
}
