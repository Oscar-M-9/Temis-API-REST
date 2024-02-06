<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkFlowTransitions extends Model
{
    use HasFactory;
    protected $fillable = [
        'etapa',
        'condicion',
        'id_workflow',
        'id_workflow_stage',
        'code_user',
        'code_company',
        'metadata',
    ];
}
