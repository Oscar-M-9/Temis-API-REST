<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserParte extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombres',
        'apellidos',
        'email',
        'categoria',
        'rol',
        'id_exp',
        'code_company',
        'code_user',
        'entidad',
        'metadata',
    ];
}
