<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkFlowTaskSuprema extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_workflow',
        'id_workflow_stage',
        'id_workflow_task',
        'id_exp',
        'nombre_etapa',
        'nombre_flujo',
        'nombre',
        'descripcion',
        'dias_duracion',
        'dias_antes_venc',
        'fecha_limite',
        'fecha_alerta',
        'fecha_finalizada',
        'attached_files',
        'estado',
        'prioridad',
        'code_user',
        'code_company',
        'metadata',
    ];
}
