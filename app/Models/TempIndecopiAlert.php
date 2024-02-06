<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempIndecopiAlert extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_indecopi',
        'n_expediente',
        'entidad',
        'id_ult_movi',
        'n_ult_movi',
        'update_information',
        'estado',
        'detalle',
        'metadata',
    ];
}
