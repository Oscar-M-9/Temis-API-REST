<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Companies_Prompts extends Model
{
    use HasFactory;
    protected $table = 'companies_prompts';
    protected $fillable = ['prompts_id', 'companies_id'];
}
