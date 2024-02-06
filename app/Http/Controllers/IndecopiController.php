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
            $movements = AccionesIndecopi::where('id_indecopi', $id)
                ->where('code_company', $dataUser->code_company)
                ->orderBy('id', 'desc')
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




        return view('dashboard.sistema_expedientes.indecopi.accionesIndecopi', compact('id', 'data', 'movements', 'comments', 'workFlowTaskExpediente', 'groupStages', 'estadoFlujoCount', 'sumAll', 'sumAllCheck'));
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
