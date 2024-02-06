<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    protected $fillable = ['type_contact', 'name', 'last_name',  'dni',	'birthdate', 'company', 'name_company', 'ruc',	'email', 'code_user', 'code_company', 'phone', 'address'];
}
