<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccionesIndecopi extends Model
{
    use HasFactory;
    protected $fillable = [
        'n_accion',
        'fecha',
        'accion_realizada',
        'anotaciones',
        'abog_virtual',
        'metadata',
        'documento',
        'video',
        'code_user',
        'code_company',
        'update_date',
        'id_indecopi',
    ];
}
