<?php

// app/Models/Investment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    use HasFactory;

    protected $table = 'investments';

    protected $fillable = [
        'user_id',       
        'amount',         
        'investment_date', 
        'status',
    ];


    protected $dates = [
        'investment_date',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);  
    }

    public function investmentResults()
    {
        return $this->hasMany(InvestmentResult::class); 
    }
}
