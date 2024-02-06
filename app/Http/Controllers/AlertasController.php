<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\CopyExpedientesToTempTable;
use App\Jobs\CopyIndecopiToTempTable;
use App\Jobs\CopySinoeDocumentToTempTable;
use App\Jobs\CopySinoeToTempTable;
use App\Jobs\CopySupremaToTempTable;
use App\Mail\Alert as MailAlert;
use App\Mail\CorreosMailable;
use App\Models\Alert;
use App\Models\Cliente;
use App\Models\ConfiAlert;
use App\Models\CorteSuprema;
use App\Models\Expedientes;
use App\Models\ExpedienteSinoe;
use App\Models\FollowUp;
use App\Models\Indecopi;
use App\Models\TempExpedienteAlert;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AlertasController extends Controller
{
    public function mostrarAlertas()
    {
        $alertCli = Alert::where('type', 'cliente')->orderByDesc('date_time')->limit(10)->get();
        $alertExp = Alert::where('type', 'expediente')->orderByDesc('date_time')->limit(10)->get();
        // $alertObs = Alert::where('type', 'observacion')->orderByDesc('date_time')->limit(10)->get();
        // TODO: VERIFICAR LA CANTIDAD DE DIAS AL ULTIMO EXPEDIENTE
        $alertObs = [];
        $dataDays = ConfiAlert::where('type',  'days')->first();
        $group = FollowUp::select('id_exp')->groupBy('id_exp')->get();
        $days = json_decode($dataDays->data)->days;
        foreach ($group as $val) {
            $idExp = $val->id_exp;
            $cantidadObs = FollowUp::where('id_exp', $idExp)->count();
            $registros = FollowUp::where('id_exp', $idExp)
                ->where('n_seguimiento', '=', ($cantidadObs - 1))
                ->where('fecha_resolucion', '<=', now()->subDays($days))->get();

            if (!$registros->isEmpty()) {
                $expediente = Expedientes::where('id', $idExp)->first(); //? DATOS DEL EXPEDIENTE
                try {
                    if ($expediente->state != "Cerrado") {

                        // ? DATOS DEL CLIENTE
                        $cliente = Cliente::where('id', $expediente->id_client)->first();
                        $nameCliente = "";
                        if ($cliente->type_contact == "Empresa") {
                            $nameCliente = $cliente->name_company;
                        } else {
                            $nameCliente = $cliente->name . ', ' . $cliente->last_name;
                        }
                        // ? FIN DATOS DEL CLIENTE
                        $expN = json_decode(json_decode($expediente)->n_expediente);
                        // TODO: ENVIAR DATOS POR CORREO
                        $dataObs = (object) [
                            'type_client' => $cliente->type_contact,
                            'nombres' => $nameCliente,
                            'n_exp' => $expN,
                            'n_obs' => $cantidadObs,
                            'days' => $days,
                            'id_exp' => $idExp,
                        ];
                        array_push($alertObs, $dataObs);
                    }
                } catch (\Throwable $th) {
                    // *
                }
            }
        }

        return view('dashboard.sistema_expedientes.expedientesAlertas', compact('alertCli', 'alertExp', 'alertObs'));
    }

    public function confiAlert(Request $request)
    {
        $emailP = $request->input('email-p');
        $emails = $request->input('email');
        $days = $request->input('days');

        $jsonEmails = [];

        if ($emails != null) {
            foreach ($emails as $index => $email) {
                $jsonEmails["email-" . $index] = $email;
            }
            $json = json_encode($jsonEmails);
        } else {
            $json = json_encode($jsonEmails);
        }

        confiAlert::where('type', '=', 'email')
            ->update([
                'data' => json_encode(['email' => $emailP]),
            ]);
        confiAlert::where('type', '=', 'emails')
            ->update([
                'data' => $json,
            ]);
        confiAlert::where('type', '=', 'days')
            ->update([
                'data' => json_encode(['days' => $days]),
            ]);
        return redirect()->route('sistema_expedientes.expedientesAlertas')->with('success', 'Los datos se han guardado exitosamente.');
    }

    // ? GET DATA DE CONFIGURACIÓN DE ALERTAS
    public function getConfiAlert()
    {
        $data = ConfiAlert::all();
        return response($data);
    }

    // ! VERIFICA EL REGISTRO PARA MANDAR UNA ALERTA POR CORREO
    public function verificarRegistros()
    {
        $configs = ConfiAlert::whereIn('type', ['email', 'emails', 'days'])->get();

        $dataEmail = $configs->firstWhere('type', 'email');
        $dataEmails = $configs->firstWhere('type', 'emails');
        $dataDays = $configs->firstWhere('type', 'days');

        $email = json_decode($dataEmail->data)->email;
        $emails = json_decode($dataEmails->data);
        $days = json_decode($dataDays->data)->days;

        $group = FollowUp::select('id_exp')->groupBy('id_exp')->get();
        foreach ($group as $val) {
            $idExp = $val->id_exp;

            // * CONTAR LOS REGISTROS Y OBTENER EL NUMERO DE ONSERBACIONES
            $cantidadObs = FollowUp::where('id_exp', $idExp)->count();
            // TODO: VERIFICAR LA CANTIDAD DE DIAS AL ULTIMO EXPEDIENTE
            $registros = FollowUp::where('id_exp', $idExp)
                ->where('n_seguimiento', '=', ($cantidadObs - 1))
                ->where('fecha_resolucion', '<=', now()->subDays($days))->get();


            if (!$registros->isEmpty()) {
                $expediente = Expedientes::where('id', $idExp)->first(); //? DATOS DEL EXPEDIENTE
                //! 'state' in expediente Pendiente Abierto
                try {
                    if ($expediente->state != "Cerrado") {

                        // ? DATOS DEL CLIENTE
                        $cliente = Cliente::where('id', $expediente->id_client)->first();
                        $nameCliente = "";
                        if ($cliente->type_contact == "Empresa") {
                            $nameCliente = $cliente->name_company;
                        } else {
                            $nameCliente = $cliente->name . ', ' . $cliente->last_name;
                        }
                        // ? FIN DATOS DEL CLIENTE
                        // dd($nameCliente);
                        $expN = json_decode(json_decode($expediente)->n_expediente);
                        $c1 = $expN->c1;
                        $c2 = $expN->c2;
                        $c3 = $expN->c3;
                        $c4 = $expN->c4;
                        $c5 = $expN->c5;
                        $c6 = $expN->c6;
                        $c7 = $expN->c7;
                        // TODO: ENVIAR DATOS POR CORREO
                        $mailData = [
                            'type_client' => $cliente->type_contact,
                            'nombres' => $nameCliente,
                            'n_exp' => $c1 . '-' . $c2 . '-' . $c3 . '-' . $c4 . '-' . $c5 . '-' . $c6 . '-' . $c7,
                            'n_obs' => $cantidadObs,
                            'days' => $days,
                            // 'web' => env('APP_URL'),
                        ];
                        Mail::to($email)->send(new MailAlert($mailData));
                        // ? ENVIANDO A LOS DEMAS CORREOS
                        if (!empty($emails)) {
                            foreach ($emails as $valEmail) {
                                Mail::to($valEmail)->send(new MailAlert($mailData));
                            }
                        }
                        echo '<h5>Mensaje enviado correctamente</h5>';
                    }
                } catch (\Throwable $th) {
                    echo '<h5>Error al enviar el mensaje -> ' . $th . '</h5>';
                }
            }
        }
    }

    // ? optimizacion 1
    // public function verificarRegistros()
    // {
    //     $configs = ConfiAlert::whereIn('type', ['email', 'emails', 'days'])->get();

    //     $dataEmail = $configs->firstWhere('type', 'email');
    //     $dataEmails = $configs->firstWhere('type', 'emails');
    //     $dataDays = $configs->firstWhere('type', 'days');

    //     $email = optional(json_decode($dataEmail->data))->email;
    //     $emails = optional(json_decode($dataEmails->data));
    //     $days = optional(json_decode($dataDays->data))->days;

    //     $group = FollowUp::select('id_exp')->groupBy('id_exp')->get();
    //     foreach ($group as $val) {
    //         $idExp = $val->id_exp;

    //         // CONTAR LOS REGISTROS Y OBTENER EL NUMERO DE OBSERVACIONES
    //         $cantidadObs = FollowUp::where('id_exp', $idExp)->count();
    //         // VERIFICAR LA CANTIDAD DE DIAS AL ULTIMO EXPEDIENTE
    //         $registros = FollowUp::where('id_exp', $idExp)
    //             ->where('n_seguimiento', '=', ($cantidadObs - 1))
    //             ->where('initial_date', '<=', now()->subDays($days))
    //             ->get();

    //         if (!$registros->isEmpty()) {
    //             $expediente = Expedientes::where('id', $idExp)->first();
    //             if ($expediente && $expediente->state != "Cerrado") {
    //                 $cliente = Cliente::where('id', $expediente->id_client)->first();
    //                 if ($cliente) {
    //                     $nameCliente = "";
    //                     if ($cliente->type_contact == "Empresa") {
    //                         $nameCliente = $cliente->name_company;
    //                     } else {
    //                         $nameCliente = $cliente->name . ', ' . $cliente->last_name;
    //                     }

    //                     $expN = json_decode($expediente->n_expediente);
    //                     $c1 = $expN->c1;
    //                     $c2 = $expN->c2;
    //                     $c3 = $expN->c3;
    //                     $c4 = $expN->c4;
    //                     $c5 = $expN->c5;
    //                     $c6 = $expN->c6;
    //                     $c7 = $expN->c7;

    //                     $mailData = [
    //                         'type_client' => $cliente->type_contact,
    //                         'nombres' => $nameCliente,
    //                         'n_exp' => $c1 . '-' . $c2 . '-' . $c3 . '-' . $c4 . '-' . $c5 . '-' . $c6 . '-' . $c7,
    //                         'n_obs' => $cantidadObs,
    //                         'days' => $days,
    //                     ];

    //                     if ($email) {
    //                         Mail::to($email)->send(new MailAlert($mailData));
    //                     }

    //                     if (!empty($emails)) {
    //                         foreach ($emails as $valEmail) {
    //                             Mail::to($valEmail)->send(new MailAlert($mailData));
    //                         }
    //                     }

    //                     echo '<h5>Mensaje enviado correctamente</h5>';
    //                 }
    //             }
    //         }
    //     }
    // }

    // public function getExpJudicial()
    // {
    //     $exp = Expedientes::where('abogado_virtual', 'si')->get();
    //     foreach ($exp as $item){
    //         $tempExp = new TempExpedienteAlert();
    //         $tempExp->id_exp = $item->id;
    //         $tempExp->n_expediente = $item->n_expediente;
    //         $tempExp->entidad = "judicial";
    //         $movi = FollowUp::where('id_exp', $item->id)->orderBy('n_seguimiento', 'desc')->get();
    //         // dd($movi[count($movi) - 1]);
    //         $ultMovi = $movi[0];
    //         $lastTitle = "";
    //         $lastValue = "";
    //         // * Validando el ultimo movimiento
    //         if ($ultMovi->fecha_resolucion == null){
    //             // FECHA DE INGRESO
    //             $fechaHora = Carbon::parse($ultMovi->fecha_ingreso);
    //             $lastValue = $fechaHora->format('d/m/Y H:i');
    //             $lastTitle = "Fecha de Ingreso";
    //         }else{
    //             // FECHA DE RESOLUCION
    //             $fecha = Carbon::parse($ultMovi->fecha_resolucion);
    //             $lastValue = $fecha->format('d/m/Y');
    //             $lastTitle = "Fecha de Resolución";
    //         }
    //         $tempExp->date_ult_movi = $lastValue;
    //         $tempExp->title_ult_movi = $lastTitle;
    //         $tempExp->id_ult_movi = $ultMovi->id;
    //         $tempExp->n_ult_movi = (count($movi) - 1);
    //         $tempExp->data_last = json_encode([
    //             'title' => $lastTitle,
    //             'value' => $lastValue,
    //         ]);
    //         // *validando los tres ultimos movimientos por fecha de resolucion
    //         $listPending = [];
    //         $idsPending = [];
    //         $contador = 0;

    //         foreach ($movi as $itemMovi) {
    //             if ($contador < 3 && $itemMovi->fecha_resolucion != null) {
    //                 $fecha = Carbon::parse($itemMovi->fecha_resolucion);
    //                 $pendingDate = $fecha->format('d/m/Y');
    //                 $idsPending[] = $itemMovi->id;
    //                 $listPending[] = $pendingDate;
    //                 $contador++;
    //             }
    //         }

    //         $tempExp->data_pending = json_encode($listPending);
    //         $tempExp->ids_pending = json_encode($idsPending);
    //         $tempExp->estado = 'pendiente';
    //         $tempExp->save();
    //     }
    // }

    // $expedientes = Expedientes::where('abogado_virtual', 'si')->get();

    // foreach ($expedientes as $expediente) {
    //     // Verificar si ya existe un registro con el mismo id_exp
    //     $existingTempExp = TempExpedienteAlert::where('id_exp', $expediente->id)->first();
    //     if (!$existingTempExp){
    //         $tempExp = new TempExpedienteAlert();
    //         $tempExp->id_exp = $expediente->id;
    //         $tempExp->n_expediente = $expediente->n_expediente;
    //         $tempExp->entidad = "judicial";

    //         $movimientos = FollowUp::where('id_exp', $expediente->id)->orderBy('n_seguimiento', 'desc')->get();

    //         if ($movimientos->isEmpty()) {
    //             continue; // Salta este expediente si no tiene movimientos
    //         }

    //         $ultMovi = $movimientos->first();

    //         $lastTitle = $ultMovi->fecha_resolucion ? "Fecha de Resolución" : "Fecha de Ingreso";
    //         $lastValue = $ultMovi->fecha_resolucion ? Carbon::parse($ultMovi->fecha_resolucion)->format('d/m/Y') : Carbon::parse($ultMovi->fecha_ingreso)->format('d/m/Y H:i');

    //         $tempExp->date_ult_movi = $lastValue;
    //         $tempExp->title_ult_movi = $lastTitle;
    //         $tempExp->id_ult_movi = $ultMovi->id;
    //         $tempExp->n_ult_movi = $movimientos->count() - 1;
    //         $tempExp->data_last = json_encode([
    //                                 'title' => $lastTitle,
    //                                 'value' => $lastValue,
    //                             ]);

    //         $pendingDates = [];
    //         $idsPending = [];
    //         $contador = 0;

    //         foreach ($movimientos as $movimiento) {
    //             if ($contador < 3 && $movimiento->fecha_resolucion) {
    //                 $fecha = Carbon::parse($movimiento->fecha_resolucion);
    //                 $pendingDate = $fecha->format('d/m/Y');
    //                 $idsPending[] = $movimiento->id;
    //                 $pendingDates[] = $pendingDate;
    //                 $contador++;
    //             }
    //         }

    //         $tempExp->data_pending = json_encode($pendingDates);
    //         $tempExp->ids_pending = json_encode($idsPending);
    //         $tempExp->estado = 'pendiente';
    //         $tempExp->save();
    //     }
    // }

    public function getExpJudicial()
    {
        $expedientes = Expedientes::where('abogado_virtual', 'si')
            ->join('companies', 'expedientes.code_company', '=', 'companies.code_company')
            ->join('suscripcions', 'companies.id_suscripcion', '=', 'suscripcions.id')
            ->where(function ($query) {
                $query->where('suscripcions.type_suscripcion', '!=', 'Suspensión')
                    ->orWhereNull('suscripcions.id');
            })
            ->get(['expedientes.*']);


        // Divide los expedientes en grupos de 100 expedientes por tarea en cola
        $chunkedExpedientes = $expedientes->chunk(100);

        foreach ($chunkedExpedientes as $expedientesGroup) {
            dispatch(new CopyExpedientesToTempTable($expedientesGroup));
        }

        return 'Tareas en cola para copiar expedientes a la tabla temporal.';
    }

    public function getExpIndecopi()
    {
        // $expedientes = Indecopi::where('abogado_virtual', 'si')->get();
        $expedientes = Indecopi::where('abogado_virtual', 'si')
            ->join('companies', 'indecopis.code_company', '=', 'companies.code_company')
            ->join('suscripcions', 'companies.id_suscripcion', '=', 'suscripcions.id')
            ->where(function ($query) {
                $query->where('suscripcions.type_suscripcion', '!=', 'Suspensión')
                    ->orWhereNull('suscripcions.id');
            })
            ->get(['indecopis.*']);

        // Divide los expedientes en grupos de 100 expedientes por tarea en cola
        $chunkedExpedientes = $expedientes->chunk(100);

        foreach ($chunkedExpedientes as $expedientesGroup) {
            dispatch(new CopyIndecopiToTempTable($expedientesGroup));
        }

        return 'Tareas en cola para copiar expedientes a la tabla temporal.';
    }

    public function getExpSuprema()
    {
        // $expedientes = CorteSuprema::where('abogado_virtual', 'si')->get();
        $expedientes = CorteSuprema::where('abogado_virtual', 'si')
            ->join('companies', 'corte_supremas.code_company', '=', 'companies.code_company')
            ->join('suscripcions', 'companies.id_suscripcion', '=', 'suscripcions.id')
            ->where(function ($query) {
                $query->where('suscripcions.type_suscripcion', '!=', 'Suspensión')
                    ->orWhereNull('suscripcions.id');
            })
            ->get(['corte_supremas.*']);
        // Divide los expedientes en grupos de 100 expedientes por tarea en cola
        $chunkedExpedientes = $expedientes->chunk(100);

        foreach ($chunkedExpedientes as $expedientesGroup) {
            dispatch(new CopySupremaToTempTable($expedientesGroup));
        }

        return 'Tareas en cola para copiar expedientes a la tabla temporal.';
    }

    public function getExpSinoe()
    {
        // $expedientes = ExpedienteSinoe::where('abogado_virtual', 'si')->get();
        $expedientes = ExpedienteSinoe::where('abogado_virtual', 'si')
            ->join('companies', 'expediente_sinoes.code_company', '=', 'companies.code_company')
            ->join('suscripcions', 'companies.id_suscripcion', '=', 'suscripcions.id')
            ->where(function ($query) {
                $query->where('suscripcions.type_suscripcion', '!=', 'Suspensión')
                    ->orWhereNull('suscripcions.id');
            })
            ->get(['expediente_sinoes.*']);

        // Divide los expedientes en grupos de 100 expedientes por tarea en cola
        $chunkedExpedientes = $expedientes->chunk(100);

        foreach ($chunkedExpedientes as $expedientesGroup) {
            dispatch(new CopySinoeToTempTable($expedientesGroup));
        }

        return 'Tareas en cola para copiar expedientes a la tabla temporal.';
    }

    public function getExpSinoeDocumentos()
    {
        $expedientes = ExpedienteSinoe::where('abogado_virtual', 'si')->get();

        // Divide los expedientes en grupos de 100 expedientes por tarea en cola
        $chunkedExpedientes = $expedientes->chunk(100);

        foreach ($chunkedExpedientes as $expedientesGroup) {
            dispatch(new CopySinoeDocumentToTempTable($expedientesGroup));
        }

        return 'Tareas en cola para copiar expedientes a la tabla temporal.';
    }
}
