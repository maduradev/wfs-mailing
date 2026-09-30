<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterNumberCounter extends Model
{
    protected $fillable = ['letter_type', 'year', 'next_number'];
}
