<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Player extends Model
{
    protected $fillable = [
        'name',
        'identification',
        'nickname',
        'phone',
        'public_token',
        'active',
    ];

    protected $casts = ['active' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (Player $player): void {
            $player->public_token ??= (string) Str::uuid();
        });
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentsFor(string $period): HasMany
    {
        return $this->payments()->where('period', $period);
    }
}
