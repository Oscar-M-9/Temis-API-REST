<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempDocumentPresentado extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_exp',
        'uid',
        'n_expediente',
        'entidad',
        'estado',
        'metadata',
        'code_company',
        'code_user'
    ];
}
