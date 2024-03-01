<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AnexoNotificationSinoe;
use App\Models\Cliente;
use App\Models\CommentNotificationSinoe;
use App\Models\CommentTaskFlujoSinoe;
use App\Models\CommentTaskSinoe;
use App\Models\Company;
use App\Models\DocumentosPresentadosSinoe;
use App\Models\EconomicExpensesSinoe;
use App\Models\EventSuggestion;
use App\Models\ExpedienteSinoe;
use App\Models\FlujoAsociadoExpedienteSinoe;
use App\Models\HistorialDocumentosSinoe;
use App\Models\NotificationSinoe;
use App\Models\SuggestionChatJudicial;
use App\Models\Suscripcion;
use App\Models\TaskExpedienteSinoe;
use App\Models\User;
use App\Models\UserParte;
use App\Models\WorkFlowTaskExpedienteSinoe;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpedienteSinoeController extends Controller
{
    protected $invalidIdResponse;

    public function __construct()
    {
        $this->invalidIdResponse = response()->json(['result' => 'sin resultado'], 400);
    }

    public function mostrarExpedientes()
    {
        $expedientes = ExpedienteSinoe::join('clientes', 'expediente_sinoes.id_client', '=', 'clientes.id')
            ->select(
                'expediente_sinoes.id',
                'expediente_sinoes.n_expediente',
                'expediente_sinoes.materia',
                'expediente_sinoes.proceso',
                'expediente_sinoes.lawyer_responsible',
                'expediente_sinoes.estado',
                'expediente_sinoes.sumilla',
                'expediente_sinoes.date_initial',
                'expediente_sinoes.update_date',
                'clientes.name',
                'clientes.last_name',
                'clientes.name_company',
                'clientes.type_contact',
                'clientes.ruc',
                'clientes.email',
                'clientes.phone',
                'notification_sinoes.fecha',
                'notification_sinoes.u_date',
            )
            ->leftJoin('notification_sinoes', function ($join) {
                $join->on('expediente_sinoes.id', '=', 'notification_sinoes.id_exp')
                    ->where('notification_sinoes.id', '=', function ($query) {
                        $query->select(DB::raw('MAX(id)'))
                            ->from('notification_sinoes')
                            ->where('abog_virtual', 'si')
                            ->whereColumn('id_exp', 'expediente_sinoes.id');
                    });
            })
            ->orderBy('expediente_sinoes.id', 'desc')
            ->where('expediente_sinoes.code_company', Auth::user()->code_company)
            ->whereNull('proceso_penal')
            ->get();

        $dataCompany = Company::where('code_company', Auth::user()->code_company)->first();
        $dataSuscripcion = Suscripcion::where('id', $dataCompany->id_suscripcion)->first();
        $totalExpedientes = ExpedienteSinoe::where('code_company', Auth::user()->code_company)
            ->whereNull('proceso_penal')
            ->count();
        $limitExpedientes = $dataSuscripcion->limit_sinoe;

        return view('dashboard.sistema_expedientes.sinoe.expedientesRegistroExpedientes', compact('expedientes', 'totalExpedientes', 'limitExpedientes'));
    }

    /**
     * Obtener expedientes de SINOE para un cliente específico
     *
     * @OA\Get(
     *     path="/api/procesos-sinoe/{idClient}",
     *     tags={"Procesos SINOE"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="idClient",
     *         in="path",
     *         description="ID del cliente",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Expedientes de SINOE del cliente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de expedientes del cliente"),
     *             @OA\Property(property="expedientes", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="n_expediente", type="string", example="10600-2023-0-1706-JR-PE-01"),
     *                     @OA\Property(property="materia", type="string", example="Materia(s)"),
     *                     @OA\Property(property="proceso", type="string", example="Proceso *"),
     *                     @OA\Property(property="lawyer_responsible", type="string", example="Especialista Legal"),
     *                     @OA\Property(property="estado", type="string", example="Estado *"),
     *                     @OA\Property(property="sumilla", type="string", example="Sumilla"),
     *                     @OA\Property(property="date_initial", type="string", example="2023-12-04"),
     *                     @OA\Property(property="update_date", type="null"),
     *                     @OA\Property(property="name", type="null"),
     *                     @OA\Property(property="last_name", type="null"),
     *                     @OA\Property(property="name_company", type="string", example="demo"),
     *                     @OA\Property(property="type_contact", type="string", example="Empresa"),
     *                     @OA\Property(property="ruc", type="string", example="1234568790875746"),
     *                     @OA\Property(property="email", type="string", example="{'email':'alnuawd@sagssdg.adgs','type_email':'Trabajo','email2':null,'type_email2':'Trabajo'}"),
     *                     @OA\Property(property="phone", type="string", example="{'phone':'2354325','type_phone':'Trabajo','phone2':null,'type_phone2':'Trabajo'}"),
     *                     @OA\Property(property="fecha", type="string", example="2023-10-17 17:40:39"),
     *                     @OA\Property(property="u_date", type="null")
     *                 )
     *             ),
     *             @OA\Property(property="totalExpedientes", type="integer", example=1),
     *             @OA\Property(property="limitExpedientes", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontraron expedientes de la Corte Suprema para el cliente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se encontraron expedientes de la Corte Suprema para el cliente"),
     *             @OA\Property(property="expedientes", type="string", example="[]"),
     *             @OA\Property(property="totalExpedientes", type="integer", example=0),
     *             @OA\Property(property="limitExpedientes", type="string", example=null)
     *         )
     *     )
     * )
     */

    public function mostrarExpedientesCliente($idClient)
    {
        if (is_numeric($idClient)) {
            $expedientes = ExpedienteSinoe::join('clientes', 'expediente_sinoes.id_client', '=', 'clientes.id')
                ->select(
                    'expediente_sinoes.id',
                    'expediente_sinoes.n_expediente',
                    'expediente_sinoes.materia',
                    'expediente_sinoes.proceso',
                    'expediente_sinoes.lawyer_responsible',
                    'expediente_sinoes.estado',
                    'expediente_sinoes.sumilla',
                    'expediente_sinoes.date_initial',
                    'expediente_sinoes.update_date',
                    'clientes.name',
                    'clientes.last_name',
                    'clientes.name_company',
                    'clientes.type_contact',
                    'clientes.ruc',
                    'clientes.email',
                    'clientes.phone',
                    'notification_sinoes.fecha',
                    'notification_sinoes.u_date',
                )
                ->leftJoin('notification_sinoes', function ($join) {
                    $join->on('expediente_sinoes.id', '=', 'notification_sinoes.id_exp')
                        ->where('notification_sinoes.id', '=', function ($query) {
                            $query->select(DB::raw('MAX(id)'))
                                ->from('notification_sinoes')
                                ->where('abog_virtual', 'si')
                                ->whereColumn('id_exp', 'expediente_sinoes.id');
                        });
                })
                ->orderBy('expediente_sinoes.id', 'desc')
                ->where('expediente_sinoes.code_company', Auth::user()->code_company)
                ->where('expediente_sinoes.id_client', $idClient)
                ->whereNull('proceso_penal')
                ->get();

            $dataCompany = Company::where('code_company', Auth::user()->code_company)->first();
            $dataSuscripcion = Suscripcion::where('id', $dataCompany->id_suscripcion)->first();
            $totalExpedientes = ExpedienteSinoe::where('code_company', Auth::user()->code_company)
                ->where('id_client', $idClient)
                ->whereNull('proceso_penal')
                ->count();
            $limitExpedientes = $dataSuscripcion->limit_sinoe;

            return response()->json([
                "status" => true,
                "message" => "Lista de expedientes del cliente",
                'expedientes' => $expedientes,
                'totalExpedientes' => $totalExpedientes,
                'limitExpedientes' => $limitExpedientes,
            ], 200);
        } else {
            return response()->json([
                "status" => false,
                "message" => "No se encontraron expedientes para el cliente",
                'expedientes' => [],
                'totalExpedientes' => 0,
                'limitExpedientes' => null,
            ], 404);
        }
    }

    public function query(Request $request)
    {
        $input = $request->all();

        $data = Cliente::select("name")
            ->where("name", "LIKE", "%{$input['query']}%")
            ->get();

        return response()->json($data);
    }

    public function updateExpediente(Request $request)
    {

        $datosExpediente = request()->all();

        // dd($request);

        $id = $datosExpediente["e-id"]; // Id del Expediente
        $codeExp = $datosExpediente["n-exp"]; // código del Expediente
        $oJuris = $datosExpediente["o-juris"];
        $disJudi = $datosExpediente["dis-judi"];
        $juez = $datosExpediente["juez"];
        $ubi = $datosExpediente["ubi"];
        $eProcesal = $datosExpediente["e-procesal"];
        $sumilla = $datosExpediente["sumilla"];
        $proceso = $datosExpediente["proceso"];
        $especialidad = $datosExpediente["especialidad"];
        $obs = $datosExpediente["obs"];
        $estado = $datosExpediente["estado"];
        $materia = $datosExpediente["case"];

        // $procesos = $datosExpediente["partesProcesales"];
        $lawyerResponsible = $datosExpediente["lawyer-responsible"];
        $dateInitial = $datosExpediente["date-initial"];
        $dateConclusion = $datosExpediente["date-conclusion"];
        $motivoConclusion = $datosExpediente["motivo-conclusion"];
        $state = $datosExpediente["state"];
        $infoDate = $datosExpediente["info-date"];
        $entidad = $datosExpediente["e-entidad"];

        $idUser = Auth()->id();

        $dataUser = User::where('id', $idUser)->get()->first();
        $timestamp = strtotime($dateConclusion);
        if ($timestamp !== false) {
            $dateConclusion = date('Y-m-d', $timestamp);
        } else {
            $dateConclusion = null;
        }

        $partesSeparadas = [];
        $partes = request()->input('parte');
        $tipoPersonas = request()->input('tipoPersona');
        $nombresRazonSocial = request()->input('nombresRazonSocial');


        foreach ($partes as $index => $parte) {
            if (isset($tipoPersonas[$index]) && isset($nombresRazonSocial[$index])) {
                $partesSeparadas[] = [$parte, $tipoPersonas[$index], $nombresRazonSocial[$index]];
            }
        }

        // $infoProceso = $datosExpediente["info-proceso"];

        // DATA EXPEDIENTE
        $uExpediente = ExpedienteSinoe::find($id);
        $uExpediente->n_expediente = $codeExp;
        $uExpediente->o_jurisdicional = $oJuris;
        $uExpediente->d_judicial = $disJudi;
        $uExpediente->juez = $juez;
        $uExpediente->ubicacion = $ubi;
        $uExpediente->e_procesal = $eProcesal;
        $uExpediente->sumilla = $sumilla;
        $uExpediente->proceso = $proceso;
        $uExpediente->especialidad = $especialidad;
        $uExpediente->observacion = $obs;
        $uExpediente->estado = $estado;
        $uExpediente->materia = $materia;
        $uExpediente->lawyer_responsible = $lawyerResponsible;
        $uExpediente->update_date = now();
        $uExpediente->state = $state;
        $uExpediente->date_state = $infoDate;
        $uExpediente->date_initial = $dateInitial;
        $uExpediente->date_conclusion = $dateConclusion;
        $uExpediente->motivo_conclusion = $motivoConclusion;
        $uExpediente->partes_procesales = json_encode($partesSeparadas);
        $uExpediente->entidad = $entidad;
        $uExpediente->code_user = $dataUser->code_user;
        $uExpediente->code_company = $dataUser->code_company;

        $uExpediente->save();

        return redirect()->route('sistema_expedientes.sinoe.expedientesRegistroExpedientes')->with('success', '¡Expediente actualizado correctamente!');
    }

    // GET DATOS EXPEDIENTE
    public function datosExpediente(Request $request)
    {
        $id = $_POST['id'];
        $dataExpediente = ExpedienteSinoe::join('clientes', 'expediente_sinoes.id_client', '=', 'clientes.id')
            ->select(
                'expediente_sinoes.id',
                'expediente_sinoes.n_expediente',
                'expediente_sinoes.o_jurisdicional',
                'expediente_sinoes.d_judicial',
                'expediente_sinoes.juez',
                'expediente_sinoes.ubicacion',
                'expediente_sinoes.e_procesal',
                'expediente_sinoes.sumilla',
                'expediente_sinoes.proceso',
                'expediente_sinoes.especialidad',
                'expediente_sinoes.observacion',
                'expediente_sinoes.estado',
                'expediente_sinoes.materia',
                'expediente_sinoes.demanding',
                'expediente_sinoes.defendant',
                // 'expedientes.info_proceso',
                'expediente_sinoes.lawyer_responsible',
                'expediente_sinoes.update_date',
                'expediente_sinoes.state',
                'expediente_sinoes.date_state',
                'expediente_sinoes.partes_procesales',
                'expediente_sinoes.entidad',
                // 'expedientes.info_date',
                // 'expedientes.initial_date',
                'expediente_sinoes.date_initial',
                'expediente_sinoes.date_conclusion',
                'expediente_sinoes.motivo_conclusion',
                'clientes.name',
                'clientes.last_name',
                'clientes.name_company',
                'clientes.type_contact',
                'clientes.ruc',
                'clientes.email',
                'clientes.phone',
            )
            ->where('expediente_sinoes.id', '=', $id)
            ->whereNull('expediente_sinoes.proceso_penal')
            ->get();

        return response($dataExpediente);
    }

    // DELETE
    public function deleteExpediente()
    {
        $id = $_POST['id'];
        $exp = ExpedienteSinoe::where('id', '=', $id)
            ->whereNull('proceso_penal')
            ->first();
        $dataUser = User::where('id', '=', auth()->id())->get()->first();
        // $currentDateTime = Carbon::now();
        // $mysqlDateTime = $currentDateTime->format('Y-m-d H:i:s');
        $expN = $exp->n_expediente;
        NotificationSinoe::where('id_exp', '=', $id)->delete();
        AnexoNotificationSinoe::where('id_exp', '=', $id)->delete();
        ExpedienteSinoe::destroy($id);
        UserParte::where('id_exp', '=', $id)
            ->where('entidad', '=', 'sinoe')
            ->where('code_company', '=', $dataUser->code_company)
            ->delete();

        TaskExpedienteSinoe::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();
        WorkFlowTaskExpedienteSinoe::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();
        CommentNotificationSinoe::where('id_exp', $id)->delete();
        CommentTaskSinoe::where('id_exp', $id)->delete();
        CommentTaskFlujoSinoe::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();
        EconomicExpensesSinoe::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();
        FlujoAsociadoExpedienteSinoe::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();

        EventSuggestion::where('code_company', Auth::user()->code_company)
            ->where('entidad', 'sinoe')
            ->where('metadata', $id)
            ->delete();

        $directoryToDelete = '/public/sinoe/' . Auth::user()->code_company . "/" . $expN;

        if (Storage::exists($directoryToDelete)) {
            Storage::deleteDirectory($directoryToDelete);
        }

        return response()->json("Eliminado");
    }

    // public function generarReporte()
    // {
    //     return view('dashboard.reporte');
    // }

    /*
     * ************************************************
     *
     *          SEGUIMIENTO DE EXPEDIENTE
     *
     ************************************************* */


    /**
     * Obtener seguimientos de Indecopi para un expediente específico
     *
     * @OA\Get(
     *     path="/api/sinoe/seguimientos-sinoe",
     *     tags={"Procesos SINOE"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="Exp",
     *         in="query",
     *         description="Número de expediente",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Seguimientos del expediente",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="1"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="n_expediente", type="string", example="01667-2022-0-1001-JR-CI-06"),
     *                     @OA\Property(property="materia", type="string", example="DESALOJO"),
     *                     @OA\Property(property="proceso", type="string", example="SUMARISIMO"),
     *                     @OA\Property(property="lawyer_responsible", type="string", example="QUISPE CRUZ ZENILDA-EJECUCION"),
     *                     @OA\Property(property="estado", type="string", example="ARCHIVO DEFINITIVO"),
     *                     @OA\Property(property="sumilla", type="string", example="DEMANDA DE DESALOJO POR OCUPANTE PRECARIO RESTITUCIÓN DE LA HABITACIÓN"),
     *                     @OA\Property(property="date_initial", type="string", example="2022-07-26"),
     *                     @OA\Property(property="update_date", type="null"),
     *                     @OA\Property(property="o_jurisdicional", type="string", example="6° JUZGADO CIVIL - SEDE CENTRAL"),
     *                     @OA\Property(property="d_judicial", type="string", example="CUSCO"),
     *                     @OA\Property(property="juez", type="string", example="LOPEZ TRELLES LUIS ALBERTO"),
     *                     @OA\Property(property="observacion", type="string", example="SE PRESENTA ANEXOS EN COPIAS CERTIFICADAS Y SIMPLES"),
     *                     @OA\Property(property="especialidad", type="string", example="CIVIL"),
     *                     @OA\Property(property="e_procesal", type="string", example="GENERAL"),
     *                     @OA\Property(property="date_conclusion", type="null"),
     *                     @OA\Property(property="ubicacion", type="string", example="ARCHIVO GENERAL"),
     *                     @OA\Property(property="motivo_conclusion", type="string", example="-------"),
     *                     @OA\Property(property="name", type="null"),
     *                     @OA\Property(property="last_name", type="null"),
     *                     @OA\Property(property="name_company", type="string", example="demo"),
     *                     @OA\Property(property="type_contact", type="string", example="Empresa"),
     *                     @OA\Property(property="ruc", type="string", example="1234568790875746"),
     *                     @OA\Property(property="email", type="string", example="{'email':'alnuawd@sagssdg.adgs','type_email':'Trabajo','email2':null,'type_email2':'Trabajo'}"),
     *                     @OA\Property(property="phone", type="string", example="{'phone':'2354325','type_phone':'Trabajo','phone2':null,'type_phone2':'Trabajo'}"),
     *                     @OA\Property(property="fecha_ingreso", type="string", example="2023-07-06 16:00:00"),
     *                     @OA\Property(property="fecha_resolucion", type="null")
     *                 )
     *             ),
     *             @OA\Property(property="movements", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=51),
     *                         @OA\Property(property="n_seguimiento", type="integer", example=50),
     *                         @OA\Property(property="fecha_ingreso", type="null"),
     *                         @OA\Property(property="fecha_resolucion", type="string", example="2023-07-06"),
     *                         @OA\Property(property="resolucion", type="string", example="s/n"),
     *                         @OA\Property(property="type_notificacion", type="string", example="Pta. Cedula Not."),
     *                         @OA\Property(property="acto", type="string", example="AUDIENCIA"),
     *                         @OA\Property(property="folios", type="null"),
     *                         @OA\Property(property="fojas", type="integer", example=1),
     *                         @OA\Property(property="proveido", type="string", example="2023-07-06"),
     *                         @OA\Property(property="obs_sumilla", type="string", example="AUDIENCIA - ACTA DE LANZAMIENTO ESTADO :CONCLUIDA"),
     *                         @OA\Property(property="descripcion", type="string", example="DESCARGADO POR: HUILLCA HUARANCA PILAR"),
     *                         @OA\Property(property="file", type="string", example="Las audiencias no se pueden visualizar por este medio."),
     *                         @OA\Property(property="noti", type="null"),
     *                         @OA\Property(property="abog_virtual", type="string", example="si"),
     *                         @OA\Property(property="u_tipo", type="null"),
     *                         @OA\Property(property="u_title", type="null"),
     *                         @OA\Property(property="u_date", type="null"),
     *                         @OA\Property(property="u_descripcion", type="null"),
     *                         @OA\Property(property="metadata", type="null"),
     *                         @OA\Property(property="documento", type="null"),
     *                         @OA\Property(property="video", type="null"),
     *                         @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                         @OA\Property(property="code_user", type="null"),
     *                         @OA\Property(property="update_date", type="null"),
     *                         @OA\Property(property="id_exp", type="integer", example=1),
     *                         @OA\Property(property="created_at", type="string", example="2023-12-02T04:04:10.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", example="2023-12-02T04:04:10.000000Z")
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://127.0.0.1:8000/api/poder-judicial/seguimientos?Exp=1&page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=11),
     *                 @OA\Property(property="last_page_url", type="string", example="http://127.0.0.1:8000/api/poder-judicial/seguimientos?Exp=1&page=11"),
     *                 @OA\Property(property="links", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="url", type="null"),
     *                         @OA\Property(property="label", type="string", example="&laquo; Previous"),
     *                         @OA\Property(property="active", type="boolean", example=false)
     *                     ),
     *                     @OA\Items(
     *                         @OA\Property(property="url", type="string", example="http://127.0.0.1:8000/api/poder-judicial/seguimientos?Exp=1&page=2"),
     *                         @OA\Property(property="label", type="string", example="Next &raquo;"),
     *                         @OA\Property(property="active", type="boolean", example=false)
     *                     )
     *                 ),
     *                 @OA\Property(property="next_page_url", type="string", example="http://127.0.0.1:8000/api/poder-judicial/seguimientos?Exp=1&page=2"),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1:8000/api/poder-judicial/seguimientos"),
     *                 @OA\Property(property="per_page", type="integer", example=5),
     *                 @OA\Property(property="prev_page_url", type="null"),
     *                 @OA\Property(property="to", type="integer", example=5),
     *                 @OA\Property(property="total", type="integer", example=54)
     *             ),
     *             @OA\Property(property="notify", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="NOTIFICACIÓN 2022-0086258-JR-CI"),
     *                     @OA\Property(property="destinatario", type="string", example="VELARDE SANTOS TEOFILO JUVENAL"),
     *                     @OA\Property(property="fecha_envio", type="string", example="2022-08-12 15:19:00"),
     *                     @OA\Property(property="anexos", type="string", example="RES N° 01 (AUTO ADMISORIO)"),
     *                     @OA\Property(property="forma_entrega", type="string", example=""),
     *                     @OA\Property(property="abog_virtual", type="string", example="si"),
     *                     @OA\Property(property="metadata", type="null"),
     *                     @OA\Property(property="code_company", type="null"),
     *                     @OA\Property(property="code_user", type="null"),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", example="2023-12-02T04:04:05.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", example="2023-12-02T04:04:05.000000Z")
     *                 )
     *             ),
     *             @OA\Property(property="comments", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="comment", type="string", example="QASDFGHJ"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="id_user", type="integer", example=1),
     *                     @OA\Property(property="id_follow_up", type="integer", example=49),
     *                     @OA\Property(property="id_notify", type="integer", example=88),
     *                     @OA\Property(property="date", type="string", example="2023-12-21 20:39:08"),
     *                     @OA\Property(property="type", type="string", example="Notificación"),
     *                     @OA\Property(property="metadata", type="null"),
     *                     @OA\Property(property="created_at", type="null"),
     *                     @OA\Property(property="updated_at", type="null")
     *                 )
     *             ),
     *             @OA\Property(property="workFlowTaskExpediente", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=1),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="tareaa"),
     *                     @OA\Property(property="descripcion", type="string", example="responsabilidad de todos"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=6),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=4),
     *                     @OA\Property(property="fecha_limite", type="string", example="2023-12-27"),
     *                     @OA\Property(property="fecha_alerta", type="string", example="2023-12-25"),
     *                     @OA\Property(property="fecha_finalizada", type="null"),
     *                     @OA\Property(property="attached_files", type="null"),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="prioridad", type="string", example="Baja"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="null"),
     *                     @OA\Property(property="created_at", type="string", example="2023-12-21T15:50:06.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", example="2023-12-21T15:50:06.000000Z")
     *                 ),
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=4),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=3),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="qwerty"),
     *                     @OA\Property(property="descripcion", type="string", example="asdasd"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=8),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=5),
     *                     @OA\Property(property="fecha_limite", type="string", example="2024-01-28"),
     *                     @OA\Property(property="fecha_alerta", type="string", example="2024-01-25"),
     *                     @OA\Property(property="fecha_finalizada", type="null"),
     *                     @OA\Property(property="attached_files", type="null"),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="prioridad", type="string", example="Media"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="null"),
     *                     @OA\Property(property="created_at", type="string", example="2024-01-21T01:11:23.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", example="2024-01-21T01:11:23.000000Z")
     *                 )
     *             ),
     *             @OA\Property(property="groupStages", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=4),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=3),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="qwerty"),
     *                     @OA\Property(property="descripcion", type="string", example="asdasd"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=8),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=5),
     *                     @OA\Property(property="fecha_limite", type="string", example="2024-01-28"),
     *                     @OA\Property(property="fecha_alerta", type="string", example="2024-01-25"),
     *                     @OA\Property(property="fecha_finalizada", type="null"),
     *                     @OA\Property(property="attached_files", type="null"),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="prioridad", type="string", example="Media"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="null"),
     *                     @OA\Property(property="created_at", type="string", example="2024-01-21T01:11:23.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", example="2024-01-21T01:11:23.000000Z")
     *                 )
     *             ),
     *             @OA\Property(property="estadoFlujoCount", type="integer", example=1),
     *             @OA\Property(property="sumAll", type="integer", example=3),
     *             @OA\Property(property="sumAllCheck", type="integer", example=0),
     *             @OA\Property(property="stageCountEnProgreso", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=1),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="tareaa"),
     *                     @OA\Property(property="descripcion", type="string", example="responsabilidad de todos"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=6),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=4),
     *                     @OA\Property(property="fecha_limite", type="string", example="2023-12-27"),
     *                     @OA\Property(property="fecha_alerta", type="string", example="2023-12-25"),
     *                     @OA\Property(property="fecha_finalizada", type="null"),
     *                     @OA\Property(property="attached_files", type="null"),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="prioridad", type="string", example="Baja"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="null"),
     *                     @OA\Property(property="created_at", type="string", example="2023-12-21T15:50:06.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", example="2023-12-21T15:50:06.000000Z")
     *                 )
     *             ),
     *             @OA\Property(property="stageCountConcluida", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=4),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=3),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="qwerty"),
     *                     @OA\Property(property="descripcion", type="string", example="asdasd"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=8),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=5),
     *                     @OA\Property(property="fecha_limite", type="string", example="2024-01-28"),
     *                     @OA\Property(property="fecha_alerta", type="string", example="2024-01-25"),
     *                     @OA\Property(property="fecha_finalizada", type="null"),
     *                     @OA\Property(property="attached_files", type="null"),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="prioridad", type="string", example="Media"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="null"),
     *                     @OA\Property(property="created_at", type="string", example="2024-01-21T01:11:23.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", example="2024-01-21T01:11:23.000000Z")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function viewSeguimiento(Request $request)
    {

        $id = request()->input('Exp');

        if ($id) {

            $dataUser = User::where('id', Auth()->id())->get()->first();

            $data = ExpedienteSinoe::join('clientes', 'expediente_sinoes.id_client', '=', 'clientes.id')
                ->select(
                    'expediente_sinoes.id',
                    'expediente_sinoes.n_expediente',
                    'expediente_sinoes.materia',
                    'expediente_sinoes.proceso',
                    'expediente_sinoes.lawyer_responsible',
                    'expediente_sinoes.estado',
                    'expediente_sinoes.sumilla',
                    'expediente_sinoes.date_initial',
                    'expediente_sinoes.update_date',
                    'expediente_sinoes.o_jurisdicional',
                    'expediente_sinoes.d_judicial',
                    'expediente_sinoes.juez',
                    'expediente_sinoes.observacion',
                    'expediente_sinoes.especialidad',
                    'expediente_sinoes.e_procesal',
                    'expediente_sinoes.date_conclusion',
                    'expediente_sinoes.ubicacion',
                    'expediente_sinoes.motivo_conclusion',
                    'notification_sinoes.u_date',
                    'clientes.name',
                    'clientes.last_name',
                    'clientes.name_company',
                    'clientes.type_contact',
                    'clientes.ruc',
                    'clientes.email',
                    'clientes.phone',
                    'notification_sinoes.fecha'
                )
                ->leftJoin('notification_sinoes', function ($join) {
                    $join->on('expediente_sinoes.id', '=', 'notification_sinoes.id_exp')
                        ->where('notification_sinoes.id', '=', function ($query) {
                            $query->select(DB::raw('MAX(id)'))
                                ->from('notification_sinoes')
                                ->whereColumn('id_exp', 'expediente_sinoes.id');
                        });
                })
                ->orderBy('expediente_sinoes.id', 'desc')
                ->where('expediente_sinoes.id', $id)
                ->where('expediente_sinoes.code_company', $dataUser->code_company)
                ->whereNull('expediente_sinoes.proceso_penal')
                ->get();

            //withQueryString() => mantener el query
            // $movements = NotificationSinoe::where('id_exp', $id)
            //     ->where('code_company', $dataUser->code_company)
            //     ->orderBy('id', 'desc')
            //     ->paginate(5)
            //     ->withQueryString();
            // $movements = NotificationSinoe::select('notification_sinoes.*', 'users.name', 'users.lastname')
            //     ->leftJoin('users', 'notification_sinoes.code_user', '=', 'users.code_user')
            //     ->where('notification_sinoes.id_exp', $id)
            //     ->where('notification_sinoes.code_company', $dataUser->code_company)
            //     ->orderBy('notification_sinoes.id', 'desc')
            //     ->paginate(5)
            //     ->withQueryString();
            $movements = NotificationSinoe::select('notification_sinoes.*', 'users.name', 'users.lastname')
                ->leftJoin('users', 'notification_sinoes.code_user', '=', 'users.code_user')
                ->where('notification_sinoes.id_exp', $id)
                ->where('notification_sinoes.code_company', $dataUser->code_company)
                ->orderBy('notification_sinoes.id', 'desc')
                ->paginate(5)
                ->withQueryString();

            // Obtener los IDs de los movimientos para usarlos en la consulta de notify
            $movementIds = $movements->pluck('id');

            // Consultar los notify para los IDs de los movimientos
            $notify = AnexoNotificationSinoe::whereIn('id_notification', $movementIds)
                ->where('code_company', Auth::user()->code_company)
                ->get();

            // Agregar los notify al resultado de movements
            foreach ($movements as $movement) {
                $movement->notify = $notify->where('id_notification', $movement->id)->all();
            }

            // $notify = AnexoNotificationSinoe::where('id_exp', $id)
            //     ->where('code_company', $dataUser->code_company)
            //     ->get();

            $comments = CommentNotificationSinoe::where('id_exp', $id)->where('code_company', $dataUser->code_company)->orderBy('date', 'asc')->get();

            $groupStages = DB::table('work_flow_task_expediente_sinoes')
                ->select('id_workflow', 'id_workflow_stage', 'id_exp', 'nombre_etapa', 'nombre_flujo')
                ->where('id_exp', $id)
                ->groupBy('id_workflow', 'id_workflow_stage', 'id_exp', 'nombre_etapa', 'nombre_flujo')
                ->get();

            $estadoFlujoCount = FlujoAsociadoExpedienteSinoe::where('id_exp', $id)->where('table_pertenece', 'flujo')->count();
            $workFlowTaskExpediente = WorkFlowTaskExpedienteSinoe::where('id_exp', $id)->get();

            $countAll = WorkFlowTaskExpedienteSinoe::where('id_exp', $id)->count();
            $countCheck = WorkFlowTaskExpedienteSinoe::where('id_exp', $id)->where('metadata', 'finalizado')->count();
            $countAllTask = TaskExpedienteSinoe::where('id_exp', $id)->count();
            $countAllTaskCheck = TaskExpedienteSinoe::where('id_exp', $id)->where('metadata', 'finalizado')->count();

            // TOTAL
            $sumAll = $countAll + $countAllTask;
            // TOTAL AVANZADO
            $sumAllCheck = $countCheck + $countAllTaskCheck;

            $historialData = HistorialDocumentosSinoe::where("code_company", Auth::user()->code_company)
                ->where("id_exp", $id)
                ->get();
            $documentosData = DocumentosPresentadosSinoe::where("code_company", Auth::user()->code_company)
                ->where("id_exp", $id)
                ->get();

            $suggestion = SuggestionChatJudicial::where('code_company', Auth::user()->code_company)
                ->where('id_exp', $id)
                ->where('entidad', 'sinoe')
                ->orderBy('id', 'asc')
                ->get();
        }

        return response()->json([
            'movements' => $movements,
        ], 200);
        // return view('dashboard.sistema_expedientes.sinoe.movimientos', compact('id', 'data', 'movements', 'notify', 'comments', 'workFlowTaskExpediente', 'groupStages', 'estadoFlujoCount', 'sumAll', 'sumAllCheck', 'historialData', 'documentosData', 'suggestion'));
    }

    /**
     * @OA\Get(
     *     path="/api/sinoe/task",
     *     tags={"Procesos SINOE"},
     *     summary="Obtener tareas de un expediente",
     *     description="Obtiene las tareas asociadas a un expediente.",
     *     operationId="getTasksSinoe",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="Exp",
     *         in="query",
     *         description="ID del expediente",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tareas obtenidas correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="workFlowTaskExpediente",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=1),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="tareaa"),
     *                     @OA\Property(property="descripcion", type="string", example="responsabilidad de todos"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=6),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=4),
     *                     @OA\Property(property="fecha_limite", type="string", example="2023-12-27"),
     *                     @OA\Property(property="fecha_alerta", type="string", example="2023-12-25"),
     *                     @OA\Property(property="fecha_finalizada", type="string", example=null),
     *                     @OA\Property(property="attached_files", type="string", example=null),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="prioridad", type="string", example="Baja"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="null"),
     *                     @OA\Property(property="created_at", type="string", example="2023-12-21T15:50:06.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", example="2023-12-21T15:50:06.000000Z")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="groupStages",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=4),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=3),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="qwerty"),
     *                     @OA\Property(property="descripcion", type="string", example="asdasd"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=8),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=5),
     *                     @OA\Property(property="fecha_limite", type="string", example="2024-01-28"),
     *                     @OA\Property(property="fecha_alerta", type="string", example="2024-01-25"),
     *                     @OA\Property(property="fecha_finalizada", type="string", example=null),
     *                     @OA\Property(property="attached_files", type="string", example=null),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="prioridad", type="string", example="Media"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="null"),
     *                     @OA\Property(property="created_at", type="string", example="2024-01-21T01:11:23.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", example="2024-01-21T01:11:23.000000Z")
     *                 )
     *             ),
     *             @OA\Property(property="estadoFlujoCount", type="integer", example=1),
     *             @OA\Property(property="sumAll", type="integer", example=3),
     *             @OA\Property(property="sumAllCheck", type="integer", example=0),
     *             @OA\Property(
     *                 property="stageCountEnProgreso",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=1),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="tareaa"),
     *                     @OA\Property(property="descripcion", type="string", example="responsabilidad de todos"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=6),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=4),
     *                     @OA\Property(property="fecha_limite", type="string", example="2023-12-27"),
     *                     @OA\Property(property="fecha_alerta", type="string", example="2023-12-25"),
     *                     @OA\Property(property="fecha_finalizada", type="string", example=null),
     *                     @OA\Property(property="attached_files", type="string", example=null),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="prioridad", type="string", example="Baja"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="null"),
     *                     @OA\Property(property="created_at", type="string", example="2023-12-21T15:50:06.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", example="2023-12-21T15:50:06.000000Z")
     *                 )
     *             ),
     *             @OA\Property(property="TaskFlujoFinalizado", type="integer", example=0),
     *             @OA\Property(property="TaskFinalizado", type="integer", example=0),
     *             @OA\Property(
     *                 property="taskExpediente",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="flujo_activo", type="string", example="no"),
     *                     @OA\Property(property="id_tarea_flujo", type="integer", example=null),
     *                     @OA\Property(property="etapa_flujo", type="string", example=null),
     *                     @OA\Property(property="transicion_flujo", type="string", example=null),
     *                     @OA\Property(property="data_flujo", type="string", example="null"),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="nombre", type="string", example="tareaa"),
     *                     @OA\Property(property="descripcion", type="string", example="responsabilidad de todos"),
     *                     @OA\Property(property="prioridad", type="string", example="Alta"),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="fecha_limite", type="string", example="2023-12-27"),
     *                     @OA\Property(property="fecha_alerta", type="string", example="2023-12-25"),
     *                     @OA\Property(property="fecha_finalizada", type="string", example=null),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="null"),
     *                     @OA\Property(property="created_at", type="string", example="2023-12-21T15:50:06.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", example="2023-12-21T15:50:06.000000Z"),
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expediente no encontrado"
     *     )
     * )
     */

    public function viewTaskProceso(Request $request)
    {

        $id = request()->input('Exp');

        if ($id) {

            $groupStages = DB::table('work_flow_task_expediente_sinoes')
                ->select('id_workflow', 'id_workflow_stage', 'id_exp', 'nombre_etapa', 'nombre_flujo')
                ->where('id_exp', $id)
                ->groupBy('id_workflow', 'id_workflow_stage', 'id_exp', 'nombre_etapa', 'nombre_flujo')
                ->get();

            $estadoFlujoCount = FlujoAsociadoExpedienteSinoe::where('id_exp', $id)->where('table_pertenece', 'flujo')->count();
            $workFlowTaskExpediente = WorkFlowTaskExpedienteSinoe::where('id_exp', $id)->get();

            $countAll = WorkFlowTaskExpedienteSinoe::where('id_exp', $id)->count();
            $countCheck = WorkFlowTaskExpedienteSinoe::where('id_exp', $id)->where('metadata', 'finalizado')->count();
            $countAllTask = TaskExpedienteSinoe::where('id_exp', $id)->count();
            $countAllTaskCheck = TaskExpedienteSinoe::where('id_exp', $id)->where('metadata', 'finalizado')->count();

            // TOTAL
            $sumAll = $countAll + $countAllTask;
            // TOTAL AVANZADO
            $sumAllCheck = $countCheck + $countAllTaskCheck;

            $TaskFinalizado = $countAllTaskCheck;
            $TaskFlujoFinalizado = $countCheck;

            $stageCountEnProgreso = WorkFlowTaskExpedienteSinoe::select('w1.*')
                ->from('work_flow_task_expediente_sinoes AS w1')
                ->join(DB::raw('(SELECT MAX(id) AS max_id, id_workflow_task
                FROM work_flow_task_expediente_sinoes
                WHERE id_exp = ' . $id . '
                AND estado = "En progreso"
                GROUP BY id_workflow_task) AS max_ids'), function ($join) {
                    $join->on('w1.id', '=', 'max_ids.max_id')
                        ->on('w1.id_workflow_task', '=', 'max_ids.id_workflow_task');
                })
                ->where('w1.id_exp', $id)
                ->get();

            $taskExpediente = TaskExpedienteSinoe::where('flujo_activo', 'no')
                ->where('id_exp', $id)
                ->orderBy('fecha_limite')
                ->get();
        }

        return response()->json([
            'workFlowTaskExpediente' => $workFlowTaskExpediente,
            'groupStages' => $groupStages,
            'estadoFlujoCount' => $estadoFlujoCount,
            'sumAll' => $sumAll,
            'sumAllCheck' => $sumAllCheck,
            'stageCountEnProgreso' => $stageCountEnProgreso,
            'TaskFlujoFinalizado' => $TaskFlujoFinalizado,
            'TaskFinalizado' => $TaskFinalizado,
            'taskExpediente' => $taskExpediente,
        ], 200);
    }

    public function addFollowUp(Request $request)
    {
        // dd($request);

        $tipoSeguimiento = request()->input("type-segui");
        $tituloSeguimiento = request()->input("title-sigui");
        $fechaSeguimiento = request()->input("date-segui");
        $descripcionSeguimiento = request()->input("descrip-segui");
        $numeroExpediente = request()->input("code-exp");
        $idExpediente = request()->input("id-exp");
        $archivoAdjunto = request()->file("a-file");
        $urlVideo = request()->input("url-video");

        $datosExpediente = ExpedienteSinoe::where('id', $idExpediente)
            ->where("code_company", Auth::user()->code_company)
            ->whereNull('proceso_penal')
            ->first();


        if ($archivoAdjunto) {
            $extension = $archivoAdjunto->getClientOriginalExtension();
            $nombreArchivo = $archivoAdjunto->getClientOriginalName();
            $nombreArchivoSinExtension = basename($nombreArchivo, ".{$extension}");

            // Verifica si la extensión del archivo está permitida
            if ($extension == 'pdf' || $extension == 'docx' || $extension == 'doc') {
                // Sube el archivo al almacenamiento
                if (file_exists(public_path('storage/docs/' . $datosExpediente->n_expediente . '/' . $nombreArchivo))) {
                    // El archivo existe
                    $nombreArchivo = $nombreArchivoSinExtension . ' - copy.' . $extension;
                    $rutaArchivo = $archivoAdjunto->storeAs('public/docs/' . $numeroExpediente, $nombreArchivo);
                    $url = Storage::url($rutaArchivo);
                } else {
                    $rutaArchivo = $archivoAdjunto->storeAs('public/docs/' . $numeroExpediente, $nombreArchivo);
                    $url = Storage::url($rutaArchivo);
                }
            }
        }

        $ultimoRegistro = NotificationSinoe::where('id_exp', $idExpediente)
            ->orderBy('n_notificacion', 'desc')
            ->first();

        $datosSeguimiento = collect([
            "u_tipo" => $tipoSeguimiento,
            "u_title" => $tituloSeguimiento,
            "u_date" => $fechaSeguimiento,
            "u_descripcion" => $descripcionSeguimiento,
            "abog_virtual" => "no",
            "id_exp" => $idExpediente,
            "code_company" => $datosExpediente->code_company,
            "code_user" => $datosExpediente->code_user,
            "metadata" => $url ?? null,
            "video" => $urlVideo ?? null
        ]);

        NotificationSinoe::insert($datosSeguimiento->toArray());

        return redirect()->back()->with('success', 'Notificación se agregó correctamente');
    }

    public function updateFollowUp(Request $request)
    {
        // dd($request);

        $idM = request()->input("id-m-e");
        $idExp = request()->input("id-exp");
        $title = request()->input("title-sigui-e");
        $date = request()->input("date-segui-e");
        $descrip = request()->input("descrip-segui-e");
        $archivoAdjunto = request()->file("e-file");

        $exp = ExpedienteSinoe::where('id', '=', $idExp)
            ->whereNull('proceso_penal')
            ->first();

        $codeCompany = auth()->user()->code_company;
        $codeUser = auth()->user()->code_user;

        $dataOld = NotificationSinoe::where('id', $idM)->where('code_company', $codeCompany)->first();

        if ($archivoAdjunto) {
            $extension = $archivoAdjunto->getClientOriginalExtension();
            $nombreArchivo = $archivoAdjunto->getClientOriginalName();
            $nombreArchivoSinExtension = basename($nombreArchivo, ".{$extension}");

            // Verifica si la extensión del archivo está permitida
            if ($extension == 'pdf' || $extension == 'docx' || $extension == 'doc') {
                if ($dataOld && $dataOld->metadata !== null) {
                    $borrar_url = str_replace('storage', 'public', $dataOld->metadata);
                    Storage::delete($borrar_url);
                }
                // Sube el archivo al almacenamiento
                if (file_exists(public_path('storage/docs/' . $exp->n_expediente . '/' . $nombreArchivo))) {
                    // El archivo existe
                    $nombreArchivo = $nombreArchivoSinExtension . ' - copy.' . $extension;
                    $rutaArchivo = $archivoAdjunto->storeAs('public/docs/' . $exp->n_expediente, $nombreArchivo);
                    $url = Storage::url($rutaArchivo);
                } else {
                    $rutaArchivo = $archivoAdjunto->storeAs('public/docs/' . $exp->n_expediente, $nombreArchivo);
                    $url = Storage::url($rutaArchivo);
                }
            }
        }


        NotificationSinoe::where('id_exp', '=', $idExp)
            ->where('id', '=', $idM)
            ->where('code_company', $codeCompany)
            ->update([
                'u_title' => $title,
                'u_date' => $date,
                'u_descripcion' => $descrip,
                'code_user' => $codeUser,
                'update_date' => now(),
                'metadata' => $url ?? $dataOld->metadata,
            ]);

        $value = '¡Movimiento del expediente ' . $exp->n_expediente . ' se actualizó correctamente!';

        return redirect()->back()->with('success', $value);
    }

    // // GET DATOS EXPEDIENTE OBSERVACION
    // public function datosExpedienteObs(Request $request)
    // {
    //     $id = $_POST['id'];
    //     $dataExpediente = DB::table('follow_ups')
    //     ->join('expedientes', 'follow_ups.id_exp', '=', 'expedientes.id')
    //     ->join('clientes', 'expedientes.id_client', '=', 'clientes.id')
    //     ->select(
    //         'expedientes.id',
    //         'expedientes.n_expediente',
    //         'follow_ups.n_seguimiento',
    //         'follow_ups.obs_sumilla',
    //         'follow_ups.acto',
    //         'follow_ups.fecha_resolucion',
    //         'follow_ups.resolucion',
    //         'follow_ups.fojas',
    //         'follow_ups.type_notificacion',
    //         'follow_ups.proveido',
    //         'follow_ups.file',
    //         'clientes.name',
    //         'clientes.type_contact',
    //         'clientes.last_name',
    //         'clientes.name_company',
    //         'clientes.dni',
    //         'clientes.ruc',
    //     )
    //     ->where('follow_ups.id_exp', $id)
    //     ->get();

    //     return response($dataExpediente);
    // }

    public function deleteFollowUp()
    {
        $id = request()->input('idM');
        $codeCompany = auth()->user()->code_company;
        $dataFollowUp = NotificationSinoe::where('id', $id)->where('code_company', $codeCompany)->first();
        if ($dataFollowUp && $dataFollowUp->metadata !== null) {
            $borrar_url = str_replace('storage', 'public', $dataFollowUp->metadata);
            Storage::delete($borrar_url);
        }
        if ($dataFollowUp && $dataFollowUp->video !== null) {
            $borrar_url = str_replace('storage', 'public', $dataFollowUp->video);
            Storage::delete($borrar_url);
        }
        NotificationSinoe::where('id', $id)
            ->where('code_company', $codeCompany)
            ->delete();
        return response()->json("Eliminado");
    }

    public function datosMovimiento()
    {
        $id = request()->input('idM');
        $codeCompany = auth()->user()->code_company;
        $data = NotificationSinoe::where('id', $id)
            ->where('code_company', $codeCompany)
            ->get();

        return response()->json($data);
    }

    // ? SEARCH MOVIMIENTOS
    public function searchSeguimiento(Request $request)
    {
        // $data = FollowUp::where("u_descripcion", "like", $request->texto."%")->orderByDesc('id')->get();
        $texto = $request->texto;
        $id = $request->idExp;

        $codeCompany = auth()->user()->code_company;
        $codeUser = auth()->user()->code_user;

        $movements = NotificationSinoe::where(function ($query) use ($texto, $id, $codeCompany) {
            $query->where('id_exp', '=', $id)
                ->where('code_company', $codeCompany)
                ->where(function ($innerQuery) use ($texto) {
                    $innerQuery->where('u_descripcion', 'like', '%' . $texto . '%')
                        ->orWhere('u_title', 'like', '%' . $texto . '%')
                        ->orWhere('n_notificacion', 'like', '%' . $texto . '%')
                        ->orWhere('n_expediente', 'like', '%' . $texto . '%')
                        ->orWhere('sumilla', 'like', '%' . $texto . '%')
                        ->orWhere('oj', 'like', '%' . $texto . '%');
                });
        })
            ->orderBy('id', 'desc')
            ->get();

        $notify = AnexoNotificationSinoe::where('id_exp', $id)
            ->where('code_company', Auth::user()->code_company)
            ->get();

        $comments = CommentNotificationSinoe::where('id_exp', $id)->orderBy('date', 'asc')->get();


        return view('dashboard.sistema_expedientes.sinoe.searchSeguimiento', compact('movements', 'notify', 'texto', 'comments'));
    }


    /*
    * **********************************
    *
    *       ENTIDAD DE EXPEDIENTE
    *
    ************************************* */

    // public function getExpEntidad(Request $request) {
    //     // $data = Entidad::orderBy('id')->get();
    //     $data = Entidad::where('id', '<>', 7)
    //                     ->orderBy('id')
    //                     ->get();
    //     return response()->json($data);
    // }

    // // ? no automatizado
    // public function getExpEntidad2(Request $request) {
    //     // Filtra los resultados donde el id no sea igual a 2
    //     $data = Entidad::where('id', '<>', 2)
    //                     ->where('id', '<>', 4)
    //                     ->where('id', '<>', 5)
    //                     ->where('id', '<>', 6)
    //                     ->orderBy('id')
    //                     ->get();
    //     return response()->json($data);
    // }

    // /*
    // * ************************************
    // *
    // *       FILTRO DE EXPEDIENTE
    // *
    // ************************************* */

    // public function getExpFiltro() {
    //     $data = FiltroExp::orderBy('distrito_judicial')->get();
    //     return response()->json($data);
    // }


    /*
    * *****************************************
    *
    *       COMENTARIOS EN MOVIMIENTOS
    *
    ****************************************** */

    public function saveComment()
    {

        $idMovi = request()->input("idMovi");
        $idExp = request()->input("idExp");
        // $idNoti= request()->input("idNoti");
        $idUser = Auth()->id();
        $comment = request()->input("comment");
        $date = date("Y-m-d H:i:s");
        $type = request()->input("type"); //principal o notificación

        $dataUser = User::where('id', $idUser)->get()->first();
        $existExp = ExpedienteSinoe::where('code_company', '=', $dataUser->code_company)
            ->whereNull('proceso_penal')
            ->first();
        $existMovemment = NotificationSinoe::where('code_company', '=', $dataUser->code_company)->get()->first();
        if ($dataUser && $existExp && $existMovemment) {
            $newData = [
                'comment' => $comment,
                'code_user' => $dataUser->code_user,
                'code_company' => $dataUser->code_company,
                'id_exp' => $idExp,
                'id_user' => $idUser,
                'id_notification' => $idMovi,
                'date' => $date,
                'type' => $type,
                'metadata' => Null,
            ];

            $insertedId = DB::table('comment_notification_sinoes')->insertGetId($newData);
            return response()->json($newData = [
                'comment' => $comment,
                'user' => $dataUser->name . ', ' . $dataUser->lastname,
                'date' => $date,
                'idC' => $insertedId,
            ]);
        }
        return response()->json('error');
    }


    public function deleteComment()
    {
        $id = request()->input('idC');
        $dataUser = User::where('id', Auth()->id())->get()->first();
        $dataComment = CommentNotificationSinoe::where('id', $id)->get()->first();
        if ($dataUser->code_company == $dataComment->code_company) {
            CommentNotificationSinoe::where('id', $id)->delete();
            return response()->json('CommentDelete');
        }
        return response()->json('ErrorDelete');
    }




    public function deleteStorageSinoeTemp()
    {
        $nSinoe = request()->input("nSinoe");

        if ($nSinoe) {
            $directoryToDelete = '/public/docs/sinoe/' . Auth::user()->code_company . "/" . $nSinoe;

            if (Storage::exists($directoryToDelete)) {
                Storage::deleteDirectory($directoryToDelete);

                return response()->json("Carpeta temporal eliminada");
            }
            return response()->json("No se encontro carpeta temporal");
        }
    }
}
