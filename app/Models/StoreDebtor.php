<?php

namespace App\Models;

use App\Models\StoreDebtMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreDebtor extends Model
{
    protected $fillable = ['name'];

    public function movements(): HasMany
    {
        return $this->hasMany(StoreDebtMovement::class, 'debtor_id');
    }

    public function getBalanceAttribute(): float
    {
        if ($this->relationLoaded('movements')) {
            return (float) $this->movements->sum(fn (StoreDebtMovement $movement) =>
                $movement->type === 'charge' ? (float) $movement->amount : -(float) $movement->amount);
        }

        return (float) $this->movements()->where('type', 'charge')->sum('amount')
            - (float) $this->movements()->where('type', 'payment')->sum('amount');
    }
}
