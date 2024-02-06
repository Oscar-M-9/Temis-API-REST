<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSinoe extends Model
{
    use HasFactory;
    protected $fillable = [
        'tipo',
        'n_notificacion',
        'n_expediente',
        'sumilla',
        'oj',
        'fecha',
        'id_exp',
        'uid_credenciales_sinoe',
        'update_date',
        'u_tipo',
        'u_title',
        'u_date',
        'u_descripcion',
        'metadata',
        'documento',
        'video',
        'code_user',
        'code_company'
    ];
}
