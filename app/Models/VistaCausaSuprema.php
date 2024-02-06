<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VistaCausaSuprema extends Model
{
    use HasFactory;
    protected $fillable = [
        'n_vista',
        'fecha_vista',
        'fecha_programacion',
        'sentido_resultado',
        'observacion',
        'tipo_vista',
        'abog_virtual',
        'metadata',
        'code_company',
        'code_user',
        'update_date',
        'id_exp',
    ];
}
