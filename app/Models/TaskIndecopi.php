<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskIndecopi extends Model
{
    use HasFactory;
    protected $fillable = [
        'flujo_activo',
        'id_tarea_flujo',
        'etapa_flujo',
        'transicion_flujo',
        'data_flujo',
        'id_exp',
        'nombre',
        'descripcion',
        'prioridad',
        'estado',
        'fecha_limite',
        'fecha_alerta',
        'fecha_finalizada',
        'code_user',
        'code_company',
        'metadata',
    ];
}
