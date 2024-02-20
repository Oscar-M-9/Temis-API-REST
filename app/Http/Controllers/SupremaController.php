<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CommentSuprema;
use App\Models\CommentTaskFlujoSuprema;
use App\Models\CommentTaskSuprema;
use App\Models\Company;
use App\Models\CorteSuprema;
use App\Models\EconomicExpensesSuprema;
use App\Models\FlujoAsociadoSuprema;
use App\Models\SeguimientoSuprema;
use App\Models\Suscripcion;
use App\Models\TaskSuprema;
use App\Models\User;
use App\Models\UserParte;
use App\Models\VistaCausaSuprema;
use App\Models\WorkFlowTaskSuprema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SupremaController extends Controller
{
    //
    public function mostrarExpedientes()
    {
        $expedientes = CorteSuprema::join('clientes', 'corte_supremas.id_client', '=', 'clientes.id')
            ->select(
                'corte_supremas.id',
                'corte_supremas.n_expediente',
                'corte_supremas.instancia',
                'corte_supremas.recurso_sala',
                'corte_supremas.fecha_ingreso',
                'corte_supremas.organo_procedencia',
                'corte_supremas.relator',
                'corte_supremas.distrito_judicial',
                'corte_supremas.numero_procedencia',
                'corte_supremas.secretario',
                'corte_supremas.delito',
                'corte_supremas.ubicacion',
                'corte_supremas.estado',
                'corte_supremas.entidad',
                'corte_supremas.url_suprema',
                'corte_supremas.partes_procesales',
                'corte_supremas.date_state',
                'corte_supremas.state',
                'clientes.name',
                'clientes.last_name',
                'clientes.name_company',
                'clientes.type_contact',
                'clientes.ruc',
                'clientes.email',
                'clientes.phone',
                'seguimiento_supremas.fecha',
                'seguimiento_supremas.u_date',
            )
            ->leftJoin('seguimiento_supremas', function ($join) {
                $join->on('corte_supremas.id', '=', 'seguimiento_supremas.id_exp')
                    ->where('seguimiento_supremas.id', '=', function ($query) {
                        $query->select(DB::raw('MAX(id)'))
                            ->from('seguimiento_supremas')
                            ->whereColumn('id_exp', 'corte_supremas.id');
                    });
            })
            ->orderBy('corte_supremas.id', 'desc')
            ->where('corte_supremas.code_company', Auth::user()->code_company)
            ->get();

        $dataCompany = Company::where('code_company', Auth::user()->code_company)->first();
        $dataSuscripcion = Suscripcion::where('id', $dataCompany->id_suscripcion)->first();
        $totalExpedientes = CorteSuprema::where('code_company', Auth::user()->code_company)->count();
        $limitExpedientes = $dataSuscripcion->limit_suprema;

        return view('dashboard.sistema_expedientes.suprema.expedientesRegistroExpedientes', compact('expedientes', 'totalExpedientes', 'limitExpedientes'));
    }

    /**
     * Obtener expedientes de la Corte Suprema para un cliente específico
     *
     * @OA\Get(
     *     path="/api/procesos-corte-suprema/{idClient}",
     *     tags={"Procesos CEJ SUPREMA"},
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
     *         description="Expedientes de la Corte Suprema del cliente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de expedientes del cliente"),
     *             @OA\Property(property="expedientes", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=9),
     *                     @OA\Property(property="n_expediente", type="string", example="02398-2010-0-5001-SU-PE-01"),
     *                     @OA\Property(property="instancia", type="string", example="SALA SUPREMA PENAL PERMANENTE"),
     *                     @OA\Property(property="recurso_sala", type="string", example="QUEJA NCPP 00072 - 2010"),
     *                     @OA\Property(property="fecha_ingreso", type="string", example="2010-08-08 00:00:00"),
     *                     @OA\Property(property="organo_procedencia", type="string", example="2° SALA PENAL DE APELACIONES"),
     *                     @OA\Property(property="relator", type="string", example="VERA LUNA  SUSANA LOURDES"),
     *                     @OA\Property(property="distrito_judicial", type="string", example="LA LIBERTAD"),
     *                     @OA\Property(property="numero_procedencia", type="string", example="0000383 - 2010"),
     *                     @OA\Property(property="secretario", type="string", example="PILAR ROXANA SALAS CAMPOS"),
     *                     @OA\Property(property="delito", type="string", example="Formas agravadas de tráfico ilícito de drogas."),
     *                     @OA\Property(property="ubicacion", type="string", example="MESA DE PARTES DE SALA SUPREMA"),
     *                     @OA\Property(property="estado", type="string", example="EN TRAMITE"),
     *                     @OA\Property(property="entidad", type="string", example="Consulta de Expedientes Judiciales Supremo (CEJ Supremo)"),
     *                     @OA\Property(property="url_suprema", type="string", example="https://apps.pj.gob.pe/cejSupremo/Expediente/DetalleExpediente.aspx?data=EIuEtHaCP5iTGggJasXOvISNOS5jGszLiJaBeTjIUz1dEKBX2ApCyZCZ7GRpHbZZFNhXV8GKAfgV9tzs7uXfJal1nwKETXP4rCmv5F4ecN0HeMfM4NojZ3iHO9rU%2fkZ5%2feGIRvjt6gr7J3noNO2sPmJhmoNLQV4t%2fhf1V5DD9%2f2R1FFVjbkr16K83QY%2bEgUWrGSnBIByYT6V8a2kpUtxCCbjL4Ba5YfBkfKSkGfbaeYf"),
     *                     @OA\Property(property="partes_procesales", type="string", example="[['AGRAVIADO ','ESTADO',''],['QUEJOSO ','SALOMON SANTACRUZ SANTACRUZ','Recurrente'],['IMPUTADO ','SALOMON SANTACRUZ SANTACRUZ',''],['MINISTERIO PUBLICO ','MINISTERIO PUBLICO','']]"),
     *                     @OA\Property(property="date_state", type="string", example="2023-12-19"),
     *                     @OA\Property(property="state", type="string", example="Pendiente"),
     *                     @OA\Property(property="name", type="string", example=null),
     *                     @OA\Property(property="last_name", type="string", example=null),
     *                     @OA\Property(property="name_company", type="string", example="demo"),
     *                     @OA\Property(property="type_contact", type="string", example="Empresa"),
     *                     @OA\Property(property="ruc", type="string", example="1234568790875746"),
     *                     @OA\Property(property="email", type="string", example="{'email':'alnuawd@sagssdg.adgs','type_email':'Trabajo','email2':null,'type_email2':'Trabajo'}"),
     *                     @OA\Property(property="phone", type="string", example="{'phone':'2354325','type_phone':'Trabajo','phone2':null,'type_phone2':'Trabajo'}"),
     *                     @OA\Property(property="fecha", type="string", example="2012-10-10"),
     *                     @OA\Property(property="u_date", type="string", example=null)
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

            $expedientes = CorteSuprema::join('clientes', 'corte_supremas.id_client', '=', 'clientes.id')
                ->select(
                    'corte_supremas.id',
                    'corte_supremas.n_expediente',
                    'corte_supremas.instancia',
                    'corte_supremas.recurso_sala',
                    'corte_supremas.fecha_ingreso',
                    'corte_supremas.organo_procedencia',
                    'corte_supremas.relator',
                    'corte_supremas.distrito_judicial',
                    'corte_supremas.numero_procedencia',
                    'corte_supremas.secretario',
                    'corte_supremas.delito',
                    'corte_supremas.ubicacion',
                    'corte_supremas.estado',
                    'corte_supremas.entidad',
                    'corte_supremas.url_suprema',
                    'corte_supremas.partes_procesales',
                    'corte_supremas.date_state',
                    'corte_supremas.state',
                    'clientes.name',
                    'clientes.last_name',
                    'clientes.name_company',
                    'clientes.type_contact',
                    'clientes.ruc',
                    'clientes.email',
                    'clientes.phone',
                    'seguimiento_supremas.fecha',
                    'seguimiento_supremas.u_date',
                )
                ->leftJoin('seguimiento_supremas', function ($join) {
                    $join->on('corte_supremas.id', '=', 'seguimiento_supremas.id_exp')
                        ->where('seguimiento_supremas.id', '=', function ($query) {
                            $query->select(DB::raw('MAX(id)'))
                                ->from('seguimiento_supremas')
                                ->whereColumn('id_exp', 'corte_supremas.id');
                        });
                })
                ->orderBy('corte_supremas.id', 'desc')
                ->where('corte_supremas.code_company', Auth::user()->code_company)
                ->where('corte_supremas.id_client', $idClient)
                ->get();

            $dataCompany = Company::where('code_company', Auth::user()->code_company)->first();
            $dataSuscripcion = Suscripcion::where('id', $dataCompany->id_suscripcion)->first();
            $totalExpedientes = CorteSuprema::where('code_company', Auth::user()->code_company)
                ->where('id_client', $idClient)
                ->count();
            $limitExpedientes = $dataSuscripcion->limit_suprema;

            // return view('dashboard.sistema_expedientes.suprema.expedientesRegistroExpedientes', compact('expedientes', 'totalExpedientes', 'limitExpedientes'));
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
        $dataExpediente = CorteSuprema::join('clientes', 'corte_supremas.id_client', '=', 'clientes.id')
            ->select(
                'corte_supremas.id',
                'corte_supremas.n_expediente',
                'corte_supremas.instancia',
                'corte_supremas.recurso_sala',
                'corte_supremas.fecha_ingreso',
                'corte_supremas.organo_procedencia',
                'corte_supremas.relator',
                'corte_supremas.distrito_judicial',
                'corte_supremas.numero_procedencia',
                'corte_supremas.secretario',
                'corte_supremas.delito',
                'corte_supremas.ubicacion',
                'corte_supremas.estado',
                'corte_supremas.entidad',
                'corte_supremas.url_suprema',
                'corte_supremas.partes_procesales',
                'corte_supremas.date_state',
                'corte_supremas.state',
                'clientes.name',
                'clientes.last_name',
                'clientes.name_company',
                'clientes.type_contact',
                'clientes.ruc',
                'clientes.email',
                'clientes.phone',
            )
            ->where('corte_supremas.id', '=', $id)
            ->get();

        return response($dataExpediente);
    }

    public function updateExpediente(Request $request)
    {

        $datosExpediente = request()->all();

        // dd($request);

        $id = $datosExpediente["e-id"];
        $nExpSuprema = $datosExpediente["m-n-exp-suprema"];
        $instanciaSuprema = $datosExpediente["m-instancia-suprema"];
        $recursoSalaSuprema = $datosExpediente["m-recurso-sala-suprema"];
        $fechaIngresoSuprema = $datosExpediente["m-fecha-ingreso-suprema"];
        $organoProcedenciaSuprema = $datosExpediente["m-organo-procedencia-suprema"];
        $relatorSuprema = $datosExpediente["m-relator-suprema"];
        $distritoJudicialSuprema = $datosExpediente["m-distrito-judicial-suprema"];
        $nProcedenciaSuprema = $datosExpediente["m-numero-procedencia-suprema"];
        $secretarioSuprema = $datosExpediente["m-secretario-suprema"];
        $delitoSuprema = $datosExpediente["m-delito-suprema"];
        $ubicacionSuprema = $datosExpediente["m-ubicacion-suprema"];
        $estadoSuprema = $datosExpediente["m-estado-suprema"];

        $state = $datosExpediente["state"];
        $infoState = $datosExpediente["info-date"];

        $partesSeparadas = [];

        $partesP = request()->input('parte');
        $datosP = request()->input('datos');
        $caracteristicaP = request()->input('caracteristica');
        foreach ($partesP as $index => $parte) {
            $caractP = $caracteristicaP[$index] == null ? "" : $caracteristicaP[$index];
            $partesSeparadas[] = [$parte, $datosP[$index], $caractP];
        }

        // DATA EXPEDIENTE CORTE SUPREMA
        $uSuprema = CorteSuprema::find($id);
        $uSuprema->n_expediente = $nExpSuprema;
        $uSuprema->instancia = $instanciaSuprema;
        $uSuprema->recurso_sala = $recursoSalaSuprema;
        $uSuprema->fecha_ingreso = $fechaIngresoSuprema;
        $uSuprema->organo_procedencia = $organoProcedenciaSuprema;
        $uSuprema->relator = $relatorSuprema;
        $uSuprema->distrito_judicial = $distritoJudicialSuprema;
        $uSuprema->numero_procedencia = $nProcedenciaSuprema;
        $uSuprema->secretario = $secretarioSuprema;
        $uSuprema->delito = $delitoSuprema;
        $uSuprema->ubicacion = $ubicacionSuprema;
        $uSuprema->estado = $estadoSuprema;
        $uSuprema->updated_at = now();
        $uSuprema->update_date = now();
        $uSuprema->partes_procesales = json_encode($partesSeparadas);
        $uSuprema->state = $state;
        $uSuprema->date_state = $infoState;
        $uSuprema->save();

        return redirect()->route('sistema_expedientes.suprema.expedientesRegistroExpedientes')->with('success', '¡Proceso actualizado correctamente!');
    }

    // DELETE
    public function deleteExpediente()
    {
        $id = $_POST['id'];
        SeguimientoSuprema::where('id_exp', '=', $id)->delete();
        VistaCausaSuprema::where('id_exp', '=', $id)->delete();
        CorteSuprema::destroy($id);

        UserParte::where('id_exp', '=', $id)
            ->where('entidad', '=', 'suprema')
            ->where('code_company', '=', Auth::user()->code_company)
            ->delete();
        TaskSuprema::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();
        WorkFlowTaskSuprema::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();
        CommentSuprema::where('id_exp', $id)->delete();
        CommentTaskSuprema::where('id_exp', $id)->delete();
        CommentTaskFlujoSuprema::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();
        EconomicExpensesSuprema::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();
        FlujoAsociadoSuprema::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();

        return response()->json("Eliminado");
    }



    /*
    *
    * Acciones del proceso de Corte Suprema
    *
    *
    */

    /**
     * Obtener seguimientos del Poder Judicial para un expediente específico
     *
     * @OA\Get(
     *     path="/api/corte-suprema/seguimientos-corte-suprema",
     *     tags={"Procesos CEJ SUPREMA"},
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

            $data = CorteSuprema::join('clientes', 'corte_supremas.id_client', '=', 'clientes.id')
                ->select(
                    'corte_supremas.id',
                    'corte_supremas.n_expediente',
                    'corte_supremas.instancia',
                    'corte_supremas.recurso_sala',
                    'corte_supremas.fecha_ingreso',
                    'corte_supremas.organo_procedencia',
                    'corte_supremas.relator',
                    'corte_supremas.distrito_judicial',
                    'corte_supremas.numero_procedencia',
                    'corte_supremas.secretario',
                    'corte_supremas.delito',
                    'corte_supremas.ubicacion',
                    'corte_supremas.estado',
                    'corte_supremas.entidad',
                    'corte_supremas.url_suprema',
                    'corte_supremas.partes_procesales',
                    'corte_supremas.date_state',
                    'corte_supremas.state',
                    'clientes.name',
                    'clientes.last_name',
                    'clientes.name_company',
                    'clientes.type_contact',
                    'clientes.ruc',
                    'clientes.email',
                    'clientes.phone',
                    'seguimiento_supremas.fecha',
                )
                ->leftJoin('seguimiento_supremas', function ($join) {
                    $join->on('corte_supremas.id', '=', 'seguimiento_supremas.id_exp')
                        ->where('seguimiento_supremas.id', '=', function ($query) {
                            $query->select(DB::raw('MAX(id)'))
                                ->from('seguimiento_supremas')
                                ->whereColumn('id_exp', 'corte_supremas.id');
                        });
                })
                ->orderBy('corte_supremas.id', 'desc')
                ->where('corte_supremas.id', $id)
                ->where('corte_supremas.code_company', $dataUser->code_company)
                ->get();

            //withQueryString() => mantener el query
            // $movements = SeguimientoSuprema::where('id_exp', $id)
            //     ->where('code_company', $dataUser->code_company)
            //     ->orderBy('id', 'desc')
            //     ->paginate(5)
            //     ->withQueryString();
            $movements = SeguimientoSuprema::select('seguimiento_supremas.*', 'users.name', 'users.lastname')
                ->leftJoin('users', 'seguimiento_supremas.code_user', '=', 'users.code_user')
                ->where('seguimiento_supremas.id_exp', $id)
                ->where('seguimiento_supremas.code_company', $dataUser->code_company)
                ->orderBy('seguimiento_supremas.id', 'desc')
                ->paginate(5)
                ->withQueryString();


            $comments = CommentSuprema::where('id_exp', $id)->where('code_company', $dataUser->code_company)->orderBy('date', 'asc')->get();

            $groupStages = DB::table('work_flow_task_supremas')
                ->select('id_workflow', 'id_workflow_stage', 'id_exp', 'nombre_etapa', 'nombre_flujo')
                ->where('id_exp', $id)
                ->groupBy('id_workflow', 'id_workflow_stage', 'id_exp', 'nombre_etapa', 'nombre_flujo')
                ->get();

            $estadoFlujoCount = FlujoAsociadoSuprema::where('id_exp', $id)->where('table_pertenece', 'flujo')->count();
            $workFlowTaskExpediente = WorkFlowTaskSuprema::where('id_exp', $id)->get();

            $vistaCausaSuprema = VistaCausaSuprema::where('id_exp', $id)->get();

            $countAll = WorkFlowTaskSuprema::where('id_exp', $id)->count();
            $countCheck = WorkFlowTaskSuprema::where('id_exp', $id)->where('metadata', 'finalizado')->count();
            $countAllTask = TaskSuprema::where('id_exp', $id)->count();
            $countAllTaskCheck = TaskSuprema::where('id_exp', $id)->where('metadata', 'finalizado')->count();

            // TOTAL
            $sumAll = $countAll + $countAllTask;
            // TOTAL AVANZADO
            $sumAllCheck = $countCheck + $countAllTaskCheck;
        }

        // return view('dashboard.sistema_expedientes.suprema.seguimientos', compact('id', 'data', 'movements', 'comments', 'workFlowTaskExpediente', 'groupStages', 'estadoFlujoCount', 'vistaCausaSuprema', 'sumAll', 'sumAllCheck'));

        return response()->json([
            'movements' => $movements,
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

        $datosExpediente = CorteSuprema::where('id', $idExp)->first();

        // $count = SeguimientoSuprema::where('id_exp', $idExp)->count();

        $dataUser = User::where('id', Auth()->id())->get()->first();

        if ($archivoAdjunto) {
            $extension = $archivoAdjunto->getClientOriginalExtension();
            $nombreArchivo = $archivoAdjunto->getClientOriginalName();
            $nombreArchivoSinExtension = basename($nombreArchivo, ".{$extension}");

            // Verifica si la extensión del archivo está permitida
            if ($extension == 'pdf' || $extension == 'docx' || $extension == 'doc') {
                // Sube el archivo al almacenamiento
                if (file_exists(public_path('storage/suprema/' . $datosExpediente->n_expediente . '/' . $nombreArchivo))) {
                    // El archivo existe
                    $nombreArchivo = $nombreArchivoSinExtension . ' - copy.' . $extension;
                    $rutaArchivo = $archivoAdjunto->storeAs('public/suprema/' . $datosExpediente->n_expediente, $nombreArchivo);
                    $url = Storage::url($rutaArchivo);
                } else {
                    $rutaArchivo = $archivoAdjunto->storeAs('public/suprema/' . $datosExpediente->n_expediente, $nombreArchivo);
                    $url = Storage::url($rutaArchivo);
                }
            }
        }

        $ultimoRegistro = SeguimientoSuprema::where('id_exp', $idExp)
            ->orderBy('n_seguimiento', 'desc')
            ->first();

        $newData = [
            'u_tipo' => $type,
            'u_title' => $title,
            'u_date' => $date,
            'u_descripcion' => $descrip,
            'abog_virtual' => 'no',
            'n_seguimiento' => $ultimoRegistro->n_seguimiento + 1,
            'id_exp' => $idExp,
            'code_company' => $dataUser->code_company,
            'code_user' => $dataUser->code_user,
            'metadata' => $url ?? null,
            "video" => $urlVideo ?? null,
        ];

        SeguimientoSuprema::insert($newData);


        return redirect()->back()->with('success', 'Movimiento se agregó correctamente');
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

        $exp = CorteSuprema::where('id', '=', $idExp)->first();

        $codeCompany = auth()->user()->code_company;
        $codeUser = auth()->user()->code_user;

        $dataOld = SeguimientoSuprema::where('id', $idM)->where('code_company', $codeCompany)->first();

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
                if (file_exists(public_path('storage/suprema/' . $exp->n_expediente . '/' . $nombreArchivo))) {
                    // El archivo existe
                    $nombreArchivo = $nombreArchivoSinExtension . ' - copy.' . $extension;
                    $rutaArchivo = $archivoAdjunto->storeAs('public/suprema/' . $exp->n_expediente, $nombreArchivo);
                    $url = Storage::url($rutaArchivo);
                } else {
                    $rutaArchivo = $archivoAdjunto->storeAs('public/suprema/' . $exp->n_expediente, $nombreArchivo);
                    $url = Storage::url($rutaArchivo);
                }
            }
        }

        SeguimientoSuprema::where('id_exp', '=', $idExp)
            ->where('id', '=', $idM)
            ->where('code_company', $codeCompany)
            ->update([
                'u_title' => $title,
                'u_date' => $date,
                'u_descripcion' => $descrip,
                'code_user' => $codeUser,
                'update_date' => now(),
                'metadata ' => $url ?? $dataOld->metadata,
            ]);

        $value = 'Movimiento del proceso se actualizó correctamente!';

        return redirect()->back()->with('success', $value);
    }

    public function datosAccion()
    {
        $id = request()->input('idM');
        $codeCompany = auth()->user()->code_company;
        $data = SeguimientoSuprema::where('id', $id)
            ->where('code_company', $codeCompany)
            ->get();

        return response()->json($data);
    }

    public function deleteAccion()
    {
        $id = request()->input('idM');
        $codeCompany = auth()->user()->code_company;
        $dataFollowUp = SeguimientoSuprema::where('id', $id)->where('code_company', $codeCompany)->first();

        if ($dataFollowUp && $dataFollowUp->metadata !== null) {
            $borrar_url = str_replace('storage', 'public', $dataFollowUp->metadata);
            Storage::delete($borrar_url);
        }
        if ($dataFollowUp && $dataFollowUp->video !== null) {
            $borrar_url = str_replace('storage', 'public', $dataFollowUp->video);
            Storage::delete($borrar_url);
        }
        SeguimientoSuprema::where('id', $id)
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

        $movements = SeguimientoSuprema::where(function ($query) use ($texto, $id, $codeCompany) {
            $query->where('id_exp', '=', $id)
                ->where('code_company', $codeCompany)
                ->where(function ($innerQuery) use ($texto) {
                    $innerQuery->where('u_descripcion', 'like', '%' . $texto . '%')
                        ->orWhere('u_title', 'like', '%' . $texto . '%')
                        ->orWhere('acto', 'like', '%' . $texto . '%')
                        ->orWhere('resolucion', 'like', '%' . $texto . '%')
                        ->orWhere('fojas', 'like', '%' . $texto . '%')
                        ->orWhere('sumilla', 'like', '%' . $texto . '%')
                        ->orWhere('desc_usuario', 'like', '%' . $texto . '%')
                        ->orWhere('presentante', 'like', '%' . $texto . '%');
                });
        })
            ->orderBy('n_seguimiento', 'desc')
            ->get();



        $comments = CommentSuprema::where('id_exp', $id)->orderBy('date', 'asc')->get();


        return view('dashboard.sistema_expedientes.suprema.searchAccion', compact('movements', 'texto', 'comments'));
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
        $existExp = CorteSuprema::where('code_company', '=', $dataUser->code_company)->get()->first();
        $existMovemment = SeguimientoSuprema::where('code_company', '=', $dataUser->code_company)->get()->first();
        if ($dataUser && $existExp && $existMovemment) {
            $newData = [
                'comment' => $comment,
                'code_user' => $dataUser->code_user,
                'code_company' => $dataUser->code_company,
                'id_exp' => $idExp,
                'id_user' => $idUser,
                'id_seguimiento' => $idMovi,
                'date' => $date,
                'type' => 'Principal',
                'metadata' => Null,
            ];

            $insertedId = DB::table('comment_supremas')->insertGetId($newData);
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
        $dataComment = CommentSuprema::where('id', $id)->get()->first();
        if ($dataUser->code_company == $dataComment->code_company) {
            CommentSuprema::where('id', $id)->delete();
            return response()->json('CommentDelete');
        }
        return response()->json('ErrorDelete');
    }
}
