<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentIndecopi extends Model
{
    use HasFactory;
    protected $fillable = [
        'comment',
        'code_user',
        'code_company',
        'id_indecopi',
        'id_user',
        'id_accion_r',
        'date',
        'type',
        'metadata',
    ];
}
