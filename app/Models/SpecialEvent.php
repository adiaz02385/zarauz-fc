<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpecialEvent extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_FINISHED = 'finished';

    protected $fillable = [
        'name',
        'event_date',
        'fee_amount',
        'description',
        'status',
    ];

    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
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
