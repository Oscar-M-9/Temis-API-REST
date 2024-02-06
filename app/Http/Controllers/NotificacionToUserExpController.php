<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserParte;
use Illuminate\Http\Request;

class NotificacionToUserExpController extends Controller
{
    //
    public function getUserNotify() {
        $idExp = request()->input('idExp');
        $entidad = request()->input('ent');

        if ($idExp){
            $dataUser = User::find(auth()->id());
            $userParte = UserParte::where('id_exp', '=', $idExp)
                                    ->where('code_company', '=', $dataUser->code_company)
                                    ->where('entidad', '=', $entidad)
                                    ->get();
            $userParteUserCode =  $userParte->pluck('code_user');
            $userParteAll = User::select('id', 'name', 'lastname')
                                    ->where('code_company', '=', $dataUser->code_company)
                                    ->whereNotIn('code_user', $userParteUserCode)
                                    ->get();
            return response()->json(['data' => $userParte, 'dataAll' => $userParteAll]);
        }
        return response()->json('error');
    }

    public function addUserNotify() {
        $idUser = request()->input('idu');
        $idExp = request()->input('ide');
        $entidad = request()->input('ent');

        $userData = User::find($idUser);
        try {
            $newData =[
                'nombres' => $userData->name,
                'apellidos' => $userData->lastname,
                'email' => $userData->email,
                'categoria' => null,
                'rol' => $userData->type_user,
                'id_exp' => $idExp,
                'code_company' => $userData->code_company,
                'code_user' => $userData->code_user,
                'entidad' => $entidad,
                'metadata' => 'si',
            ];
            UserParte::insert($newData);
            return response()->json($newData);
        } catch (\Throwable $th) {
            return response()->json('error');
        }

    }

    public function deleteUserNotify() {
        $idN = request()->input('idn');
        // $idExp = request()->input('ide');
        // $entidad = request()->input('ent');

        // $userData = User::find($idUser);

        try {
            // UserParte::where('id_exp', $idExp)
            //            ->where('entidad', $entidad)
            //            ->where('code_user', $userData->code_user)
            //            ->where('code_company', $userData->code_company)
            //            ->delete();
            UserParte::where('id', $idN)->delete();
            return response()->json('delete');
        } catch (\Throwable $th) {
            return response()->json('error');
        }
    }
}
