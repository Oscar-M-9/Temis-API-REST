<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSuggestion extends Model
{
    use HasFactory;
    protected $fillable = [
        'fecha',
        'titulo',
        'descripcion',
        'code_user',
        'code_company',
        'entidad',
        'estado',
        'metadata',
    ];
}
