<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentTaskFlujoIndecopi extends Model
{
    use HasFactory;
    protected $fillable = [
        'comment',
        'id_exp',
        'id_task',
        'id_stage',
        'id_flujo',
        'date',
        'entidad',
        'code_user',
        'code_company',
        'metadata',
    ];
}
