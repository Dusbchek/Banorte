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
        'title',       
        'description',  
        'image',       
        'status',       
        'start_date',   
        'end_date',     
    ];

  
    protected $dates = [
        'start_date',
        'end_date',
    ];
}
