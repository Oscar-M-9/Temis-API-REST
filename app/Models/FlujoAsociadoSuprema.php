<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlujoAsociadoSuprema extends Model
{
    use HasFactory;
    protected $fillable =  [
        'estado',
        'id_exp',
        'id_workflow',
        'id_workflow_stage',
        'date_time',
        'code_user',
        'code_company',
        'etapa',
        'condicion',
        'estado_transition',
        'table_pertenece',
        'metadata',
    ];
}
