<?php
// app/Models/SpecialSection.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialSection extends Model
{
    use HasFactory;

    protected $table = 'special_sections';

    protected $fillable = [
        'name',       
        'description',  
        'id',       
        'user_id', 
        'balance'      
            
    ];

  
  
}
