<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecialIncome extends Model
{
    protected $fillable = [
        'title',
        'category',
        'amount',
        'income_date',
        'method',
        'reference',
        'attachment_path',
        'notes',
        'registered_by',
    ];

    protected $casts = [
        'amount' => 'integer',
        'income_date' => 'date',
    ];

    public function registrar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
