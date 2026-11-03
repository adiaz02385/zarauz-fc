<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpecialEvent extends Model
{
    protected $fillable = [
        'name',
        'event_date',
        'fee_amount',
        'description',
    ];

    protected $casts = [
        'event_date' => 'date',
        'fee_amount' => 'integer',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(SpecialEventPayment::class);
    }
}
