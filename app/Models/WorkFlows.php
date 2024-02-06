<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkFlows extends Model
{
    use HasFactory;
    protected $fillable = [
        'uid',
        'nombre',
        'code_user',
        'code_company',
        'metadata',
    ];
}
