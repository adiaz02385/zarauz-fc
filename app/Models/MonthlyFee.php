<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyFee extends Model
{
    protected $fillable = ['period', 'amount', 'due_date'];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'integer',
    ];
}
