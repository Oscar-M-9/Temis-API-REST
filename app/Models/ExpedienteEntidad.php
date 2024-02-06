<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpedienteEntidad extends Model
{
    use HasFactory;
    protected $fillable = ['code_company', 'code_user', 'entidad', 'id_client', 'id_exp'];
}
