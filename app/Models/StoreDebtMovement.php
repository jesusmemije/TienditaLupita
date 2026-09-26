<?php

namespace App\Models;

use App\Models\StoreDebtor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreDebtMovement extends Model
{
    protected $fillable = ['amount', 'type', 'description', 'movement_date'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'movement_date' => 'date',
        ];
    }

    public function debtor(): BelongsTo
    {
        return $this->belongsTo(StoreDebtor::class, 'debtor_id');
    }
}
