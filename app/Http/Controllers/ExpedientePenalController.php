<?php

namespace App\Http\Controllers;

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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpedientePenalController extends Controller
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
            ->where('expediente_sinoes.proceso_penal', 'si')
            ->get();

        $dataCompany = Company::where('code_company', Auth::user()->code_company)->first();
        $dataSuscripcion = Suscripcion::where('id', $dataCompany->id_suscripcion)->first();
        $totalExpedientes = ExpedienteSinoe::where('code_company', Auth::user()->code_company)
            ->where('proceso_penal', 'si')
            ->count();
        $limitExpedientes = $dataSuscripcion->limit_sinoe;

        return view('dashboard.sistema_expedientes.penal.expedientesRegistroExpedientes', compact('expedientes', 'totalExpedientes', 'limitExpedientes'));
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

        return redirect()->route('sistema_expedientes.penal.expedientesRegistroExpedientes')->with('success', '¡Expediente actualizado correctamente!');
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
            ->where('expediente_sinoes.proceso_penal', 'si')
            ->get();

        return response($dataExpediente);
    }

    // DELETE
    public function deleteExpediente()
    {
        $id = $_POST['id'];
        $exp = ExpedienteSinoe::where('id', '=', $id)
            ->where('proceso_penal', 'si')
            ->first();
        $dataUser = User::where('id', '=', auth()->id())
            ->first();
        // $currentDateTime = Carbon::now();
        // $mysqlDateTime = $currentDateTime->format('Y-m-d H:i:s');
        $expN = $exp->n_expediente;
        NotificationSinoe::where('id_exp', '=', $id)->delete();
        AnexoNotificationSinoe::where('id_exp', '=', $id)->delete();
        ExpedienteSinoe::destroy($id);
        UserParte::where('id_exp', '=', $id)
            ->where('entidad', '=', 'penal')
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
            ->where('entidad', 'penal')
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
                ->where('expediente_sinoes.proceso_penal', 'si')
                ->get();

            //withQueryString() => mantener el query
            $movements = NotificationSinoe::where('id_exp', $id)
                ->where('code_company', $dataUser->code_company)
                ->orderBy('id', 'desc')
                ->paginate(5)
                ->withQueryString();

            $notify = AnexoNotificationSinoe::where('id_exp', $id)
                ->where('code_company', $dataUser->code_company)
                ->get();

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
                ->where('entidad', 'penal')
                ->orderBy('id', 'asc')
                ->get();
        }


        return view('dashboard.sistema_expedientes.penal.movimientos', compact('id', 'data', 'movements', 'notify', 'comments', 'workFlowTaskExpediente', 'groupStages', 'estadoFlujoCount', 'sumAll', 'sumAllCheck', 'historialData', 'documentosData', 'suggestion'));
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
            ->where('proceso_penal', 'si')
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
            ->where('proceso_penal', 'si')
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


        return view('dashboard.sistema_expedientes.penal.searchSeguimiento', compact('movements', 'notify', 'texto', 'comments'));
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
            ->where('proceso_penal', 'si')
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
