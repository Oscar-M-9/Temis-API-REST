<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Indecopi extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo',
        'numero',
        'oficina',
        'responsable',
        'via_presentacion',
        'fecha_inicio',
        'estado',
        'fecha',
        'forma_conclusion',
        'partes_procesales',
        'acciones_realizadas',
        'state',
        'date_state',
        'i_entidad',
        'entidad',
        'abogado_virtual',
        'id_client',
        'code_user',
        'code_company',
        'metadata',
    ];
}
