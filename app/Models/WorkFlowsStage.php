<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkFlowsStage extends Model
{
    use HasFactory;
    protected $fillable = [
        'uid',
        'nombre',
        'id_workflow',
        'code_user',
        'code_company',
        'metadata',
    ];
}
