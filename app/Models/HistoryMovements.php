<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryMovements extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_movimiento',
        'id_exp',
        'id_client',
        'entidad',
        'estado',
        'code_company',
        'metadata',
    ];
}
