<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'n_seguimiento',
        'fecha_ingreso',
        'fecha_resolucion',
        'resolucion',
        'type_notificacion',
        'acto',
        'folios',
        'fojas',
        'proveido',
        'obs_sumilla',
        'descripcion',
        'file',
        'noti',
        'metadata',
        'documento',
        'video',
        'abog_virtual',
        'update_date',
        'id_exp',
        'u_tipo',
        'u_title',
        'u_date',
        'u_descripcion',
        'code_company',
        'code_user',
    ];

    public function expediente()
    {
        return $this->belongsTo(Expedientes::class);
    }
}
