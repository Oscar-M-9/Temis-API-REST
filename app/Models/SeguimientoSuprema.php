<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeguimientoSuprema extends Model
{
    use HasFactory;
    protected $fillable = [
        'n_seguimiento',
        'fecha',
        'acto',
        'resolucion',
        'fojas',
        'sumilla',
        'desc_usuario',
        'presentante',
        'abog_virtual',
        'u_tipo',
        'u_title',
        'u_date',
        'u_descripcion',
        'metadata',
        'documento',
        'video',
        'code_company',
        'code_user',
        'update_date',
        'id_exp',
    ];
}
