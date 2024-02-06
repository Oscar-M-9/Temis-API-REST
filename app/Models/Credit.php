<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    use HasFactory;
    protected $fillable = [
        'used_suggestions',
        'used_writtings',
        'used_training_knowledge',
        'used_prompts',
        'total',
        'code_company',
        'current_period_start',
        'current_period_end',
        'used_bot',
        'used_chatpdf'
    ];
}
