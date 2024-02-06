<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertCliente extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_client',
        'fecha_limite',
        'titulo',
        'descripcion',
        'code_company',
        'code_user',
        'metadata',
    ];
}
