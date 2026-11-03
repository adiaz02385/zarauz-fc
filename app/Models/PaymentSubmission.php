<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSubmission extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'player_id',
        'period',
        'amount',
        'paid_at',
        'method',
        'reference',
        'notes',
        'attachment_path',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'payment_id',
    ];

    protected $casts = [
        'paid_at' => 'date',
        'amount' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
