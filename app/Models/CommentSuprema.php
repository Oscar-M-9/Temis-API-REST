<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentSuprema extends Model
{
    use HasFactory;
    protected $fillable = [
        'comment',
        'code_user',
        'code_company',
        'id_exp',
        'id_user',
        'id_seguimiento',
        'date',
        'type',
        'metadata',
    ];
}
