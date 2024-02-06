<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EconomicExpensesIndecopi extends Model
{
    use HasFactory;
    protected $fillable = [
        'date_time',
        'moneda',
        'mount',
        'titulo',
        'descripcion',
        'status',
        'attached_files',
        'metadata',
        'code_user',
        'code_company',
        'id_indecopi',
        'entidad',
    ];
}
