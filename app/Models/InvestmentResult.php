<?php

// app/Models/InvestmentResult.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentResult extends Model
{
    use HasFactory;

    protected $table = 'investment_results';


    protected $fillable = [
        'investment_id',
        'result',
        'date',
        'created_at',
        'updated_at',
    ];

  
  

    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }
}
