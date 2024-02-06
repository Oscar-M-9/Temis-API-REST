<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Expedientes;
use Illuminate\Http\Request;

class ExpReportesController extends Controller
{
    public function mostrarReportes()
    {
        $data = Expedientes::join('clientes', 'expedientes.id_client', '=', 'clientes.id')
            ->select(
                'expedientes.id',
                'expedientes.n_expediente',
                'expedientes.materia',
                'expedientes.process',
                'expedientes.info_proceso',
                'expedientes.lawyer_responsible',
                'expedientes.state',
                'expedientes.info_date',
                'expedientes.info_date',
                'expedientes.initial_date',
                'expedientes.update_date',
                'clientes.name',
                'clientes.last_name',
                'clientes.name_company',
                'clientes.type_contact',
                'clientes.ruc',
                'clientes.email',
                'clientes.phone',
                'clientes.address',
            )
            ->get();
        return view('dashboard.sistema_expedientes.expedientesReportes', compact('data'));
    }
}
