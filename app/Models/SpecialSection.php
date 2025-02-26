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
        'user_id',
        'name',
        'description',
        'balance',
        'created_at',
        'updated_at',
    ];

  
  
}
