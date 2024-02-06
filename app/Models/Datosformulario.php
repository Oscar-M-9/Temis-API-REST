<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Datosformulario extends Model
{
    use HasFactory;
    protected $fillable = ['radio','date','text','number','email','select','id_formulario'];

}
