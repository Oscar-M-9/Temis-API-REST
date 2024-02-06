<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountGoogleCalendar extends Model
{
    use HasFactory;
    protected $fillable = [
        'access_token',
        'expires_in',
        'refresh_token',
        'scope',
        'token_type',
        'created',
        'client_id',
        'client_secret',
        'id_calendar',
        'iam_email',
        'id_user',
        'code_user',
        'code_company',
        'metadata',
    ];
}
