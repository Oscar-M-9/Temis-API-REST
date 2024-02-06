<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnexoNotificationSinoe extends Model
{
    use HasFactory;
    protected $fillable = [
        'tipo',
        'identificacion',
        'n_paginas',
        'documento',
        'id_exp',
        'id_notification',
        'code_user',
        'code_company'
    ];
}
