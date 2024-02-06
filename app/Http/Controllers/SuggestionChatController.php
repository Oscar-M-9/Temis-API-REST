<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\SuggestionChatJudicial;
use App\Models\User;
use Google\Service\ServiceControl\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuggestionChatController extends Controller
{
    //
    public function addHistoryChat()
    {
        $idMovi = request()->input("id_movi");
        $idExp = request()->input("id_exp");
        $codeExp = request()->input("code_exp");
        $chatUser = request()->input("chat_user");
        $prompt = request()->input("prompt");
        $entidad = request()->input("entidad");

        $dataUser = User::find(auth()->id());
        SuggestionChatJudicial::create([
            "id_movi" => $idMovi,
            "id_exp" => $idExp,
            "code_exp" => $codeExp,
            "chat_user" => $chatUser,
            "prompt" => $prompt,
            "entidad" => $entidad,
            // "estado" => ,
            "code_user" => $dataUser->code_user,
            "code_company" => $dataUser->code_company,
            // "metadata" => ,
        ]);

        if ($chatUser == "message legalbot") {
            $credits = Credit::where('code_company', $dataUser->code_company)->first();
            Credit::where('id', $credits->id)->update([
                'used_suggestions' => DB::raw('used_suggestions + 1'),
                'total' => DB::raw('total + 1'),
            ]);
            // Credit::where('id', $credits->id)->increment('used_suggestions');
            // Credit::where('id', $credits->id)->increment('total');
        }

        return response()->json('success');
    }
}
