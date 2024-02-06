<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpedienteSinoe extends Model
{
    use HasFactory;
    protected $fillable = [
        'n_expediente',
        'o_jurisdicional',
        'd_judicial',
        'juez',
        'ubicacion',
        'e_procesal',
        'sumilla',
        'proceso',
        'especialidad',
        'observacion',
        'estado',
        'materia',
        'demanding',
        'defendant',
        'lawyer_responsible',
        'update_date',
        'date_initial',
        'date_conclusion',
        'motivo_conclusion',
        'id_client', 'state',
        'date_state',
        'partes_procesales',
        'abogado_virtual',
        'entidad',
        'code_user',
        'code_company'
    ];

}
