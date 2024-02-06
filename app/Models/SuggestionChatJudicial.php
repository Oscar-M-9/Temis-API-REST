<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuggestionChatJudicial extends Model
{
    use HasFactory;
    protected $fillable = [
        "id_movi",
        "id_exp",
        "code_exp",
        "chat_user",
        "prompt",
        "entidad",
        "estado",
        "code_user",
        "code_company",
        "metadata",
    ];
}
