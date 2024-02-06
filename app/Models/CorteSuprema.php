<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorteSuprema extends Model
{
    use HasFactory;
    protected $fillable = [
        'n_expediente',
        'instancia',
        'recurso_sala',
        'fecha_ingreso',
        'organo_procedencia',
        'relator',
        'distrito_judicial',
        'numero_procedencia',
        'secretario',
        'delito',
        'ubicacion',
        'estado',
        'update_date',
        'url_suprema',
        'state',
        'date_state',
        'partes_procesales',
        'vista_causas',
        'abogado_virtual',
        'entidad',
        'code_user',
        'code_company',
        'metadata',
        'id_client',
    ];
}
