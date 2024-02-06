<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempSinoeDocumentAlert extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_exp',
        'n_expediente',
        'entidad',
        'uid',
        'fecha_hora',
        'id_ult_movi',
        'update_information',
        'estado',
        'metadata'	
    ];
}
