<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialAdvice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'advice',
        'advice_type',
        'created_at',
        'updated_at',
    ];
}
