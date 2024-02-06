<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suscripcion extends Model
{
    use HasFactory;
    protected $fillable = [
        'price',
        'type_suscripcion',
        'dias_suscripcion',
        'accept_terms_and_conditions',
        'current_period_start',
        'current_period_end',
        'cancel_at_period_end',
        'cancel_at',
        'ended_at',
        "limit_credit",
        "limit_users",
        "limit_workflows",
        "access_judicial",
        "access_indecopi",
        "access_suprema",
        "access_sinoe",
        "limit_judicial",
        "limit_indecopi",
        "limit_suprema",
        "limit_sinoe",
        "limit_credencial_sinoe",
        'metadata',
    ];
}
