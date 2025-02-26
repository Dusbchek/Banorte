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
        'amount',         
        'return',         
        'investment_date', 
        'result_date',    
    ];

  
    protected $dates = [
        'investment_date',
        'result_date',
    ];

    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }
}
