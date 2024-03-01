<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AccionesIndecopi;
use App\Models\CommentIndecopi;
use App\Models\CommentTaskFlujoIndecopi;
use App\Models\CommentTaskIndecopi;
use App\Models\Company;
use App\Models\EconomicExpensesIndecopi;
use App\Models\FlujoAsociadoIndecopi;
use App\Models\Indecopi;
use App\Models\Suscripcion;
use App\Models\TaskIndecopi;
use App\Models\User;
use App\Models\UserParte;
use App\Models\WorkFlowTaskIndecopi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IndecopiController extends Controller
{
    //
    public function mostrarExpedientes()
    {
        $expedientes = Indecopi::join('clientes', 'indecopis.id_client', '=', 'clientes.id')
            ->select(
                'indecopis.id',
                'indecopis.tipo',
                'indecopis.numero',
                'indecopis.oficina',
                'indecopis.responsable',
                'indecopis.via_presentacion',
                'indecopis.fecha_inicio',
                'indecopis.estado',
                'indecopis.fecha',
                'indecopis.forma_conclusion',
                'indecopis.partes_procesales1',
                'indecopis.partes_procesales2',
                'indecopis.acciones_realizadas',
                'indecopis.entidad',
                'clientes.name',
                'clientes.last_name',
                'clientes.name_company',
                'clientes.type_contact',
                'clientes.ruc',
                'clientes.email',
                'clientes.phone',
                'acciones_indecopis.fecha',
            )
            ->leftJoin('acciones_indecopis', function ($join) {
                $join->on('indecopis.id', '=', 'acciones_indecopis.id_indecopi')
                    ->where('acciones_indecopis.id', '=', function ($query) {
                        $query->select(DB::raw('MAX(id)'))
                            ->from('acciones_indecopis')
                            ->whereColumn('id_indecopi', 'indecopis.id');
                    });
            })
            ->orderBy('indecopis.id', 'desc')
            ->where('indecopis.code_company', Auth::user()->code_company)
            ->get();

        $dataCompany = Company::where('code_company', Auth::user()->code_company)->first();
        $dataSuscripcion = Suscripcion::where('id', $dataCompany->id_suscripcion)->first();
        $totalExpedientes = Indecopi::where('code_company', Auth::user()->code_company)->count();
        $limitExpedientes = $dataSuscripcion->limit_indecopi;

        return view('dashboard.sistema_expedientes.indecopi.expedientesRegistroExpedientes', compact('expedientes', 'totalExpedientes', 'limitExpedientes'));
    }

    /**
     * Obtener expedientes de INDECOPI para un cliente específico
     *
     * @OA\Get(
     *     path="/api/procesos-indecopi/{idClient}",
     *     tags={"Procesos INDECOPI"},
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
     *         description="Expedientes de INDECOPI del cliente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de expedientes del cliente"),
     *             @OA\Property(property="expedientes", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="tipo", type="string", example="Reclamos"),
     *                     @OA\Property(property="numero", type="string", example="00001210-2020-SAC-CAJ/RC"),
     *                     @OA\Property(property="oficina", type="string", example="ORI CAJAMARCA"),
     *                     @OA\Property(property="responsable", type="string", example="SEGUNDO  VASQUEZ ALVARADO"),
     *                     @OA\Property(property="via_presentacion", type="string", example="RECLAMA VIRTUAL"),
     *                     @OA\Property(property="fecha_inicio", type="string", example="0000-00-00"),
     *                     @OA\Property(property="estado", type="string", example="ARCHIVADO"),
     *                     @OA\Property(property="fecha", type="string", example="2020-10-12"),
     *                     @OA\Property(property="forma_conclusion", type="null"),
     *                     @OA\Property(property="partes_procesales1", type="string", example="[['','DNI','26702196','NARRO LEON WILMA LUISA','PERU, CAJAMARCA, CAJAMARCA, LOS BANOS DEL INCA'],['RECLAMANTE','RUC','20570826825','ERS ENTRENAMIENTO Y CAPACITACION - CAJAMARCA S.A.C.','PERU, CAJAMARCA, CAJAMARCA, CAJAMARCA']]"),
     *                     @OA\Property(property="partes_procesales2", type="string", example="[['RECLAMADO','RUC','20454870591','BS GRUPO S.A.C.','ROMA\\ÑA - CALLE 2 107','PERU, AREQUIPA, AREQUIPA, CAYMA']]"),
     *                     @OA\Property(property="acciones_realizadas", type="null"),
     *                     @OA\Property(property="entidad", type="string", example="Indecopi - Búsqueda por Reclamante/Ciudadano"),
     *                     @OA\Property(property="name", type="string", example=null),
     *                     @OA\Property(property="last_name", type="string", example=null),
     *                     @OA\Property(property="name_company", type="string", example="demo"),
     *                     @OA\Property(property="type_contact", type="string", example="Empresa"),
     *                     @OA\Property(property="ruc", type="string", example="1234568790875746"),
     *                     @OA\Property(property="email", type="string", example="{'email':'alnuawd@sagssdg.adgs','type_email':'Trabajo','email2':null,'type_email2':'Trabajo'}"),
     *                     @OA\Property(property="phone", type="string", example="{'phone':'2354325','type_phone':'Trabajo','phone2':null,'type_phone2':'Trabajo'}")
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
            $expedientes = Indecopi::join('clientes', 'indecopis.id_client', '=', 'clientes.id')
                ->select(
                    'indecopis.id',
                    'indecopis.tipo',
                    'indecopis.numero',
                    'indecopis.oficina',
                    'indecopis.responsable',
                    'indecopis.via_presentacion',
                    'indecopis.fecha_inicio',
                    'indecopis.estado',
                    'indecopis.fecha',
                    'indecopis.forma_conclusion',
                    'indecopis.partes_procesales1',
                    'indecopis.partes_procesales2',
                    'indecopis.acciones_realizadas',
                    'indecopis.entidad',
                    'clientes.name',
                    'clientes.last_name',
                    'clientes.name_company',
                    'clientes.type_contact',
                    'clientes.ruc',
                    'clientes.email',
                    'clientes.phone',
                    'acciones_indecopis.fecha',
                )
                ->leftJoin('acciones_indecopis', function ($join) {
                    $join->on('indecopis.id', '=', 'acciones_indecopis.id_indecopi')
                        ->where('acciones_indecopis.id', '=', function ($query) {
                            $query->select(DB::raw('MAX(id)'))
                                ->from('acciones_indecopis')
                                ->whereColumn('id_indecopi', 'indecopis.id');
                        });
                })
                ->orderBy('indecopis.id', 'desc')
                ->where('indecopis.code_company', Auth::user()->code_company)
                ->where('indecopis.id_client', $idClient)
                ->get();

            $dataCompany = Company::where('code_company', Auth::user()->code_company)->first();
            $dataSuscripcion = Suscripcion::where('id', $dataCompany->id_suscripcion)->first();
            $totalExpedientes = Indecopi::where('code_company', Auth::user()->code_company)
                ->where('id_client', $idClient)
                ->count();
            $limitExpedientes = $dataSuscripcion->limit_indecopi;

            // return view('dashboard.sistema_expedientes.indecopi.expedientesRegistroExpedientes', compact('expedientes', 'totalExpedientes', 'limitExpedientes'));
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

    // GET DATOS EXPEDIENTE
    public function datosExpediente(Request $request)
    {
        $id = $_POST['id'];
        $dataExpediente = Indecopi::join('clientes', 'indecopis.id_client', '=', 'clientes.id')
            ->select(
                'indecopis.id',
                'indecopis.tipo',
                'indecopis.numero',
                'indecopis.oficina',
                'indecopis.responsable',
                'indecopis.via_presentacion',
                'indecopis.fecha_inicio',
                'indecopis.estado',
                'indecopis.fecha',
                'indecopis.forma_conclusion',
                'indecopis.partes_procesales1',
                'indecopis.partes_procesales2',
                'indecopis.acciones_realizadas',
                'indecopis.entidad',
                'indecopis.i_entidad',
                'indecopis.state',
                'indecopis.date_state',
                'clientes.name',
                'clientes.last_name',
                'clientes.name_company',
                'clientes.type_contact',
                'clientes.ruc',
                'clientes.email',
                'clientes.phone',
            )
            ->where('indecopis.id', '=', $id)
            ->get();

        return response($dataExpediente);
    }

    public function updateExpediente(Request $request)
    {

        $datosExpediente = request()->all();

        // dd($request);

        $id = $datosExpediente["e-id"]; // Id del indecopi
        $iNTipo = $datosExpediente["i-n-tipo"]; // código del Indecopi
        $iTipo = $datosExpediente["i-tipo"];
        $iEntidad = $datosExpediente["i-entidad"];
        $iOficina = $datosExpediente["i-oficina"];
        $iResponsable = $datosExpediente["i-responsable"];
        $iViaPresentacion = $datosExpediente["i-via-presentacion"];
        $iFechaInicio = $datosExpediente["i-fecha-inicio"];
        $iEstado = $datosExpediente["i-estado"];
        $iFecha = $datosExpediente["i-fecha"];
        $iFormaConclusion = $datosExpediente["i-forma-conclusion"];

        $state = $datosExpediente["state"];
        $infoState = $datosExpediente["info-date"];

        $data1Values = [];
        $data2Values = [];

        $numRegistros = count($datosExpediente['ip-condicion']);

        // Iterar a través de los registros
        for ($i = 0; $i < $numRegistros; $i++) {
            // Crear un subarray para cada registro
            $registro = [
                $datosExpediente['ip-condicion'][$i],
                $datosExpediente['ip-tipo-doc'][$i],
                $datosExpediente['ip-n-doc'][$i],
                $datosExpediente['ip-nombre'][$i],
                $datosExpediente['ip-pais-etc'][$i],
            ];
            // Agregar el subarray al array de datos finales
            $data1Values[] = $registro;
        }

        $numRegistros2 = count($datosExpediente['ip-condicion2']);

        // Iterar a través de los registros
        for ($i = 0; $i < $numRegistros2; $i++) {
            // Crear un subarray para cada registro
            $registro2 = [
                $datosExpediente['ip-condicion2'][$i],
                $datosExpediente['ip-tipo-doc2'][$i],
                $datosExpediente['ip-n-doc2'][$i],
                $datosExpediente['ip-nombre2'][$i],
                $datosExpediente['ip-direccion2'][$i],
                $datosExpediente['ip-pais-etc2'][$i],
            ];
            // Agregar el subarray al array de datos finales
            $data2Values[] = $registro2;
        }

        // $idUser = Auth()->id();

        // $dataUser = User::where('id', $idUser)->get()->first();

        // $infoProceso = $datosExpediente["info-proceso"];

        // DATA EXPEDIENTE
        $uIndecopi = Indecopi::find($id);
        $uIndecopi->numero = $iNTipo;
        $uIndecopi->tipo = $iTipo;
        $uIndecopi->i_entidad = $iEntidad;
        $uIndecopi->oficina = $iOficina;
        $uIndecopi->responsable = $iResponsable;
        $uIndecopi->via_presentacion = $iViaPresentacion;
        $uIndecopi->fecha_inicio = $iFechaInicio;
        $uIndecopi->estado = $iEstado;
        $uIndecopi->fecha = $iFecha;
        $uIndecopi->forma_conclusion = $iFormaConclusion;
        $uIndecopi->updated_at = now();
        $uIndecopi->state = $state;
        $uIndecopi->date_state = $infoState;
        $uIndecopi->partes_procesales1 = json_encode($data1Values);
        $uIndecopi->partes_procesales2 = json_encode($data2Values);
        $uIndecopi->save();

        // $currentDateTime = Carbon::now();
        // $mysqlDateTime = $currentDateTime->format('Y-m-d H:i:s');
        // Alert::insert([
        //     'date_time'=> $mysqlDateTime,
        //     'message' =>"Se actualizó el expediente  N° ". $codeExp,
        //     'alert' => 'info',
        //     'type' => $entidad,
        //     'id_exp' => $id,
        //     'num_obs' => 0,
        // ]);
        return redirect()->route('sistema_expedientes.indecopi.expedientesRegistroExpedientes')->with('success', '¡Proceso actualizado correctamente!');
    }

    // DELETE
    public function deleteExpediente()
    {
        $id = $_POST['id'];
        $exp = Indecopi::where('id', '=', $id)->get()->first();
        // $currentDateTime = Carbon::now();
        // $mysqlDateTime = $currentDateTime->format('Y-m-d H:i:s');
        $expN = $exp->numero;
        AccionesIndecopi::where('id_indecopi', '=', $id)->delete();
        Indecopi::destroy($id);
        // Alert::insert([
        //     'date_time'=> $mysqlDateTime,
        //     'message' =>"Se eliminó el expediente  N° ". $expN,
        //     'alert' => 'danger',
        //     'type' => 'expediente',
        //     'id_exp' => $id,
        // ]);
        UserParte::where('id_exp', '=', $id)
            ->where('entidad', '=', 'indecopi')
            ->where('code_company', '=', Auth::user()->code_company)
            ->delete();
        TaskIndecopi::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();
        WorkFlowTaskIndecopi::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();
        CommentIndecopi::where('id_indecopi', $id)->delete();
        CommentTaskIndecopi::where('id_exp', $id)->delete();
        CommentTaskFlujoIndecopi::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();
        EconomicExpensesIndecopi::where('id_indecopi', $id)->where('code_company', Auth::user()->code_company)->delete();
        FlujoAsociadoIndecopi::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();

        return response()->json("Eliminado");
    }



    /*
    *
    * Acciones del proceso de Indecopi
    *
    *
    */

    /**
     * Obtener seguimientos de Indecopi para un expediente específico
     *
     * @OA\Get(
     *     path="/api/indecopi/acciones-realizadas",
     *     tags={"Procesos INDECOPI"},
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

    public function viewAcciones(Request $request)
    {

        $id = request()->input('Exp');

        if ($id) {
            $dataUser = User::where('id', Auth()->id())->get()->first();

            $data = Indecopi::join('clientes', 'indecopis.id_client', '=', 'clientes.id')
                ->select(
                    'indecopis.id',
                    'indecopis.tipo',
                    'indecopis.numero',
                    'indecopis.oficina',
                    'indecopis.responsable',
                    'indecopis.via_presentacion',
                    'indecopis.fecha_inicio',
                    'indecopis.estado',
                    'indecopis.fecha',
                    'indecopis.forma_conclusion',
                    'indecopis.partes_procesales1',
                    'indecopis.partes_procesales2',
                    'indecopis.acciones_realizadas',
                    'indecopis.entidad',
                    'indecopis.i_entidad',
                    'indecopis.state',
                    'indecopis.date_state',
                    'clientes.name',
                    'clientes.last_name',
                    'clientes.name_company',
                    'clientes.type_contact',
                    'clientes.ruc',
                    'clientes.email',
                    'clientes.phone',
                    'acciones_indecopis.fecha',
                )
                ->leftJoin('acciones_indecopis', function ($join) {
                    $join->on('indecopis.id', '=', 'acciones_indecopis.id_indecopi')
                        ->where('acciones_indecopis.id', '=', function ($query) {
                            $query->select(DB::raw('MAX(id)'))
                                ->from('acciones_indecopis')
                                ->whereColumn('id_indecopi', 'indecopis.id');
                        });
                })
                ->orderBy('indecopis.id', 'desc')
                ->where('indecopis.id', $id)
                ->where('indecopis.code_company', $dataUser->code_company)
                ->get();

            //withQueryString() => mantener el query
            // $movements = AccionesIndecopi::where('id_indecopi', $id)
            //     ->where('code_company', $dataUser->code_company)
            //     ->orderBy('id', 'desc')
            //     ->paginate(5)
            //     ->withQueryString();
            $movements = AccionesIndecopi::select('acciones_indecopis.*', 'users.name', 'users.lastname')
                ->leftJoin('users', 'acciones_indecopis.code_user', '=', 'users.code_user')
                ->where('acciones_indecopis.id_indecopi', $id)
                ->where('acciones_indecopis.code_company', $dataUser->code_company)
                ->orderBy('acciones_indecopis.id', 'desc')
                ->paginate(5)
                ->withQueryString();


            $comments = CommentIndecopi::where('id_indecopi', $id)->where('code_company', $dataUser->code_company)->orderBy('date', 'asc')->get();

            $groupStages = DB::table('work_flow_task_indecopis')
                ->select('id_workflow', 'id_workflow_stage', 'id_exp', 'nombre_etapa', 'nombre_flujo')
                ->where('id_exp', $id)
                ->groupBy('id_workflow', 'id_workflow_stage', 'id_exp', 'nombre_etapa', 'nombre_flujo')
                ->get();

            $estadoFlujoCount = FlujoAsociadoIndecopi::where('id_exp', $id)->where('table_pertenece', 'flujo')->count();
            $workFlowTaskExpediente = WorkFlowTaskIndecopi::where('id_exp', $id)->get();

            $countAll = WorkFlowTaskIndecopi::where('id_exp', $id)->count();
            $countCheck = WorkFlowTaskIndecopi::where('id_exp', $id)->where('metadata', 'finalizado')->count();
            $countAllTask = TaskIndecopi::where('id_exp', $id)->count();
            $countAllTaskCheck = TaskIndecopi::where('id_exp', $id)->where('metadata', 'finalizado')->count();

            // TOTAL
            $sumAll = $countAll + $countAllTask;
            // TOTAL AVANZADO
            $sumAllCheck = $countCheck + $countAllTaskCheck;
        }




        // return view('dashboard.sistema_expedientes.indecopi.accionesIndecopi', compact('id', 'data', 'movements', 'comments', 'workFlowTaskExpediente', 'groupStages', 'estadoFlujoCount', 'sumAll', 'sumAllCheck'));

        return response()->json([
            'movements' => $movements,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/indecopi/task",
     *     tags={"Procesos INDECOPI"},
     *     summary="Obtener tareas de un expediente",
     *     description="Obtiene las tareas asociadas a un expediente.",
     *     operationId="getTasksIndecopi",
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
            $groupStages = DB::table('work_flow_task_indecopis')
                ->select('id_workflow', 'id_workflow_stage', 'id_exp', 'nombre_etapa', 'nombre_flujo')
                ->where('id_exp', $id)
                ->groupBy('id_workflow', 'id_workflow_stage', 'id_exp', 'nombre_etapa', 'nombre_flujo')
                ->get();

            $estadoFlujoCount = FlujoAsociadoIndecopi::where('id_exp', $id)->where('table_pertenece', 'flujo')->count();
            $workFlowTaskExpediente = WorkFlowTaskIndecopi::where('id_exp', $id)->get();

            $countAll = WorkFlowTaskIndecopi::where('id_exp', $id)->count();
            $countCheck = WorkFlowTaskIndecopi::where('id_exp', $id)->where('metadata', 'finalizado')->count();
            $countAllTask = TaskIndecopi::where('id_exp', $id)->count();
            $countAllTaskCheck = TaskIndecopi::where('id_exp', $id)->where('metadata', 'finalizado')->count();

            // TOTAL
            $sumAll = $countAll + $countAllTask;
            // TOTAL AVANZADO
            $sumAllCheck = $countCheck + $countAllTaskCheck;

            $TaskFinalizado = $countAllTaskCheck;
            $TaskFlujoFinalizado = $countCheck;

            $stageCountEnProgreso = WorkFlowTaskIndecopi::select('w1.*')
                ->from('work_flow_task_indecopis AS w1')
                ->join(DB::raw('(SELECT MAX(id) AS max_id, id_workflow_task
                FROM work_flow_task_indecopis
                WHERE id_exp = ' . $id . '
                AND estado = "En progreso"
                GROUP BY id_workflow_task) AS max_ids'), function ($join) {
                    $join->on('w1.id', '=', 'max_ids.max_id')
                        ->on('w1.id_workflow_task', '=', 'max_ids.id_workflow_task');
                })
                ->where('w1.id_exp', $id)
                ->get();

            $taskExpediente = TaskIndecopi::where('flujo_activo', 'no')
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

        $type = request()->input("type-segui");
        $title = request()->input("title-sigui");
        $date = request()->input("date-segui");
        $descrip = request()->input("descrip-segui");
        $idExp = request()->input("id-exp");
        $archivoAdjunto = request()->file("a-file");
        $urlVideo = request()->input("url-video");

        $datosExpediente = Indecopi::where('id', $idExp)->first();

        // $count = AccionesIndecopi::where('id_indecopi', $idExp)->count();

        $dataUser = User::where('id', Auth()->id())->get()->first();

        if ($archivoAdjunto) {
            $extension = $archivoAdjunto->getClientOriginalExtension();
            $nombreArchivo = $archivoAdjunto->getClientOriginalName();
            $nombreArchivoSinExtension = basename($nombreArchivo, ".{$extension}");

            // Verifica si la extensión del archivo está permitida
            if ($extension == 'pdf' || $extension == 'docx' || $extension == 'doc') {
                // Sube el archivo al almacenamiento
                if (file_exists(public_path('storage/indecopi/' . $datosExpediente->n_expediente . '/' . $nombreArchivo))) {
                    // El archivo existe
                    $nombreArchivo = $nombreArchivoSinExtension . ' - copy.' . $extension;
                    $rutaArchivo = $archivoAdjunto->storeAs('public/indecopi/' . $datosExpediente->n_expediente, $nombreArchivo);
                    $url = Storage::url($rutaArchivo);
                } else {
                    $rutaArchivo = $archivoAdjunto->storeAs('public/indecopi/' . $datosExpediente->n_expediente, $nombreArchivo);
                    $url = Storage::url($rutaArchivo);
                }
            }
        }

        $ultimoRegistro = AccionesIndecopi::where('id_indecopi', $idExp)
            ->orderBy('n_accion', 'desc')
            ->first();

        $newData = [
            'fecha' => $date,
            'accion_realizada' => $title,
            'anotaciones' => $descrip,
            'abog_virtual' => 'no',
            'n_accion' => $ultimoRegistro->n_accion + 1,
            'id_indecopi' => $idExp,
            'code_company' => $dataUser->code_company,
            'code_user' => $dataUser->code_user,
            'metadata' => $url ?? null,
            "video" => $urlVideo ?? null
        ];

        AccionesIndecopi::insert($newData);


        return redirect()->back()->with('success', 'Acción se agregó correctamente');
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

        $exp = Indecopi::where('id', '=', $idExp)->get()->first();

        $codeCompany = auth()->user()->code_company;
        $codeUser = auth()->user()->code_user;

        $dataOld = AccionesIndecopi::where('id', $idM)->where('code_company', $codeCompany)->first();

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
                if (file_exists(public_path('storage/indecopi/' . $exp->n_expediente . '/' . $nombreArchivo))) {
                    // El archivo existe
                    $nombreArchivo = $nombreArchivoSinExtension . ' - copy.' . $extension;
                    $rutaArchivo = $archivoAdjunto->storeAs('public/indecopi/' . $exp->n_expediente, $nombreArchivo);
                    $url = Storage::url($rutaArchivo);
                } else {
                    $rutaArchivo = $archivoAdjunto->storeAs('public/indecopi/' . $exp->n_expediente, $nombreArchivo);
                    $url = Storage::url($rutaArchivo);
                }
            }
        }

        AccionesIndecopi::where('id_indecopi', '=', $idExp)
            ->where('id', '=', $idM)
            ->where('code_company', $codeCompany)
            ->update([
                'accion_realizada' => $title,
                'fecha' => $date,
                'anotaciones' => $descrip,
                'code_user' => $codeUser,
                'update_date' => now(),
                'metadata' => $url ?? $dataOld->metadata,
            ]);

        $value = '¡Acción del proceso se actualizó correctamente!';

        return redirect()->back()->with('success', $value);
    }

    public function datosAccion()
    {
        $id = request()->input('idM');
        $codeCompany = auth()->user()->code_company;
        $data = AccionesIndecopi::where('id', $id)
            ->where('code_company', $codeCompany)
            ->get();

        return response()->json($data);
    }

    public function deleteAccion()
    {
        $id = request()->input('idM');
        $codeCompany = auth()->user()->code_company;
        $dataFollowUp = AccionesIndecopi::where('id', $id)->where('code_company', $codeCompany)->first();

        if ($dataFollowUp && $dataFollowUp->metadata !== null) {
            $borrar_url = str_replace('storage', 'public', $dataFollowUp->metadata);
            Storage::delete($borrar_url);
        }
        if ($dataFollowUp && $dataFollowUp->video !== null) {
            $borrar_url = str_replace('storage', 'public', $dataFollowUp->video);
            Storage::delete($borrar_url);
        }
        AccionesIndecopi::where('id', $id)
            ->where('code_company', $codeCompany)
            ->delete();

        return response()->json("Eliminado");
    }

    // ? SEARCH ACCION REALIZADA
    public function searchAccion(Request $request)
    {
        // $data = FollowUp::where("u_descripcion", "like", $request->texto."%")->orderByDesc('id')->get();
        $texto = $request->texto;
        $id = $request->idExp;

        $codeCompany = auth()->user()->code_company;
        $codeUser = auth()->user()->code_user;

        $movements = AccionesIndecopi::where(function ($query) use ($texto, $id, $codeCompany) {
            $query->where('id_indecopi', '=', $id)
                ->where('code_company', $codeCompany)
                ->where(function ($innerQuery) use ($texto) {
                    $innerQuery->where('accion_realizada', 'like', '%' . $texto . '%')
                        ->orWhere('anotaciones', 'like', '%' . $texto . '%');
                });
        })
            ->orderBy('n_accion', 'desc')
            ->get();



        $comments = CommentIndecopi::where('id_indecopi', $id)->orderBy('date', 'asc')->get();


        return view('dashboard.sistema_expedientes.indecopi.searchAccion', compact('movements', 'texto', 'comments'));
    }

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
        $existExp = Indecopi::where('code_company', '=', $dataUser->code_company)->get()->first();
        $existMovemment = AccionesIndecopi::where('code_company', '=', $dataUser->code_company)->get()->first();
        if ($dataUser && $existExp && $existMovemment) {
            $newData = [
                'comment' => $comment,
                'code_user' => $dataUser->code_user,
                'code_company' => $dataUser->code_company,
                'id_indecopi' => $idExp,
                'id_user' => $idUser,
                'id_accion_r' => $idMovi,
                'date' => $date,
                'type' => 'Principal',
                'metadata' => Null,
            ];

            $insertedId = DB::table('comment_indecopis')->insertGetId($newData);
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
        $dataComment = CommentIndecopi::where('id', $id)->get()->first();
        if ($dataUser->code_company == $dataComment->code_company) {
            CommentIndecopi::where('id', $id)->delete();
            return response()->json('CommentDelete');
        }
        return response()->json('ErrorDelete');
    }
}
