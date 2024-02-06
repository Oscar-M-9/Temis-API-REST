<?php

namespace App\Http\Controllers;

use App\Models\AccountGoogleCalendar;
use App\Models\CommentTaskFlujoSinoe;
use App\Models\CommentTaskSinoe;
use App\Models\FlujoAsociadoExpedienteSinoe;
use App\Models\TaskExpedienteSinoe;
use App\Models\User;
use App\Models\UserParte;
use App\Models\WorkFlows;
use App\Models\WorkFlowsStage;
use App\Models\WorkFlowsTask;
use App\Models\WorkFlowTaskExpedienteSinoe;
use App\Models\WorkFlowTransitions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskPenalController extends Controller
{
    // protected $client;

    // public function __construct()
    // {
    //     $clientSecretPath = storage_path('app/google-calendar/client_secret.json');
    //     $this->client = new Google_Client();
    //     $this->client->setAuthConfig($clientSecretPath);
    //     $this->client->setAccessType('offline');
    //     $this->client->setApprovalPrompt('force');
    //     $this->client->addScope([
    //         Google_Service_Calendar::CALENDAR,
    //         Google_Service_Calendar::CALENDAR_EVENTS
    //     ]);
    //     $this->client->setRedirectUri(url('/google/calendar/callback/events'));
    // }

    public function addTaskSinoe(Request $request)
    {
        // dd($request);
        $idExp = request()->input("idExp");
        $taskName = request()->input("taskName");
        $taskPrioridad = request()->input("taskPrioridad");
        $taskDescripcion = request()->input("taskDescripcion");
        $taskDateLimite = request()->input("taskDateLimite");
        $taskDateAlerta = request()->input("taskDateAlerta");
        $taskFlujoActivo = request()->input("taskFlujoActivo");

        if ($idExp && $idExp == '') {
            return response()->json('error');
        }
        $dataExist = TaskExpedienteSinoe::where('nombre', $taskName)
            ->where('flujo_activo', 'no')
            ->where('id_exp', $idExp)
            ->first();
        if ($dataExist) {
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

        TaskExpedienteSinoe::insert($newData);

        return response()->json('success');
    }

    public function updateTaskSinoe(Request $request)
    {
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

        if ($id && $id == '') {
            return response()->json('error');
        }
        $dataExist = TaskExpedienteSinoe::where('nombre', $taskName)
            ->where('flujo_activo', 'no')
            ->where('id_exp', $idExp)
            ->first();
        if ($dataExist) {
            $upData = [
                'flujo_activo' => $taskFlujoActivo,
                'descripcion' => $taskDescripcion,
                'prioridad' => $taskPrioridad,
                'fecha_limite' => $taskDateLimite,
                'fecha_alerta' => $taskDateAlerta,
            ];
            TaskExpedienteSinoe::where('id', $id)->update($upData);
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

        TaskExpedienteSinoe::where('id', $id)->update($newData);

        return response()->json('success');
    }

    public function deleteTaskSinoe(Request $request)
    {
        //
        // dd($request);
        $id = request()->input("id");

        if ($id && $id !== '') {
            TaskExpedienteSinoe::where('id', $id)->delete();
            return response()->json('Eliminado');
        }
    }

    public function getAllTaskSinoe()
    {
        $idExp = request()->input('idExp');
        $data = TaskExpedienteSinoe::where('flujo_activo', 'no')
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

    public function saveComment()
    {

        $idTask = request()->input('idTask');
        $idExp = request()->input('idExp');
        $comment = request()->input('comment');

        $idUser = Auth()->id();
        $date = date("Y-m-d H:i:s");

        $dataUser = User::where('id', $idUser)->first();
        $existTask = TaskExpedienteSinoe::where('id', '=', $idTask)->first();
        if ($dataUser && $existTask) {
            $newData = [
                'comment' => $comment,
                'code_user' => $dataUser->code_user,
                'code_company' => $dataUser->code_company,
                'id_exp' => $idExp,
                'id_task' => $idTask,
                'date' => $date,
                'metadata' => Null,
            ];

            $insertedId = DB::table('comment_task_sinoes')->insertGetId($newData);
            return response()->json($newData = [
                'comment' => $comment,
                'user' => $dataUser->name . ', ' . $dataUser->lastname,
                'date' => $date,
                'id' => $insertedId,
            ]);
        }
        return response()->json('error');
    }

    public function deleteComment()
    {
        $id = request()->input('idC');
        $dataUser = User::where('id', Auth()->id())->first();
        $dataComment = CommentTaskSinoe::where('id', $id)->get()->first();
        if ($dataUser->code_company == $dataComment->code_company) {
            CommentTaskSinoe::where('id', $id)->delete();
            return response()->json('CommentDelete');
        }
        return response()->json('ErrorDelete');
    }

    public function getComment()
    {
        $id = request()->input('id');
        if ($id && $id !== '') {
            // $data = CommentTaskJudicial::where('id_task', $id)->get();
            $data = CommentTaskSinoe::join('users', 'comment_task_sinoes.code_user', '=', 'users.code_user')
                ->select(
                    'comment_task_sinoes.id',
                    'comment_task_sinoes.comment',
                    'comment_task_sinoes.code_user',
                    'comment_task_sinoes.id_exp',
                    'comment_task_sinoes.date',
                    'users.name',
                    'users.lastname',
                )
                ->where('id_task', $id)
                ->orderBy('comment_task_sinoes.id', 'asc')
                ->get();

            return response()->json($data);
        }
        return response()->json([]);
    }

    public function updateStatusComment()
    {
        $idTask = request()->input('idTask');
        $idExp = request()->input('idExp');
        $estado = request()->input('estado');
        $dataTimeNow = Carbon::now();

        if ($idTask && $idExp) {
            $upData = [
                'estado' => $estado,
                'fecha_finalizada' => $dataTimeNow,
            ];
            TaskExpedienteSinoe::where('id', $idTask)->where('id_exp', $idExp)->update($upData);
        }
    }

    // BUSQUEDA DE TAREAS
    public function searchTask(Request $request)
    {
        // $data = FollowUp::where("u_descripcion", "like", $request->texto."%")->orderByDesc('id')->get();
        $texto = $request->texto;
        $id = $request->idExp;


        $data = TaskExpedienteSinoe::where(function ($query) use ($texto, $id) {
            $query->where('id_exp', '=', $id)
                ->where(function ($innerQuery) use ($texto) {
                    $innerQuery->where('nombre', 'like', '%' . $texto . '%');
                });
        })
            ->orderBy('fecha_limite')
            ->get();

        return response()->json($data);
    }









    public function getCommentFlujoSinoe()
    {
        $id = request()->input('id');
        $idWorkFlow = request()->input('idWorkFlow');
        $idWorkFlowTask = request()->input('idWorkFlowTask');
        $idWorkFlowStage = request()->input('idWorkFlowStage');
        $idExp = request()->input('idExp');

        if ($id && $id !== '') {
            // $data = CommentTaskJudicial::where('id_task', $id)->get();
            $data = CommentTaskFlujoSinoe::join('users', 'comment_task_flujo_sinoes.code_user', '=', 'users.code_user')
                ->select(
                    'comment_task_flujo_sinoes.id',
                    'comment_task_flujo_sinoes.comment',
                    'comment_task_flujo_sinoes.code_user',
                    'comment_task_flujo_sinoes.id_exp',
                    'comment_task_flujo_sinoes.id_flujo',
                    'comment_task_flujo_sinoes.date',
                    'users.name',
                    'users.lastname',
                )
                ->where('id_exp', $idExp)
                ->where('id_stage', $idWorkFlowStage)
                ->where('id_task', $idWorkFlowTask)
                ->where('id_flujo', $idWorkFlow)
                ->orderBy('comment_task_flujo_sinoes.id', 'asc')
                ->get();

            return response()->json($data);
        }
        return response()->json([]);
    }


    public function updateStatusCommentFlujoSinoe()
    {
        $id = request()->input("id");
        $idExp = request()->input("idExp");
        $idWorkFlow = request()->input("idWorkFlow");
        $idWorkFlowTask = request()->input("idWorkFlowTask");
        $idWorkFlowStage = request()->input("idWorkFlowStage");
        $estado = request()->input("estado");

        $dataTimeNow = Carbon::now();

        if ($id && $idExp && $idWorkFlow && $idWorkFlowStage && $idWorkFlowTask) {
            $upData = [
                'estado' => $estado,
                'fecha_finalizada' => $dataTimeNow,
                'metadata' => 'finalizado',
            ];
            // actualizar el etado de la tarea de la etapa
            WorkFlowTaskExpedienteSinoe::where('id', $id)
                ->where('id_exp', $idExp)
                ->where('code_company', Auth::user()->code_company)
                ->update($upData);

            $dataUser = User::where('id', Auth::user()->id)->first();
            // // agrupo las etapas del flujo activado en el expediente
            // $groupStages = DB::table('work_flow_task_expedientes')
            //     ->select('id_workflow', 'id_workflow_stage', 'id_exp', 'nombre_etapa', 'nombre_flujo')
            //     ->where('id_exp', $idExp)
            //     ->where("code_company", Auth::user()->code_company)
            //     ->groupBy('id_workflow', 'id_workflow_stage', 'id_exp', 'nombre_etapa', 'nombre_flujo')
            //     ->get();

            // Cantidad de todas las tareas refistradas
            $workFlowTaskCountAll = WorkFlowTaskExpedienteSinoe::where('id_exp', $idExp)
                ->where("code_company", Auth::user()->code_company)
                ->count();
            // cantidad de todas las tareas finalizzadas
            $workFlowTaskCountFin = WorkFlowTaskExpedienteSinoe::where('id_exp', $idExp)
                ->where("code_company", Auth::user()->code_company)
                ->where('metadata', 'finalizado')
                ->count();

            // estado del flujo
            $estadoFlujoActivo = FlujoAsociadoExpedienteSinoe::where('id_exp', $idExp)
                ->where("code_company", Auth::user()->code_company)
                ->where('estado', 'activo')
                ->where('table_pertenece', 'flujo')
                ->first();
            $estadoFlujoNoActivo = FlujoAsociadoExpedienteSinoe::where('id_exp', $idExp)
                ->where("code_company", Auth::user()->code_company)
                ->where('estado', 'no activo')
                ->where('table_pertenece', 'flujo')
                ->first();

            $dataFlujo = WorkFlows::where('id', $idWorkFlow)
                ->where('code_company', $dataUser->code_company)
                ->first();

            // Ver si un usuario vinculado con el expediente
            // acepto el consetimiento en google calendar
            $userPartes = UserParte::where('entidad', 'judicial')
                ->where('id_exp', $idExp)
                ->where('code_company', Auth::user()->code_company)
                ->get();

            $firstDataAccount = null;
            foreach ($userPartes as $key => $value) {
                $dataAccount = AccountGoogleCalendar::where('code_user', $value->code_user)->first();
                if ($dataAccount) {
                    $firstDataAccount = $dataAccount;
                    break;
                }
            }

            if ($estadoFlujoActivo) {
                if (!$estadoFlujoNoActivo) {
                    // si todas las tareas se encuentran finalizadas
                    if ($workFlowTaskCountAll === $workFlowTaskCountFin) {

                        $conditionWorkFlow = WorkFlowTransitions::where('id_workflow', $idWorkFlow)
                            ->where('id_workflow_stage', $idWorkFlowStage)
                            ->where("code_company", Auth::user()->code_company)
                            ->get();
                        var_dump($conditionWorkFlow);
                        if ($conditionWorkFlow->isEmpty()) {
                            var_dump("default");
                            // No se encontro nigun resultado de transicion lo cual tomara el por default
                            $dataStageNext = WorkFlowsStage::where('id', ">", $idWorkFlowStage)
                                ->where("id_workflow", $idWorkFlow)
                                ->where("code_company", Auth::user()->code_company)
                                ->orderBy('id', 'asc')
                                ->first();

                            if ($dataStageNext) {
                                // Tareas de la nueva etapa
                                $dataTaskStageNext = WorkFlowsTask::where('id_workflow', $idWorkFlow)
                                    ->where('id_workflow_stage', $dataStageNext->id)
                                    ->where("id_workflow", $idWorkFlow)
                                    ->where("code_company", $dataUser->code_company)
                                    ->get();

                                // agregamos la nuevas tareas de la nueva etapa
                                if (count($dataTaskStageNext) > 0) {
                                    foreach ($dataTaskStageNext as $item) {
                                        $WorkFlowTaskExpedienteNextTable = new WorkFlowTaskExpedienteSinoe();
                                        $WorkFlowTaskExpedienteNextTable->id_workflow = $item->id_workflow;
                                        $WorkFlowTaskExpedienteNextTable->id_workflow_stage = $item->id_workflow_stage;
                                        $WorkFlowTaskExpedienteNextTable->id_workflow_task = $item->id;
                                        $WorkFlowTaskExpedienteNextTable->id_exp = $idExp;
                                        $WorkFlowTaskExpedienteNextTable->nombre_etapa = $dataStageNext->nombre;
                                        $WorkFlowTaskExpedienteNextTable->nombre_flujo = $dataFlujo->nombre;
                                        $WorkFlowTaskExpedienteNextTable->nombre = $item->nombre;
                                        $WorkFlowTaskExpedienteNextTable->descripcion = $item->descripcion;
                                        $WorkFlowTaskExpedienteNextTable->dias_duracion = $item->dias_duracion;
                                        $WorkFlowTaskExpedienteNextTable->dias_antes_venc = $item->dias_antes_venc;

                                        $fechaDuracion = Carbon::now()->addDays($item->dias_duracion);
                                        $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                                        $fechaAlerta = Carbon::now()->addDays($item->dias_antes_venc);
                                        $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                                        $WorkFlowTaskExpedienteNextTable->fecha_limite = $fechaFormateadaDuracion;
                                        $WorkFlowTaskExpedienteNextTable->fecha_alerta = $fechaFormateadaAlerta;
                                        $WorkFlowTaskExpedienteNextTable->fecha_finalizada = null;

                                        $WorkFlowTaskExpedienteNextTable->attached_files = $item->attached_files;
                                        $WorkFlowTaskExpedienteNextTable->estado = 'En progreso';
                                        $WorkFlowTaskExpedienteNextTable->prioridad = $item->prioridad;
                                        $WorkFlowTaskExpedienteNextTable->code_user = Auth::user()->code_user;
                                        $WorkFlowTaskExpedienteNextTable->code_company = Auth::user()->code_company;
                                        $WorkFlowTaskExpedienteNextTable->save();

                                        // Creando evento en google calendar
                                        // if ($firstDataAccount){
                                        //     // Actualización del usuario desde la base de datos
                                        //     $this->client->setAccessToken($firstDataAccount->metadata);

                                        //     // Si el token de acceso ha expirado, usar el token de actualización para obtener un nuevo token de acceso
                                        //     if ($this->client->isAccessTokenExpired()) {
                                        //         $this->client->fetchAccessTokenWithRefreshToken($firstDataAccount->refresh_token);
                                        //         $newAccessToken = $this->client->getAccessToken();

                                        //         // Actualizar el nuevo token de acceso en la base de datos
                                        //         AccountGoogleCalendar::where('id', $firstDataAccount->id)->update([
                                        //             'access_token' => $newAccessToken["access_token"],
                                        //         ]);
                                        //         $this->client->setAccessToken($newAccessToken);
                                        //     }
                                        //     // Hacer solicitudes a la API de Google Calendar
                                        //     $service = new Google_Service_Calendar($this->client);


                                        //     // ID del calendario obtenido desde la base de datos
                                        //     $calendarId = $firstDataAccount->id_calendar;

                                        //     // Crear un objeto EventDateTime para la fecha de inicio
                                        //     $inicioEvento = new EventDateTime();
                                        //     $inicioEvento->setDate($fechaFormateadaDuracion);
                                        //     $inicioEvento->setTimeZone('America/Lima');
                                        //     // Crear un objeto EventDateTime para la fecha de end
                                        //     $endEvento = new EventDateTime();
                                        //     $endEvento->setDate($fechaFormateadaDuracion);
                                        //     $endEvento->setTimeZone('America/Lima');

                                        //     // Crear el creador del evento
                                        //     $eventCreator = new EventCreator();

                                        //     $eventCreator->setEmail(config('app.iamcalendar'));
                                        //     $eventCreator->setDisplayName('Temis');

                                        //     // Crear un nuevo evento
                                        //     $evento = new \Google\Service\Calendar\Event();

                                        //     $evento->setCreator($eventCreator);
                                        //     $evento->setSummary($item->nombre);
                                        //     $evento->setDescription($item->descripcion);
                                        //     $evento->setStart($inicioEvento);
                                        //     $evento->setEnd($endEvento);
                                        //     $evento->setColorId(7);

                                        //     $invitados = [];
                                        //     if ($userPartes){
                                        //         foreach ($userPartes as $key => $value) {
                                        //             if ($value->code_user !== $dataUser->code_user){
                                        //                 $invitados[] = ["email" => $value->email];
                                        //             }
                                        //         }
                                        //     }
                                        //     $evento->setAttendees($invitados);

                                        //     // Crear el evento
                                        //     $eventsData =$service->events->insert($calendarId, $evento);
                                        //     $eventsDataId = $eventsData->getId();
                                        //     $metaData = [
                                        //         "calendarId" => $dataAccount->id_calendar,
                                        //         "eventsId" => $eventsDataId,
                                        //         "code_user" => $firstDataAccount->code_user,
                                        //         "code_code_company" => $firstDataAccount->code_code_company,
                                        //         "account_id" => $firstDataAccount->id
                                        //     ];
                                        //     $WorkFlowTaskExpedienteNextTable->metadata = json_encode($metaData);
                                        //     $WorkFlowTaskExpedienteNextTable->save();
                                        // }
                                    }
                                }
                            }
                        } else {
                            var_dump("transicion");
                            // En caso de existir transicion crear la etapa segun la transicion

                            // verificar la condicion antes de hacer la transicion

                            // cantidad total de las tareas de la etapa
                            $stageCount = WorkFlowTaskExpedienteSinoe::select('w1.*')
                                ->from('work_flow_task_expediente_sinoes AS w1')
                                ->join(DB::raw('(SELECT MAX(id) AS max_id, id_workflow_task
                                FROM work_flow_task_expediente_sinoes
                                WHERE id_exp = ' . $idExp . '
                                AND id_workflow = ' . $idWorkFlow . '
                                AND id_workflow_stage = ' . $idWorkFlowStage . '
                                GROUP BY id_workflow_task) AS max_ids'), function ($join) {
                                    $join->on('w1.id', '=', 'max_ids.max_id')
                                        ->on('w1.id_workflow_task', '=', 'max_ids.id_workflow_task');
                                })
                                ->where('w1.id_exp', $idExp)
                                ->where('w1.id_workflow', $idWorkFlow)
                                ->where('w1.id_workflow_stage', $idWorkFlowStage)
                                ->get();

                            // Contamos su estado de las tareas Aprobada / Rechazada /En progreso
                            $stageCountAprobada = 0;
                            $stageCountRechazada = 0;
                            $stageCountEnProgreso = 0;

                            foreach ($stageCount as $key => $valSC) {
                                if ($valSC->estado == "Aprobada") {
                                    $stageCountAprobada++;
                                }
                                if ($valSC->estado == "Rechazada") {
                                    $stageCountRechazada++;
                                }
                                if ($valSC->estado == "En progreso") {
                                    $stageCountEnProgreso++;
                                }
                            }

                            if ($stageCountEnProgreso == 0) {

                                foreach ($conditionWorkFlow as $key => $itemConditionR) {
                                    // ? TODAS LAS TAREAS RECHAZADAS
                                    if ($itemConditionR->condicion == "Todas rechazadas") {
                                        var_dump('rechazada');
                                        if ($stageCountRechazada == count($stageCount)) {
                                            $dataStageNew = WorkFlowsStage::where('nombre', $itemConditionR->etapa)
                                                ->where('id_workflow', $itemConditionR->id_workflow)
                                                ->where('code_company', $dataUser->code_company)
                                                ->first();
                                            $dataFlujo = WorkFlows::where('id', $itemConditionR->id_workflow)
                                                ->where('code_company', $dataUser->code_company)
                                                ->first();
                                            $dataTaskStage = WorkFlowsTask::where('id_workflow', $idWorkFlow)
                                                ->where('id_workflow_stage', $dataStageNew->id)
                                                ->where('code_company', $dataUser->code_company)
                                                ->get();

                                            if (count($dataTaskStage) > 0) {
                                                foreach ($dataTaskStage as $item) {
                                                    $WorkFlowTaskExpedienteTable = new WorkFlowTaskExpedienteSinoe();
                                                    $WorkFlowTaskExpedienteTable->id_workflow = $item->id_workflow;
                                                    $WorkFlowTaskExpedienteTable->id_workflow_stage = $item->id_workflow_stage;
                                                    $WorkFlowTaskExpedienteTable->id_workflow_task = $item->id;
                                                    $WorkFlowTaskExpedienteTable->id_exp = $idExp;
                                                    $WorkFlowTaskExpedienteTable->nombre_etapa = $dataStageNew->nombre;
                                                    $WorkFlowTaskExpedienteTable->nombre_flujo = $dataFlujo->nombre;
                                                    $WorkFlowTaskExpedienteTable->nombre = $item->nombre;
                                                    $WorkFlowTaskExpedienteTable->descripcion = $item->descripcion;
                                                    $WorkFlowTaskExpedienteTable->dias_duracion = $item->dias_duracion;
                                                    $WorkFlowTaskExpedienteTable->dias_antes_venc = $item->dias_antes_venc;

                                                    $fechaDuracion = Carbon::now()->addDays($item->dias_duracion);
                                                    $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                                                    $fechaAlerta = Carbon::now()->addDays($item->dias_antes_venc);
                                                    $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                                                    $WorkFlowTaskExpedienteTable->fecha_limite = $fechaFormateadaDuracion;
                                                    $WorkFlowTaskExpedienteTable->fecha_alerta = $fechaFormateadaAlerta;
                                                    $WorkFlowTaskExpedienteTable->fecha_finalizada = null;

                                                    $WorkFlowTaskExpedienteTable->attached_files = $item->attached_files;
                                                    $WorkFlowTaskExpedienteTable->estado = 'En progreso';
                                                    $WorkFlowTaskExpedienteTable->prioridad = $item->prioridad;
                                                    $WorkFlowTaskExpedienteTable->code_user = Auth::user()->code_user;
                                                    $WorkFlowTaskExpedienteTable->code_company = Auth::user()->code_company;
                                                    $WorkFlowTaskExpedienteTable->save();

                                                    // // Creando evento en google calendar
                                                    // if ($firstDataAccount){
                                                    //     // Actualización del usuario desde la base de datos
                                                    //     $this->client->setAccessToken($firstDataAccount->metadata);

                                                    //     // Si el token de acceso ha expirado, usar el token de actualización para obtener un nuevo token de acceso
                                                    //     if ($this->client->isAccessTokenExpired()) {
                                                    //         $this->client->fetchAccessTokenWithRefreshToken($firstDataAccount->refresh_token);
                                                    //         $newAccessToken = $this->client->getAccessToken();

                                                    //         // Actualizar el nuevo token de acceso en la base de datos
                                                    //         AccountGoogleCalendar::where('id', $firstDataAccount->id)->update([
                                                    //             'access_token' => $newAccessToken["access_token"],
                                                    //         ]);
                                                    //         $this->client->setAccessToken($newAccessToken);
                                                    //     }
                                                    //     // Hacer solicitudes a la API de Google Calendar
                                                    //     $service = new Google_Service_Calendar($this->client);


                                                    //     // ID del calendario obtenido desde la base de datos
                                                    //     $calendarId = $firstDataAccount->id_calendar;

                                                    //     // Crear un objeto EventDateTime para la fecha de inicio
                                                    //     $inicioEvento = new EventDateTime();
                                                    //     $inicioEvento->setDate($fechaFormateadaDuracion);
                                                    //     $inicioEvento->setTimeZone('America/Lima');
                                                    //     // Crear un objeto EventDateTime para la fecha de end
                                                    //     $endEvento = new EventDateTime();
                                                    //     $endEvento->setDate($fechaFormateadaDuracion);
                                                    //     $endEvento->setTimeZone('America/Lima');

                                                    //     // Crear el creador del evento
                                                    //     $eventCreator = new EventCreator();

                                                    //     $eventCreator->setEmail(config('app.iamcalendar'));
                                                    //     $eventCreator->setDisplayName('Temis');

                                                    //     // Crear un nuevo evento
                                                    //     $evento = new \Google\Service\Calendar\Event();

                                                    //     $evento->setCreator($eventCreator);
                                                    //     $evento->setSummary($item->nombre);
                                                    //     $evento->setDescription($item->descripcion);
                                                    //     $evento->setStart($inicioEvento);
                                                    //     $evento->setEnd($endEvento);
                                                    //     $evento->setColorId(7);

                                                    //     $invitados = [];
                                                    //     if ($userPartes){
                                                    //         foreach ($userPartes as $key => $value) {
                                                    //             if ($value->code_user !== $dataUser->code_user){
                                                    //                 $invitados[] = ["email" => $value->email];
                                                    //             }
                                                    //         }
                                                    //     }
                                                    //     $evento->setAttendees($invitados);

                                                    //     // Crear el evento
                                                    //     $eventsData =$service->events->insert($calendarId, $evento);
                                                    //     $eventsDataId = $eventsData->getId();
                                                    //     $metaData = [
                                                    //         "calendarId" => $dataAccount->id_calendar,
                                                    //         "eventsId" => $eventsDataId,
                                                    //         "code_user" => $firstDataAccount->code_user,
                                                    //         "code_code_company" => $firstDataAccount->code_code_company,
                                                    //         "account_id" => $firstDataAccount->id
                                                    //     ];
                                                    //     $WorkFlowTaskExpedienteTable->metadata = json_encode($metaData);
                                                    //     $WorkFlowTaskExpedienteTable->save();
                                                    // }
                                                }
                                            }
                                        }
                                    }
                                    // ? TODAS LAS TAREAS APROBADAS
                                    if ($itemConditionR->condicion == "Todas aprobadas") {
                                        var_dump("Aprobada");
                                        if ($stageCountAprobada == count($stageCount)) {

                                            $dataStage = WorkFlowsStage::where('nombre', $itemConditionR->etapa)
                                                ->where('id_workflow', $itemConditionR->id_workflow)
                                                ->where('code_company', $dataUser->code_company)
                                                ->first();
                                            $dataFlujo = WorkFlows::where('id', $itemConditionR->id_workflow)
                                                ->where('code_company', $dataUser->code_company)
                                                ->first();
                                            $dataTaskStage = WorkFlowsTask::where('id_workflow', $idWorkFlow)
                                                ->where('id_workflow_stage', $dataStage->id)
                                                ->where('code_company', $dataUser->code_company)
                                                ->get();

                                            // New transition in task
                                            $dataTransitions = WorkFlowTransitions::where('id_workflow', $dataStage->id_workflow)
                                                ->where('id_workflow_stage', $dataStage->id)
                                                ->where('code_company', $dataUser->code_company)
                                                ->get();

                                            if (count($dataTaskStage) > 0) {
                                                foreach ($dataTaskStage as $item) {
                                                    $WorkFlowTaskExpedienteTable = new WorkFlowTaskExpedienteSinoe();
                                                    $WorkFlowTaskExpedienteTable->id_workflow = $item->id_workflow;
                                                    $WorkFlowTaskExpedienteTable->id_workflow_stage = $item->id_workflow_stage;
                                                    $WorkFlowTaskExpedienteTable->id_workflow_task = $item->id;
                                                    $WorkFlowTaskExpedienteTable->id_exp = $idExp;
                                                    $WorkFlowTaskExpedienteTable->nombre_etapa = $dataStage->nombre;
                                                    $WorkFlowTaskExpedienteTable->nombre_flujo = $dataFlujo->nombre;
                                                    $WorkFlowTaskExpedienteTable->nombre = $item->nombre;
                                                    $WorkFlowTaskExpedienteTable->descripcion = $item->descripcion;
                                                    $WorkFlowTaskExpedienteTable->dias_duracion = $item->dias_duracion;
                                                    $WorkFlowTaskExpedienteTable->dias_antes_venc = $item->dias_antes_venc;

                                                    $fechaDuracion = Carbon::now()->addDays($item->dias_duracion);
                                                    $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                                                    $fechaAlerta = Carbon::now()->addDays($item->dias_antes_venc);
                                                    $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                                                    $WorkFlowTaskExpedienteTable->fecha_limite = $fechaFormateadaDuracion;
                                                    $WorkFlowTaskExpedienteTable->fecha_alerta = $fechaFormateadaAlerta;
                                                    $WorkFlowTaskExpedienteTable->fecha_finalizada = null;

                                                    $WorkFlowTaskExpedienteTable->attached_files = $item->attached_files;
                                                    $WorkFlowTaskExpedienteTable->estado = 'En progreso';
                                                    $WorkFlowTaskExpedienteTable->prioridad = $item->prioridad;
                                                    $WorkFlowTaskExpedienteTable->code_user = Auth::user()->code_user;
                                                    $WorkFlowTaskExpedienteTable->code_company = Auth::user()->code_company;
                                                    $WorkFlowTaskExpedienteTable->save();

                                                    // Creando evento en google calendar
                                                    // if ($firstDataAccount){
                                                    //     // Actualización del usuario desde la base de datos
                                                    //     $this->client->setAccessToken($firstDataAccount->metadata);

                                                    //     // Si el token de acceso ha expirado, usar el token de actualización para obtener un nuevo token de acceso
                                                    //     if ($this->client->isAccessTokenExpired()) {
                                                    //         $this->client->fetchAccessTokenWithRefreshToken($firstDataAccount->refresh_token);
                                                    //         $newAccessToken = $this->client->getAccessToken();

                                                    //         // Actualizar el nuevo token de acceso en la base de datos
                                                    //         AccountGoogleCalendar::where('id', $firstDataAccount->id)->update([
                                                    //             'access_token' => $newAccessToken["access_token"],
                                                    //         ]);
                                                    //         $this->client->setAccessToken($newAccessToken);
                                                    //     }
                                                    //     // Hacer solicitudes a la API de Google Calendar
                                                    //     $service = new Google_Service_Calendar($this->client);


                                                    //     // ID del calendario obtenido desde la base de datos
                                                    //     $calendarId = $firstDataAccount->id_calendar;

                                                    //     // Crear un objeto EventDateTime para la fecha de inicio
                                                    //     $inicioEvento = new EventDateTime();
                                                    //     $inicioEvento->setDate($fechaFormateadaDuracion);
                                                    //     $inicioEvento->setTimeZone('America/Lima');
                                                    //     // Crear un objeto EventDateTime para la fecha de end
                                                    //     $endEvento = new EventDateTime();
                                                    //     $endEvento->setDate($fechaFormateadaDuracion);
                                                    //     $endEvento->setTimeZone('America/Lima');

                                                    //     // Crear el creador del evento
                                                    //     $eventCreator = new EventCreator();

                                                    //     $eventCreator->setEmail(config('app.iamcalendar'));
                                                    //     $eventCreator->setDisplayName('Temis');

                                                    //     // Crear un nuevo evento
                                                    //     $evento = new \Google\Service\Calendar\Event();

                                                    //     $evento->setCreator($eventCreator);
                                                    //     $evento->setSummary($item->nombre);
                                                    //     $evento->setDescription($item->descripcion);
                                                    //     $evento->setStart($inicioEvento);
                                                    //     $evento->setEnd($endEvento);
                                                    //     $evento->setColorId(7);

                                                    //     $invitados = [];
                                                    //     if ($userPartes){
                                                    //         foreach ($userPartes as $key => $value) {
                                                    //             if ($value->code_user !== $dataUser->code_user){
                                                    //                 $invitados[] = ["email" => $value->email];
                                                    //             }
                                                    //         }
                                                    //     }
                                                    //     $evento->setAttendees($invitados);

                                                    //     // Crear el evento
                                                    //     $eventsData =$service->events->insert($calendarId, $evento);
                                                    //     $eventsDataId = $eventsData->getId();
                                                    //     $metaData = [
                                                    //         "calendarId" => $dataAccount->id_calendar,
                                                    //         "eventsId" => $eventsDataId,
                                                    //         "code_user" => $firstDataAccount->code_user,
                                                    //         "code_code_company" => $firstDataAccount->code_code_company,
                                                    //         "account_id" => $firstDataAccount->id
                                                    //     ];
                                                    //     $WorkFlowTaskExpedienteTable->metadata = json_encode($metaData);
                                                    //     $WorkFlowTaskExpedienteTable->save();
                                                    // }
                                                }
                                            }
                                        }
                                    }
                                    // ? TODAS FINALIZADAS, CON AL MENOS UNA RECHAZADA
                                    if ($itemConditionR->condicion == "Todas finalizadas, con al menos una rechazada") {
                                        var_dump('una rechazada');
                                        if ($stageCountAprobada == count($stageCount) - 1) {
                                            $dataStage = WorkFlowsStage::where('nombre', $itemConditionR->etapa)
                                                ->where('id_workflow', $itemConditionR->id_workflow)
                                                ->where('code_company', $dataUser->code_company)
                                                ->first();
                                            $dataFlujo = WorkFlows::where('id', $itemConditionR->id_workflow)
                                                ->where('code_company', $dataUser->code_company)
                                                ->first();
                                            $dataTaskStage = WorkFlowsTask::where('id_workflow', $idWorkFlow)
                                                ->where('id_workflow_stage', $dataStage->id)
                                                ->where('code_company', $dataUser->code_company)
                                                ->get();

                                            if (count($dataTaskStage) > 0) {
                                                foreach ($dataTaskStage as $item) {
                                                    $WorkFlowTaskExpedienteTable = new WorkFlowTaskExpedienteSinoe();
                                                    $WorkFlowTaskExpedienteTable->id_workflow = $item->id_workflow;
                                                    $WorkFlowTaskExpedienteTable->id_workflow_stage = $item->id_workflow_stage;
                                                    $WorkFlowTaskExpedienteTable->id_workflow_task = $item->id;
                                                    $WorkFlowTaskExpedienteTable->id_exp = $idExp;
                                                    $WorkFlowTaskExpedienteTable->nombre_etapa = $dataStage->nombre;
                                                    $WorkFlowTaskExpedienteTable->nombre_flujo = $dataFlujo->nombre;
                                                    $WorkFlowTaskExpedienteTable->nombre = $item->nombre;
                                                    $WorkFlowTaskExpedienteTable->descripcion = $item->descripcion;
                                                    $WorkFlowTaskExpedienteTable->dias_duracion = $item->dias_duracion;
                                                    $WorkFlowTaskExpedienteTable->dias_antes_venc = $item->dias_antes_venc;

                                                    $fechaDuracion = Carbon::now()->addDays($item->dias_duracion);
                                                    $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                                                    $fechaAlerta = Carbon::now()->addDays($item->dias_antes_venc);
                                                    $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                                                    $WorkFlowTaskExpedienteTable->fecha_limite = $fechaFormateadaDuracion;
                                                    $WorkFlowTaskExpedienteTable->fecha_alerta = $fechaFormateadaAlerta;
                                                    $WorkFlowTaskExpedienteTable->fecha_finalizada = null;

                                                    $WorkFlowTaskExpedienteTable->attached_files = $item->attached_files;
                                                    $WorkFlowTaskExpedienteTable->estado = 'En progreso';
                                                    $WorkFlowTaskExpedienteTable->prioridad = $item->prioridad;
                                                    $WorkFlowTaskExpedienteTable->code_user = Auth::user()->code_user;
                                                    $WorkFlowTaskExpedienteTable->code_company = Auth::user()->code_company;
                                                    $WorkFlowTaskExpedienteTable->save();

                                                    // Creando evento en google calendar
                                                    // if ($firstDataAccount){
                                                    //     // Actualización del usuario desde la base de datos
                                                    //     $this->client->setAccessToken($firstDataAccount->metadata);

                                                    //     // Si el token de acceso ha expirado, usar el token de actualización para obtener un nuevo token de acceso
                                                    //     if ($this->client->isAccessTokenExpired()) {
                                                    //         $this->client->fetchAccessTokenWithRefreshToken($firstDataAccount->refresh_token);
                                                    //         $newAccessToken = $this->client->getAccessToken();

                                                    //         // Actualizar el nuevo token de acceso en la base de datos
                                                    //         AccountGoogleCalendar::where('id', $firstDataAccount->id)->update([
                                                    //             'access_token' => $newAccessToken["access_token"],
                                                    //         ]);
                                                    //         $this->client->setAccessToken($newAccessToken);
                                                    //     }
                                                    //     // Hacer solicitudes a la API de Google Calendar
                                                    //     $service = new Google_Service_Calendar($this->client);


                                                    //     // ID del calendario obtenido desde la base de datos
                                                    //     $calendarId = $firstDataAccount->id_calendar;

                                                    //     // Crear un objeto EventDateTime para la fecha de inicio
                                                    //     $inicioEvento = new EventDateTime();
                                                    //     $inicioEvento->setDate($fechaFormateadaDuracion);
                                                    //     $inicioEvento->setTimeZone('America/Lima');
                                                    //     // Crear un objeto EventDateTime para la fecha de end
                                                    //     $endEvento = new EventDateTime();
                                                    //     $endEvento->setDate($fechaFormateadaDuracion);
                                                    //     $endEvento->setTimeZone('America/Lima');

                                                    //     // Crear el creador del evento
                                                    //     $eventCreator = new EventCreator();

                                                    //     $eventCreator->setEmail(config('app.iamcalendar'));
                                                    //     $eventCreator->setDisplayName('Temis');

                                                    //     // Crear un nuevo evento
                                                    //     $evento = new \Google\Service\Calendar\Event();

                                                    //     $evento->setCreator($eventCreator);
                                                    //     $evento->setSummary($item->nombre);
                                                    //     $evento->setDescription($item->descripcion);
                                                    //     $evento->setStart($inicioEvento);
                                                    //     $evento->setEnd($endEvento);
                                                    //     $evento->setColorId(7);

                                                    //     $invitados = [];
                                                    //     if ($userPartes){
                                                    //         foreach ($userPartes as $key => $value) {
                                                    //             if ($value->code_user !== $dataUser->code_user){
                                                    //                 $invitados[] = ["email" => $value->email];
                                                    //             }
                                                    //         }
                                                    //     }
                                                    //     $evento->setAttendees($invitados);

                                                    //     // Crear el evento
                                                    //     $eventsData =$service->events->insert($calendarId, $evento);
                                                    //     $eventsDataId = $eventsData->getId();
                                                    //     $metaData = [
                                                    //         "calendarId" => $dataAccount->id_calendar,
                                                    //         "eventsId" => $eventsDataId,
                                                    //         "code_user" => $firstDataAccount->code_user,
                                                    //         "code_code_company" => $firstDataAccount->code_code_company,
                                                    //         "account_id" => $firstDataAccount->id
                                                    //     ];
                                                    //     $WorkFlowTaskExpedienteTable->metadata = json_encode($metaData);
                                                    //     $WorkFlowTaskExpedienteTable->save();
                                                    // }
                                                }
                                            }
                                        }
                                    }
                                    // ? TODAS FINALIZADAS, CON AL MENOS UNA APROBADA
                                    if ($itemConditionR->condicion == "Todas finalizadas, con al menos una aprobada") {
                                        var_dump('una aprobada');
                                        if ($stageCountRechazada == count($stageCount) - 1) {
                                            $dataStage = WorkFlowsStage::where('nombre', $itemConditionR->etapa)
                                                ->where('id_workflow', $itemConditionR->id_workflow)
                                                ->where('code_company', $dataUser->code_company)
                                                ->first();
                                            $dataFlujo = WorkFlows::where('id', $itemConditionR->id_workflow)
                                                ->where('code_company', $dataUser->code_company)
                                                ->first();
                                            $dataTaskStage = WorkFlowsTask::where('id_workflow', $idWorkFlow)
                                                ->where('id_workflow_stage', $dataStage->id)
                                                ->where('code_company', $dataUser->code_company)
                                                ->get();

                                            if (count($dataTaskStage) > 0) {
                                                foreach ($dataTaskStage as $item) {
                                                    $WorkFlowTaskExpedienteTable = new WorkFlowTaskExpedienteSinoe();
                                                    $WorkFlowTaskExpedienteTable->id_workflow = $item->id_workflow;
                                                    $WorkFlowTaskExpedienteTable->id_workflow_stage = $item->id_workflow_stage;
                                                    $WorkFlowTaskExpedienteTable->id_workflow_task = $item->id;
                                                    $WorkFlowTaskExpedienteTable->id_exp = $idExp;
                                                    $WorkFlowTaskExpedienteTable->nombre_etapa = $dataStage->nombre;
                                                    $WorkFlowTaskExpedienteTable->nombre_flujo = $dataFlujo->nombre;
                                                    $WorkFlowTaskExpedienteTable->nombre = $item->nombre;
                                                    $WorkFlowTaskExpedienteTable->descripcion = $item->descripcion;
                                                    $WorkFlowTaskExpedienteTable->dias_duracion = $item->dias_duracion;
                                                    $WorkFlowTaskExpedienteTable->dias_antes_venc = $item->dias_antes_venc;

                                                    $fechaDuracion = Carbon::now()->addDays($item->dias_duracion);
                                                    $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                                                    $fechaAlerta = Carbon::now()->addDays($item->dias_antes_venc);
                                                    $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                                                    $WorkFlowTaskExpedienteTable->fecha_limite = $fechaFormateadaDuracion;
                                                    $WorkFlowTaskExpedienteTable->fecha_alerta = $fechaFormateadaAlerta;
                                                    $WorkFlowTaskExpedienteTable->fecha_finalizada = null;

                                                    $WorkFlowTaskExpedienteTable->attached_files = $item->attached_files;
                                                    $WorkFlowTaskExpedienteTable->estado = 'En progreso';
                                                    $WorkFlowTaskExpedienteTable->prioridad = $item->prioridad;
                                                    $WorkFlowTaskExpedienteTable->code_user = Auth::user()->code_user;
                                                    $WorkFlowTaskExpedienteTable->code_company = Auth::user()->code_company;
                                                    $WorkFlowTaskExpedienteTable->save();

                                                    // Creando evento en google calendar
                                                    // if ($firstDataAccount){
                                                    //     // Actualización del usuario desde la base de datos
                                                    //     $this->client->setAccessToken($firstDataAccount->metadata);

                                                    //     // Si el token de acceso ha expirado, usar el token de actualización para obtener un nuevo token de acceso
                                                    //     if ($this->client->isAccessTokenExpired()) {
                                                    //         $this->client->fetchAccessTokenWithRefreshToken($firstDataAccount->refresh_token);
                                                    //         $newAccessToken = $this->client->getAccessToken();

                                                    //         // Actualizar el nuevo token de acceso en la base de datos
                                                    //         AccountGoogleCalendar::where('id', $firstDataAccount->id)->update([
                                                    //             'access_token' => $newAccessToken["access_token"],
                                                    //         ]);
                                                    //         $this->client->setAccessToken($newAccessToken);
                                                    //     }
                                                    //     // Hacer solicitudes a la API de Google Calendar
                                                    //     $service = new Google_Service_Calendar($this->client);


                                                    //     // ID del calendario obtenido desde la base de datos
                                                    //     $calendarId = $firstDataAccount->id_calendar;

                                                    //     // Crear un objeto EventDateTime para la fecha de inicio
                                                    //     $inicioEvento = new EventDateTime();
                                                    //     $inicioEvento->setDate($fechaFormateadaDuracion);
                                                    //     $inicioEvento->setTimeZone('America/Lima');
                                                    //     // Crear un objeto EventDateTime para la fecha de end
                                                    //     $endEvento = new EventDateTime();
                                                    //     $endEvento->setDate($fechaFormateadaDuracion);
                                                    //     $endEvento->setTimeZone('America/Lima');

                                                    //     // Crear el creador del evento
                                                    //     $eventCreator = new EventCreator();

                                                    //     $eventCreator->setEmail(config('app.iamcalendar'));
                                                    //     $eventCreator->setDisplayName('Temis');

                                                    //     // Crear un nuevo evento
                                                    //     $evento = new \Google\Service\Calendar\Event();

                                                    //     $evento->setCreator($eventCreator);
                                                    //     $evento->setSummary($item->nombre);
                                                    //     $evento->setDescription($item->descripcion);
                                                    //     $evento->setStart($inicioEvento);
                                                    //     $evento->setEnd($endEvento);
                                                    //     $evento->setColorId(7);

                                                    //     $invitados = [];
                                                    //     if ($userPartes){
                                                    //         foreach ($userPartes as $key => $value) {
                                                    //             if ($value->code_user !== $dataUser->code_user){
                                                    //                 $invitados[] = ["email" => $value->email];
                                                    //             }
                                                    //         }
                                                    //     }
                                                    //     $evento->setAttendees($invitados);

                                                    //     // Crear el evento
                                                    //     $eventsData =$service->events->insert($calendarId, $evento);
                                                    //     $eventsDataId = $eventsData->getId();
                                                    //     $metaData = [
                                                    //         "calendarId" => $dataAccount->id_calendar,
                                                    //         "eventsId" => $eventsDataId,
                                                    //         "code_user" => $firstDataAccount->code_user,
                                                    //         "code_code_company" => $firstDataAccount->code_code_company,
                                                    //         "account_id" => $firstDataAccount->id
                                                    //     ];
                                                    //     $WorkFlowTaskExpedienteTable->metadata = json_encode($metaData);
                                                    //     $WorkFlowTaskExpedienteTable->save();
                                                    // }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function saveCommentFlujoSinoe()
    {

        // $idTask = request()->input('idTask');
        // $idExp = request()->input('idExp');
        // $comment = request()->input('comment');

        $id = request()->input("id");
        $idExp = request()->input("idExp");
        $idWorkFlow = request()->input("idworkFlow");
        $idWorkFlowTask = request()->input("idworkFlowTask");
        $idWorkFlowStage = request()->input("idworkFlowStage");
        $comment = request()->input("comment");


        $idUser = Auth()->id();
        $date = date("Y-m-d H:i:s");

        $dataUser = User::where('id', $idUser)->first();
        $existTask = WorkFlowTaskExpedienteSinoe::where('id_workflow_task', $idWorkFlowTask)
            ->where('id_workflow_stage', $idWorkFlowStage)
            ->where('id_workflow', $idWorkFlow)
            ->first();
        if ($dataUser && $existTask) {
            $newData = [
                'comment' => $comment,
                'id_exp' => $idExp,
                'id_task' => $idWorkFlowTask,
                'id_flujo' => $idWorkFlow,
                'id_stage' => $idWorkFlowStage,
                'date' => $date,
                'entidad' => 'sinoe',
                'code_user' => $dataUser->code_user,
                'code_company' => $dataUser->code_company,
                'metadata' => null,

            ];

            $insertedId = DB::table('comment_task_flujo_sinoes')->insertGetId($newData);
            return response()->json($newData = [
                'comment' => $comment,
                'user' => $dataUser->name . ', ' . $dataUser->lastname,
                'date' => $date,
                'id' => $insertedId,
            ]);
        }
        return response()->json('error');
    }

    public function deleteCommentFlujoSinoe()
    {
        $id = request()->input('idC');
        $dataUser = User::where('id', Auth()->id())->first();
        $dataComment = CommentTaskFlujoSinoe::where('id', $id)->get()->first();
        if ($dataUser->code_company == $dataComment->code_company) {
            CommentTaskFlujoSinoe::where('id', $id)->delete();
            return response()->json('CommentDelete');
        }
        return response()->json('ErrorDelete');
    }
}
