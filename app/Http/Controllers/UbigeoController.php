<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UbigeoController extends Controller
{
    //
    public function getStates()
    {
        $departamentos = DB::table('departamentos')
                    ->orderBy('nombre')
                    ->get()
                    ->toArray();
        return json_encode($departamentos);
    }

    public function getCities()
    {
        $id = request('id');
        $provincias = DB::table('provincias')
                    ->where('departamento_id', $id)
                    ->orderBy('nombre')
                    ->get()
                    ->toArray();

        return json_encode($provincias);
    }

    public function getDistricts()
    {
        $id = request('id');
        $distritos = DB::table('distritos')
                    ->where('provincia_id', $id)
                    ->orderBy('nombre')
                    ->get()
                    ->toArray();

        return json_encode($distritos);
    }

}
