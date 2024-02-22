<?php

namespace App\Http\Controllers;

use App\Models\AccionesIndecopi;
use App\Models\Cliente;
use App\Models\CorteSuprema;
use App\Models\Expedientes;
use App\Models\ExpedienteSinoe;
use App\Models\FollowUp;
use App\Models\HistoryMovements;
use App\Models\Indecopi;
use App\Models\NotificationSinoe;
use App\Models\SeguimientoSuprema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReporteController extends Controller
{

    /**
     * Obtener notificaciones de reportes
     *
     * @OA\Get (
     *     path="/api/notificacion-reportes",
     *     tags={"Notificaciones"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de notificaciones de reportes",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notificaciones"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id_history", type="integer", example=16),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="id_client", type="integer", example=1),
     *                     @OA\Property(property="id_movimiento", type="integer", example=1),
     *                     @OA\Property(property="date_time", type="string", format="date-time", example="2024-01-17T00:15:10.000000Z"),
     *                     @OA\Property(property="estado", type="string", example="no"),
     *                     @OA\Property(property="entidad", type="string", example="Sinoe"),
     *                     @OA\Property(property="exp_n_exp", type="string", example="10600-2023-0-1706-JR-PE-01"),
     *                     @OA\Property(property="movi_accion_realizada", type="string", example="RES. N° UNO MAS ESCRITO DE REQUERIMIENTO DE ACUSACION DIRECTA Y ANEXOS"),
     *                     @OA\Property(property="movi_anotaciones", type="string", example="1 JUZGADO DE INVEST. PREPARATORIA-MBJ JLO"),
     *                     @OA\Property(property="client_type_contact", type="string", example="Empresa"),
     *                     @OA\Property(property="client_name", type="string", example=null),
     *                     @OA\Property(property="client_last_name", type="string", example=null),
     *                     @OA\Property(property="client_name_company", type="string", example="demo"),
     *                     @OA\Property(property="url", type="string", example="/seguimientos-sinoe?Exp=1")
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     */

    public function notificacionReporte()
    {
        $dataHistorial = [];
        $historialAll = HistoryMovements::where('code_company', Auth::user()->code_company)
            ->where('estado', 'no')
            ->orderBy('created_at', 'desc')
            ->take(99)
            ->get();

        foreach ($historialAll as $key => $value) {
            // ? Indecopi
            if ($value->entidad == 'indecopi' || $value->entidad == 'Indecopi') {
                $dataIndecopi = Indecopi::where('id', $value->id_exp)->where('code_company', Auth::user()->code_company)->first();
                $dataMovimiento = AccionesIndecopi::where('id', $value->id_movimiento)->where('code_company', Auth::user()->code_company)->first();
                $dataCliente = Cliente::where('id', $value->id_client)->where('code_company', Auth::user()->code_company)->first();

                $dataHistorial[] = [
                    'id_history' => $value->id,
                    'id_exp' => $value->id_exp,
                    'id_client' => $value->id_client,
                    'id_movimiento' => $value->id_movimiento,
                    'date_time' => $value->created_at,
                    'estado' => $value->estado,
                    'entidad' => 'Indecopi',
                    'exp_n_exp' => $dataIndecopi->numero,
                    'movi_accion_realizada' => $dataMovimiento->accion_realizada,
                    'movi_anotaciones' => $dataMovimiento->anotaciones,
                    'client_type_contact' => $dataCliente->type_contact,
                    'client_name' => $dataCliente->name,
                    'client_last_name' => $dataCliente->last_name,
                    'client_name_company' => $dataCliente->name_company,
                    'url' => '/acciones-realizadas?Exp=' . $value->id_exp,
                ];
            }
            // ? CEJ Judicial
            if ($value->entidad == 'judicial' || $value->entidad == 'CEJ Judicial') {
                $dataExp = Expedientes::where('id', $value->id_exp)->where('code_company', Auth::user()->code_company)->first();
                $dataMovimiento = FollowUp::where('id', $value->id_movimiento)->where('code_company', Auth::user()->code_company)->first();
                $dataCliente = Cliente::where('id', $value->id_client)->where('code_company', Auth::user()->code_company)->first();

                $dataHistorial[] = [
                    'id_history' => $value->id,
                    'id_exp' => $value->id_exp,
                    'id_client' => $value->id_client,
                    'id_movimiento' => $value->id_movimiento,
                    'date_time' => $value->created_at,
                    'estado' => $value->estado,
                    'entidad' => 'CEJ Judicial',
                    'exp_n_exp' => $dataExp->n_expediente,
                    'movi_accion_realizada' => $dataMovimiento->obs_sumilla,
                    'movi_anotaciones' => $dataMovimiento->descripcion,
                    'client_type_contact' => $dataCliente->type_contact,
                    'client_name' => $dataCliente->name,
                    'client_last_name' => $dataCliente->last_name,
                    'client_name_company' => $dataCliente->name_company,
                    'url' => '/seguimientos?Exp=' . $value->id_exp,
                ];
            }
            // ? CEJ suprema
            if ($value->entidad == 'suprema' || $value->entidad == 'CEJ Suprema') {
                $dataExp = CorteSuprema::where('id', $value->id_exp)->where('code_company', Auth::user()->code_company)->first();
                $dataMovimiento = SeguimientoSuprema::where('id', $value->id_movimiento)->where('code_company', Auth::user()->code_company)->first();
                $dataCliente = Cliente::where('id', $value->id_client)->where('code_company', Auth::user()->code_company)->first();

                $dataHistorial[] = [
                    'id_history' => $value->id,
                    'id_exp' => $value->id_exp,
                    'id_client' => $value->id_client,
                    'id_movimiento' => $value->id_movimiento,
                    'date_time' => $value->created_at,
                    'estado' => $value->estado,
                    'entidad' => 'CEJ Suprema',
                    'exp_n_exp' => $dataExp->n_expediente,
                    'movi_accion_realizada' => $dataMovimiento->sumilla,
                    'movi_anotaciones' => $dataMovimiento->desc_usuario,
                    'client_type_contact' => $dataCliente->type_contact,
                    'client_name' => $dataCliente->name,
                    'client_last_name' => $dataCliente->last_name,
                    'client_name_company' => $dataCliente->name_company,
                    'url' => '/seguimientos-corte-suprema?Exp=' . $value->id_exp,
                ];
            }
            // ? Sinoe
            if ($value->entidad == 'sinoe' || $value->entidad == 'Sinoe') {
                $dataExp = ExpedienteSinoe::where('id', $value->id_exp)->where('code_company', Auth::user()->code_company)->first();
                $dataMovimiento = NotificationSinoe::where('id', $value->id_movimiento)->where('code_company', Auth::user()->code_company)->first();
                $dataCliente = Cliente::where('id', $value->id_client)->where('code_company', Auth::user()->code_company)->first();

                $dataHistorial[] = [
                    'id_history' => $value->id,
                    'id_exp' => $value->id_exp,
                    'id_client' => $value->id_client,
                    'id_movimiento' => $value->id_movimiento,
                    'date_time' => $value->created_at,
                    'estado' => $value->estado,
                    'entidad' => 'Sinoe',
                    'exp_n_exp' => $dataExp->n_expediente,
                    'movi_accion_realizada' => $dataMovimiento->sumilla,
                    'movi_anotaciones' => $dataMovimiento->oj,
                    'client_type_contact' => $dataCliente->type_contact,
                    'client_name' => $dataCliente->name,
                    'client_last_name' => $dataCliente->last_name,
                    'client_name_company' => $dataCliente->name_company,
                    'url' => '/seguimientos-sinoe?Exp=' . $value->id_exp,
                ];
            }
        }

        return response()->json([
            "status" => true,
            "message" => "Notificaciones",
            "data" => $dataHistorial
        ], 200);
    }

    /**
     * Actualizar estado de notificación de reporte por ID
     *
     * @OA\Put (
     *     path="/api/update-estado-history-movements/{id}",
     *     tags={"Notificaciones"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la notificación",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estado de la notificación actualizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="id", type="string", example="16"),
     *             @OA\Property(property="message", type="string", example="El estado de la notificación ha sido actualizado con éxito")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Recurso no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="id", type="string", example="1"),
     *             @OA\Property(property="message", type="string", example="Recurso no encontrado")
     *         )
     *     ),
     * )
     *
     */

    public function updateEstadoHistoryMovements($id)
    {

        $exist = HistoryMovements::where('id', $id)
            ->where('code_company', Auth::user()->code_company)
            ->first();
        if ($exist) {
            HistoryMovements::where('id', $id)
                ->update([
                    'estado' => 'si'
                ]);
            return response()->json([
                'status' => true,
                'id' => $id,
                'message' => 'El estado de la notificación ha sido actualizado con éxito'
            ], 200);
        }
        return response()->json([
            'status' => false,
            'id' => $id,
            'message' => 'Recurso no encontrado'
        ], 404);
    }
}
