<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentosPresentadosSinoe extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_exp',
        'id_historial',
        'descripcion',
        'file_doc',
        'file_cargo',
        'metadata',
        'code_company',
        'code_user'
    ];
}
