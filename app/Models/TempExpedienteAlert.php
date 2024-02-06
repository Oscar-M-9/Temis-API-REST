<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempExpedienteAlert extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_exp',
        'n_expediente',
        'entidad',
        'date_ult_movi',
        'title_ult_movi',
        'id_ult_movi',
        'n_ult_movi',
        'update_information',
        'data_last',
        'data_pending',
        'estado',
        'metadata',
    ];
}
