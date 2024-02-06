<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CommentTaskIndecopi;
use App\Models\TaskIndecopi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskIndecopiController extends Controller
{
    //
    //
    public function addTaskJudicial(Request $request) {
        // dd($request);
        $idExp = request()->input("idExp");
        $taskName = request()->input("taskName");
        $taskPrioridad = request()->input("taskPrioridad");
        $taskDescripcion = request()->input("taskDescripcion");
        $taskDateLimite = request()->input("taskDateLimite");
        $taskDateAlerta = request()->input("taskDateAlerta");
        $taskFlujoActivo = request()->input("taskFlujoActivo");

        if ($idExp && $idExp == ''){
            return response()->json('error');
        }
        $dataExist = TaskIndecopi::where('nombre', $taskName)
                                    ->where('flujo_activo', 'no')
                                    ->where('id_exp', $idExp)
                                    ->first();
        if ($dataExist){
            return response()->json('info');
        }

        $newData = [
            'flujo_activo' => $taskFlujoActivo,
            'id_tarea_flujo' => null,
            'id_exp' => $idExp,
            'nombre' => $taskName,
            'descripcion' => $taskDescripcion,
            'prioridad' => $taskPrioridad,
            'estado' => 'En progreso',
            'fecha_limite' => $taskDateLimite,
            'fecha_alerta' => $taskDateAlerta,
            'code_user' => Auth::user()->code_user,
            'code_company' => Auth::user()->code_company,
        ];

        TaskIndecopi::insert($newData);

        return response()->json('success');

    }

    public function updateTaskJudicial(Request $request) {
        //
        // dd($request);
        $id = request()->input("id");
        $idExp = request()->input("idExp");
        $taskName = request()->input("taskName");
        $taskPrioridad = request()->input("taskPrioridad");
        $taskDescripcion = request()->input("taskDescripcion");
        $taskDateLimite = request()->input("taskDateLimite");
        $taskDateAlerta = request()->input("taskDateAlerta");
        $taskFlujoActivo = request()->input("taskFlujoActivo");

        if ($id && $id == ''){
            return response()->json('error');
        }
        $dataExist = TaskIndecopi::where('nombre', $taskName)
                                    ->where('flujo_activo', 'no')
                                    ->where('id_exp', $idExp)
                                    ->first();
        if ($dataExist){
            $upData = [
                'flujo_activo' => $taskFlujoActivo,
                'descripcion' => $taskDescripcion,
                'prioridad' => $taskPrioridad,
                'fecha_limite' => $taskDateLimite,
                'fecha_alerta' => $taskDateAlerta,
            ];
            TaskIndecopi::where('id', $id)->update($upData);
            return response()->json('info');
        }

        $newData = [
            'flujo_activo' => $taskFlujoActivo,
            'nombre' => $taskName,
            'descripcion' => $taskDescripcion,
            'prioridad' => $taskPrioridad,
            'fecha_limite' => $taskDateLimite,
            'fecha_alerta' => $taskDateAlerta,
        ];

        TaskIndecopi::where('id', $id)->update($newData);

        return response()->json('success');
    }

    public function deleteTaskJudicial(Request $request) {
        //
        // dd($request);
        $id = request()->input("id");

        if ($id && $id !== ''){
            TaskIndecopi::where('id', $id)->delete();
            return response()->json('Eliminado');
        }
    }

    public function getAllTaskJudicial() {
        $idExp = request()->input('idExp');
        $data = TaskIndecopi::where('flujo_activo', 'no')
                                ->where('id_exp', $idExp)
                                ->orderBy('fecha_limite')
                                ->get();

        return response()->json($data);
    }



    /*
    * *****************************************
    *
    *       COMENTARIOS EN MOVIMIENTOS
    *
    ****************************************** */

    public function saveComment() {

        $idTask = request()->input('idTask');
        $idExp = request()->input('idExp');
        $comment = request()->input('comment');

        $idUser = Auth()->id();
        $date = date("Y-m-d H:i:s");

        $dataUser = User::where('id', $idUser)->first();
        $existTask = TaskIndecopi::where('id', '=', $idTask)->first();
        if ($dataUser && $existTask){
            $newData =[
                'comment' => $comment,
                'code_user' => $dataUser->code_user,
                'code_company' => $dataUser->code_company,
                'id_exp' => $idExp,
                'id_task' => $idTask,
                'date' => $date,
                'metadata' => Null,
            ];

            $insertedId = DB::table('comment_task_indecopis')->insertGetId($newData);
            return response()->json($newData =[
                'comment' => $comment,
                'user' => $dataUser->name . ', '. $dataUser->lastname,
                'date' => $date,
                'id' => $insertedId,
            ]);
        }
        return response()->json('error');

    }

    public function deleteComment() {
        $id = request()->input('idC');
        $dataUser = User::where('id', Auth()->id())->first();
        $dataComment = CommentTaskIndecopi::where('id', $id)->get()->first();
        if ($dataUser->code_company == $dataComment->code_company){
            CommentTaskIndecopi::where('id', $id)->delete();
            return response()->json('CommentDelete');
        }
        return response()->json('ErrorDelete');
    }

    public function getComment() {
        $id = request()->input('id');
        if ($id && $id !== ''){
            // $data = CommentTaskJudicial::where('id_task', $id)->get();
            $data = CommentTaskIndecopi::join('users', 'comment_task_indecopis.code_user', '=', 'users.code_user')
            ->select(
                'comment_task_indecopis.id',
                'comment_task_indecopis.comment',
                'comment_task_indecopis.code_user',
                'comment_task_indecopis.id_exp',
                'comment_task_indecopis.date',
                'users.name',
                'users.lastname',
            )
            ->where('id_task', $id)
            ->orderBy('comment_task_indecopis.id', 'asc')
            ->get();

            return response()->json($data);
        }
        return response()->json([]);

    }

    public function updateStatusComment(){
        $idTask = request()->input('idTask');
        $idExp = request()->input('idExp');
        $estado = request()->input('estado');
        $dataTimeNow = Carbon::now();

        if ($idTask && $idExp){
            $upData = [
                'estado' => $estado,
                'fecha_finalizada' => $dataTimeNow,
            ];
            TaskIndecopi::where('id', $idTask)->where('id_exp', $idExp)->update($upData);
        }
    }

    // BUSQUEDA DE TAREAS
    public function searchTask(Request $request) {
        // $data = FollowUp::where("u_descripcion", "like", $request->texto."%")->orderByDesc('id')->get();
        $texto = $request->texto;
        $id = $request->idExp;


        $data = TaskIndecopi::where(function($query) use ($texto, $id) {
            $query->where('id_exp', '=', $id)
                ->where(function($innerQuery) use ($texto) {
                    $innerQuery->where('nombre', 'like', '%' . $texto . '%');
                });
        })
        ->orderBy('fecha_limite')
        ->get();

        return response()->json($data);
    }
}
