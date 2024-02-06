<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Credenciales;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CredencialesSinoeController extends Controller
{
    //
    public function credencialesSinoe()
    {
        $userData = User::where('id', Auth::user()->id)->first();
        // $credencialesAll = Credenciales::where('code_company')->get();
        $credencialesAll = Credenciales::select(
            'credenciales.id',
            'credenciales.uid',
            'credenciales.entidad',
            'credenciales.referencia',
            'credenciales.user',
            'credenciales.compartida',
            'credenciales.code_user',
            'credenciales.code_company',
            'credenciales.metadata',
            'users.name',
            'users.lastname',
            'users.email',
            'users.rol',
            'users.type_user'
        )
            ->join('users', function ($join) {
                $join->on('credenciales.code_user', '=', 'users.code_user')
                    ->whereColumn('credenciales.code_company', '=', 'users.code_company');
            })
            ->where('credenciales.code_company', $userData->code_company)
            ->orderBy('credenciales.id')
            ->get();
        $usuarios = User::where('id', '<>', $userData->id)->where('code_company', $userData->code_company)->get();
        return view('dashboard.usuarios.credencialesSinoe', compact('usuarios', 'credencialesAll'));
    }

    public function addCredenciales(Request $request)
    {
        // dd($request);
        $entidad = request()->input("c-entidad");
        $nombreReferencia = request()->input("c-nombre-referencia");
        $newUser = request()->input("c-new-user");
        $newPassword = request()->input("c-new-password");
        $usersAll = request()->input("c-compartir-all", "0"); // 0 = false / 1 = true
        $userCompartida = request()->input("c-users-compartida");

        $existName = Credenciales::where('referencia', $nombreReferencia)->first();
        if ($entidad == 0) {
            return back()->withErrors([
                'c-entidad' => 'Seleccione una entidad.',
            ])->withInput();
        }
        $this->validate($request, [
            'c-nombre-referencia' => 'required',
        ]);
        if ($existName) {
            return back()->withErrors([
                'c-nombre-referencia' => 'Ya existe una credencial con este nombre de referencia.',
            ])->withInput();
        }

        $newData = [
            'uid' => DB::raw('UUID_SHORT()'),
            'entidad' => $entidad,
            'referencia' => $nombreReferencia,
            'user' => $newUser,
            'password' => $newPassword,
            'compartida' => $usersAll,
            'code_user' => Auth::user()->code_user,
            'code_company' => Auth::user()->code_company,
            'status' => null,
            'metadata' => $userCompartida ? json_encode($userCompartida) : null,
            'created_at' => Carbon::now(),
        ];
        Credenciales::insert($newData);

        return redirect()->route("usuarios.credencialesSinoe")->with('success', 'Credencial agregado con éxito');
    }

    public function updateCredenciales(Request $request)
    {
        // dd($request);
        $id = request()->input("c-id-edit");
        $entidad = request()->input("c-entidad-edit");
        $nombreReferencia = request()->input("c-nombre-referencia-edit");
        $newUser = request()->input("c-new-user-edit");
        $newPassword = request()->input("c-new-password-edit");
        $usersAll = request()->input("c-compartir-all-edit", "0"); // 0 = false / 1 = true
        $userCompartida = request()->input("c-users-compartida-edit");

        $existName = Credenciales::where('referencia', $nombreReferencia)->count();

        $this->validate($request, [
            'c-nombre-referencia-edit' => 'required',
        ]);
        if ($existName && $existName > 1) {
            return back()->withErrors([
                'c-nombre-referencia-edit' => 'Ya existe una credencial con este nombre de referencia.',
            ])->withInput();
        }

        $upData = [
            'entidad' => $entidad,
            'referencia' => $nombreReferencia,
            'user' => $newUser,
            'password' => $newPassword,
            'compartida' => $usersAll,
            'metadata' => $userCompartida ? json_encode($userCompartida) : null,
            'updated_at' => Carbon::now(),
        ];
        Credenciales::where('id', $id)->where('code_company', Auth::user()->code_company)->update($upData);

        return redirect()->route("usuarios.credencialesSinoe")->with('success', 'Credencial agregado con éxito');
    }

    public function deleteCredenciales()
    {
        $id = request()->input("id");
        $dataExist = Credenciales::where('id', $id)->where('code_company', Auth::user()->code_company)->first();
        if ($dataExist) {
            Credenciales::where('id', $id)->delete();
            return response()->json("Eliminado");
        }
    }

    public function getDataCredencial()
    {
        $id = request()->input("id");
        $data = Credenciales::select(
            'id',
            'entidad',
            'user',
            'password',
            'referencia',
            'uid',
            'compartida',
            'metadata',
        )
            ->where("code_company", Auth::user()->code_company)
            ->where("id", $id)
            ->first();

        $userData = User::where('id', Auth::user()->id)->first();
        $usuarios = User::where('id', '<>', $userData->id)->where('code_company', $userData->code_company)->get();
        return response()->json(["data" => $data, "users" => $usuarios]);
    }

    // public function getCredencialesCompartida() {

    //     // CREDENCIALES CREADAS POR EL USUARIO
    //     $credenciales = Credenciales::where()
    // }

}
