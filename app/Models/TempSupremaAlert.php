<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempSupremaAlert extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_suprema',
        'n_expediente',
        'entidad',
        'id_ult_movi',
        'count_movi',
        'n_ult_movi',
        'vista_causa',
        'ids_vista_causa',
        'update_information',
        'estado',
        'url',
        'metadata',
    ];
}
