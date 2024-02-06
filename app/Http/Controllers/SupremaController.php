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
            $movements = SeguimientoSuprema::where('id_exp', $id)
                ->where('code_company', $dataUser->code_company)
                ->orderBy('id', 'desc')
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

        return view('dashboard.sistema_expedientes.suprema.seguimientos', compact('id', 'data', 'movements', 'comments', 'workFlowTaskExpediente', 'groupStages', 'estadoFlujoCount', 'vistaCausaSuprema', 'sumAll', 'sumAllCheck'));
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
