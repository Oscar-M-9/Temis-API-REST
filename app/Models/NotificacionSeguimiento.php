<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificacionSeguimiento extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'destinatario',
        'fecha_envio',
        'anexos',
        'forma_entrega',
        'abog_virtual',
        'metadata',
        'id_exp',
        'code_company',
        'code_user',
    ];
}
