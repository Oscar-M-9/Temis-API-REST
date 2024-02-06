<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialDocumentosSinoe extends Model
{
    use HasFactory;
    protected $fillable = [
        'n_expediente',
        'id_exp',
        'n_escrito',
        'distrito_judicial',
        'organo_juris',
        'tipo_doc',
        'fecha_presentacion',
        'sumilla',
        'file_doc',
        'file_cargo',
        'metadata',
        'code_company',
        'code_user'
    ];
}
