<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkFlowsTask extends Model
{
    use HasFactory;
    protected $fillable = [
        'uid',
        'nombre',
        'descripcion',
        'dias_duracion',
        'dias_antes_venc',
        'attached_files',
        'estado',
        'prioridad',
        'id_workflow',
        'id_workflow_stage',
        'code_user',
        'code_company',
        'metadata',
    ];
}
