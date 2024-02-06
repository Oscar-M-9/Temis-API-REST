<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credenciales extends Model
{
    use HasFactory;
    protected $fillable = [
        'uid',
        'entidad',
        'referencia',
        'user',
        'password',
        'compartida',
        'code_user',
        'code_company',
        'status',
        'metadata',
    ];
}
