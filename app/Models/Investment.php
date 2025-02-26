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
        'result', 
        'status',
        'investment_type',
        'special_section_id'
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
