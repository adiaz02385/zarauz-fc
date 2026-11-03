<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecialEventPayment extends Model
{
    protected $fillable = [
        'special_event_id',
        'player_id',
        'amount',
        'paid_at',
        'method',
        'reference',
        'attachment_path',
        'notes',
        'registered_by',
    ];

    protected $casts = [
        'amount' => 'integer',
        'paid_at' => 'date',
    ];

    public function specialEvent(): BelongsTo
    {
        return $this->belongsTo(SpecialEvent::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function registrar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
