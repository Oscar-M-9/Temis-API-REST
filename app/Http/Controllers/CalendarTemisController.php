<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\EventSuggestion;
use App\Models\TaskExpediente;
use App\Models\TaskExpedienteSinoe;
use App\Models\TaskIndecopi;
use App\Models\TaskSuprema;
use App\Models\UserParte;
use App\Models\WorkFlowTaskExpediente;
use App\Models\WorkFlowTaskExpedienteSinoe;
use App\Models\WorkFlowTaskIndecopi;
use App\Models\WorkFlowTaskSuprema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarTemisController extends Controller
{
    //

    /**
     * Obtener datos del calendario
     *
     * @OA\Get(
     *     path="/api/calendar",
     *     tags={"Calendario"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Datos del calendario",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Eventos"),
     *             @OA\Property(property="eventSuggestion", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="fecha", type="string", format="date", example="2023-12-20"),
     *                     @OA\Property(property="titulo", type="string", example="Fecha demo"),
     *                     @OA\Property(property="descripcion", type="string", example="Se registró esta fecha como demo"),
     *                     @OA\Property(property="entidad", type="string", example="judicial"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-21T03:17:41.000000Z")
     *                 )
     *             ),
     *             @OA\Property(property="eventsExpedientes", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="title", type="string", example="Tarea 1"),
     *                     @OA\Property(property="start", type="string", format="date", example="2023-12-23"),
     *                     @OA\Property(property="fecha", type="string", format="date", example="2023-12-23"),
     *                     @OA\Property(property="prioridad", type="string", example="Media"),
     *                     @OA\Property(property="entidad", type="string", example="CEJ Judicial"),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="description", type="string", example="Demo de tarea en expediente"),
     *                     @OA\Property(property="name", type="string", nullable=true),
     *                     @OA\Property(property="lastName", type="string", nullable=true),
     *                     @OA\Property(property="nameCompany", type="string", example="demo"),
     *                     @OA\Property(property="typeContact", type="string", example="Empresa"),
     *                     @OA\Property(property="nExpediente", type="string", example="01667-2022-0-1001-JR-CI-06"),
     *                     @OA\Property(property="backgroundColor", type="string", example="#4c9ce2"),
     *                     @OA\Property(property="textColor", type="string", example="#FFF"),
     *                     @OA\Property(property="borderColor", type="string", example="#1c55b0"),
     *                     @OA\Property(property="editable", type="boolean", example=false)
     *                 )
     *             ),
     *             @OA\Property(property="workFlowTaskExpedientes", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=1),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="tareaa"),
     *                     @OA\Property(property="descripcion", type="string", example="Responsabilidad de todos"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=6),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=4),
     *                     @OA\Property(property="fecha_limite", type="string", format="date", example="2023-12-27"),
     *                     @OA\Property(property="fecha_alerta", type="string", format="date", example="2023-12-25"),
     *                     @OA\Property(property="fecha_finalizada", type="string", nullable=true, format="date-time", example=null),
     *                     @OA\Property(property="attached_files", type="string", nullable=true),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="prioridad", type="string", example="Baja"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="string", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-21T15:50:06.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-12-21T15:50:06.000000Z"),
     *                     @OA\Property(property="n_expediente", type="string", example="01667-2022-0-1001-JR-CI-06"),
     *                     @OA\Property(property="name", type="string", nullable=true),
     *                     @OA\Property(property="last_name", type="string", nullable=true),
     *                     @OA\Property(property="name_company", type="string", example="demo"),
     *                     @OA\Property(property="type_contact", type="string", example="Empresa")
     *                 )
     *             ),
     *             @OA\Property(property="workFlowTaskSuprema", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=1),
     *                     @OA\Property(property="id_exp", type="integer", example=9),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="tareaa"),
     *                     @OA\Property(property="descripcion", type="string", example="Responsabilidad de todos"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=6),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=4),
     *                     @OA\Property(property="fecha_limite", type="string", format="date", example="2024-01-26"),
     *                     @OA\Property(property="fecha_alerta", type="string", format="date", example="2024-01-24"),
     *                     @OA\Property(property="fecha_finalizada", type="string", nullable=true, format="date-time", example=null),
     *                     @OA\Property(property="attached_files", type="string", nullable=true),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="prioridad", type="string", example="Baja"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="string", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-21T00:58:49.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-21T00:58:49.000000Z"),
     *                     @OA\Property(property="n_expediente", type="string", example="02398-2010-0-5001-SU-PE-01"),
     *                     @OA\Property(property="name", type="string", nullable=true),
     *                     @OA\Property(property="last_name", type="string", nullable=true),
     *                     @OA\Property(property="name_company", type="string", example="demo"),
     *                     @OA\Property(property="type_contact", type="string", example="Empresa")
     *                 )
     *             ),
     *             @OA\Property(property="workFlowTaskIndecopi", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=1),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="tareaa"),
     *                     @OA\Property(property="descripcion", type="string", example="Responsabilidad de todos"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=6),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=4),
     *                     @OA\Property(property="fecha_limite", type="string", format="date", example="2024-01-26"),
     *                     @OA\Property(property="fecha_alerta", type="string", format="date", example="2024-01-24"),
     *                     @OA\Property(property="fecha_finalizada", type="string", nullable=true, format="date-time", example=null),
     *                     @OA\Property(property="attached_files", type="string", nullable=true),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="prioridad", type="string", example="Baja"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="string", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-21T00:58:49.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-21T00:58:49.000000Z"),
     *                     @OA\Property(property="n_expediente", type="string", example="02398-2010-0-5001-SU-PE-01"),
     *                     @OA\Property(property="name", type="string", nullable=true),
     *                     @OA\Property(property="last_name", type="string", nullable=true),
     *                     @OA\Property(property="name_company", type="string", example="demo"),
     *                     @OA\Property(property="type_contact", type="string", example="Empresa")
     *                 )
     *             ),
     *             @OA\Property(property="workFlowTaskSinoe", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=1),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="tareaa"),
     *                     @OA\Property(property="descripcion", type="string", example="Responsabilidad de todos"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=6),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=4),
     *                     @OA\Property(property="fecha_limite", type="string", format="date", example="2024-01-26"),
     *                     @OA\Property(property="fecha_alerta", type="string", format="date", example="2024-01-24"),
     *                     @OA\Property(property="fecha_finalizada", type="string", nullable=true, format="date-time", example=null),
     *                     @OA\Property(property="attached_files", type="string", nullable=true),
     *                     @OA\Property(property="estado", type="string", example="Aprobada"),
     *                     @OA\Property(property="prioridad", type="string", example="Baja"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="string", example="finalizado"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-21T00:58:49.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-21T00:58:49.000000Z"),
     *                     @OA\Property(property="n_expediente", type="string", example="02398-2010-0-5001-SU-PE-01"),
     *                     @OA\Property(property="name", type="string", nullable=true),
     *                     @OA\Property(property="last_name", type="string", nullable=true),
     *                     @OA\Property(property="name_company", type="string", example="demo"),
     *                     @OA\Property(property="type_contact", type="string", example="Empresa")
     *                 )
     *             ),
     *         ),
     *     ),
     * )
     */
    public function view()
    {
        // Eventos de Sugerencia de la IA
        $eventSuggestion = EventSuggestion::select(
            'fecha',
            'titulo',
            'descripcion',
            'entidad',
            'created_at',
        )
            ->where('code_company', Auth::user()->code_company)
            ->get();

        // $jsonEventSuggestion = $eventSuggestion->toJson();

        // * Tareas de CEJ Judicial

        $taskExpedientes = TaskExpediente::join('user_partes', 'task_expedientes.id_exp', '=', 'user_partes.id_exp')
            ->join('expedientes', 'task_expedientes.id_exp', '=', 'expedientes.id')
            ->join('clientes', 'expedientes.id_client', '=', 'clientes.id')
            ->where('user_partes.code_company', Auth::user()->code_company)
            ->where('user_partes.code_user', Auth::user()->code_user)
            ->where('user_partes.entidad', 'judicial')
            ->where('task_expedientes.code_company', Auth::user()->code_company)
            ->select(
                'task_expedientes.*',
                'expedientes.n_expediente',
                'clientes.name',
                'clientes.last_name',
                'clientes.name_company',
                'clientes.type_contact',
            )
            ->get();



        $workFlowTaskExpedientes = WorkFlowTaskExpediente::join('user_partes', 'work_flow_task_expedientes.id_exp', '=', 'user_partes.id_exp')
            ->join('expedientes', 'work_flow_task_expedientes.id_exp', '=', 'expedientes.id')
            ->join('clientes', 'expedientes.id_client', '=', 'clientes.id')
            ->where('user_partes.code_company', Auth::user()->code_company)
            ->where('user_partes.code_user', Auth::user()->code_user)
            ->where('user_partes.entidad', 'judicial')
            ->where('work_flow_task_expedientes.code_company', Auth::user()->code_company)
            ->select(
                'work_flow_task_expedientes.*',
                'expedientes.n_expediente',
                'clientes.name',
                'clientes.last_name',
                'clientes.name_company',
                'clientes.type_contact',
            )
            ->get();

        // * Tareas de CEJ Suprema

        $taskSuprema = TaskSuprema::join('user_partes', 'task_supremas.id_exp', '=', 'user_partes.id_exp')
            ->join('corte_supremas', 'task_supremas.id_exp', '=', 'corte_supremas.id')
            ->join('clientes', 'corte_supremas.id_client', '=', 'clientes.id')
            ->where('user_partes.code_company', Auth::user()->code_company)
            ->where('user_partes.code_user', Auth::user()->code_user)
            ->where('user_partes.entidad', 'suprema')
            ->where('task_supremas.code_company', Auth::user()->code_company)
            ->select(
                'task_supremas.*',
                'corte_supremas.n_expediente',
                'clientes.name',
                'clientes.last_name',
                'clientes.name_company',
                'clientes.type_contact',
            )
            ->get();


        $workFlowTaskSuprema = WorkFlowTaskSuprema::join('user_partes', 'work_flow_task_supremas.id_exp', '=', 'user_partes.id_exp')
            ->join('corte_supremas', 'work_flow_task_supremas.id_exp', '=', 'corte_supremas.id')
            ->join('clientes', 'corte_supremas.id_client', '=', 'clientes.id')
            ->where('user_partes.code_company', Auth::user()->code_company)
            ->where('user_partes.code_user', Auth::user()->code_user)
            ->where('user_partes.entidad', 'suprema')
            ->where('work_flow_task_supremas.code_company', Auth::user()->code_company)
            ->select(
                'work_flow_task_supremas.*',
                'corte_supremas.n_expediente',
                'clientes.name',
                'clientes.last_name',
                'clientes.name_company',
                'clientes.type_contact',
            )
            ->get();

        // * Tareas de Indecopi

        $taskIndecopi = TaskIndecopi::join('user_partes', 'task_indecopis.id_exp', '=', 'user_partes.id_exp')
            ->join('indecopis', 'task_indecopis.id_exp', '=', 'indecopis.id')
            ->join('clientes', 'indecopis.id_client', '=', 'clientes.id')
            ->where('user_partes.code_company', Auth::user()->code_company)
            ->where('user_partes.code_user', Auth::user()->code_user)
            ->where('user_partes.entidad', 'indecopi')
            ->where('task_indecopis.code_company', Auth::user()->code_company)
            ->select(
                'task_indecopis.*',
                'indecopis.numero',
                'clientes.name',
                'clientes.last_name',
                'clientes.name_company',
                'clientes.type_contact',
            )
            ->get();


        $workFlowTaskIndecopi = WorkFlowTaskIndecopi::join('user_partes', 'work_flow_task_indecopis.id_exp', '=', 'user_partes.id_exp')
            ->join('indecopis', 'work_flow_task_indecopis.id_exp', '=', 'indecopis.id')
            ->join('clientes', 'indecopis.id_client', '=', 'clientes.id')
            ->where('user_partes.code_company', Auth::user()->code_company)
            ->where('user_partes.code_user', Auth::user()->code_user)
            ->where('user_partes.entidad', 'indecopi')
            ->where('work_flow_task_indecopis.code_company', Auth::user()->code_company)
            ->select(
                'work_flow_task_indecopis.*',
                'indecopis.numero',
                'clientes.name',
                'clientes.last_name',
                'clientes.name_company',
                'clientes.type_contact',
            )
            ->get();

        // * Tareas de Sinoe

        $taskSinoe = TaskExpedienteSinoe::join('user_partes', 'task_expediente_sinoes.id_exp', '=', 'user_partes.id_exp')
            ->join('expediente_sinoes', 'task_expediente_sinoes.id_exp', '=', 'expediente_sinoes.id')
            ->join('clientes', 'expediente_sinoes.id_client', '=', 'clientes.id')
            ->where('user_partes.code_company', Auth::user()->code_company)
            ->where('user_partes.code_user', Auth::user()->code_user)
            ->where('user_partes.entidad', 'sinoe')
            ->where('task_expediente_sinoes.code_company', Auth::user()->code_company)
            ->select(
                'task_expediente_sinoes.*',
                'expediente_sinoes.n_expediente',
                'clientes.name',
                'clientes.last_name',
                'clientes.name_company',
                'clientes.type_contact',
            )
            ->get();


        $workFlowTaskSinoe = WorkFlowTaskExpedienteSinoe::join('user_partes', 'work_flow_task_expediente_sinoes.id_exp', '=', 'user_partes.id_exp')
            ->join('expediente_sinoes', 'work_flow_task_expediente_sinoes.id_exp', '=', 'expediente_sinoes.id')
            ->join('clientes', 'expediente_sinoes.id_client', '=', 'clientes.id')
            ->where('user_partes.code_company', Auth::user()->code_company)
            ->where('user_partes.code_user', Auth::user()->code_user)
            ->where('user_partes.entidad', 'sinoe')
            ->where('work_flow_task_expediente_sinoes.code_company', Auth::user()->code_company)
            ->select(
                'work_flow_task_expediente_sinoes.*',
                'expediente_sinoes.n_expediente',
                'clientes.name',
                'clientes.last_name',
                'clientes.name_company',
                'clientes.type_contact',
            )
            ->get();

        $eventsExpedientes = [];

        foreach ($taskExpedientes as $key => $value) {
            $eventsExpedientes[] = [
                'title' => $value->nombre,
                'start' => $value->fecha_limite,
                'fecha' => $value->fecha_limite,
                'prioridad' => $value->prioridad,
                'entidad' => 'CEJ Judicial',
                'estado' => $value->estado,
                'description' => $value->descripcion,
                'name' => $value->name,
                'lastName' => $value->last_name,
                'nameCompany' => $value->name_company,
                'typeContact' => $value->type_contact,
                'nExpediente' => $value->n_expediente,
                'backgroundColor' => '#4c9ce2',
                'textColor' => '#FFF',
                'borderColor' => '#1c55b0',
                'editable' => false,
            ];
        }
        foreach ($taskIndecopi as $key => $value) {
            $eventsExpedientes[] = [
                'title' => $value->nombre,
                'start' => $value->fecha_limite,
                'fecha' => $value->fecha_limite,
                'prioridad' => $value->prioridad,
                'entidad' => 'Indecopi',
                'estado' => $value->estado,
                'description' => $value->descripcion,
                'name' => $value->name,
                'lastName' => $value->last_name,
                'nameCompany' => $value->name_company,
                'typeContact' => $value->type_contact,
                'nExpediente' => $value->numero,
                'backgroundColor' => '#4c9ce2',
                'textColor' => '#FFF',
                'borderColor' => '#1c55b0',
                'editable' => false,
            ];
        }
        foreach ($taskSuprema as $key => $value) {
            $eventsExpedientes[] = [
                'title' => $value->nombre,
                'start' => $value->fecha_limite,
                'fecha' => $value->fecha_limite,
                'prioridad' => $value->prioridad,
                'entidad' => 'CEJ Suprema',
                'estado' => $value->estado,
                'description' => $value->descripcion,
                'name' => $value->name,
                'lastName' => $value->last_name,
                'nameCompany' => $value->name_company,
                'typeContact' => $value->type_contact,
                'nExpediente' => $value->n_expediente,
                'backgroundColor' => '#4c9ce2',
                'textColor' => '#FFF',
                'borderColor' => '#1c55b0',
                'editable' => false,
            ];
        }
        foreach ($taskSinoe as $key => $value) {
            $eventsExpedientes[] = [
                'title' => $value->nombre,
                'start' => $value->fecha_limite,
                'fecha' => $value->fecha_limite,
                'prioridad' => $value->prioridad,
                'entidad' => 'Sinoe',
                'estado' => $value->estado,
                'description' => $value->descripcion,
                'name' => $value->name,
                'lastName' => $value->last_name,
                'nameCompany' => $value->name_company,
                'typeContact' => $value->type_contact,
                'nExpediente' => $value->n_expediente,
                'backgroundColor' => '#4c9ce2',
                'textColor' => '#FFF',
                'borderColor' => '#1c55b0',
                'editable' => false,
            ];
        }

        return response()->json([
            "status" => true,
            "message" => "Eventos",
            'eventSuggestion' => $eventSuggestion,
            'eventsExpedientes' => $eventsExpedientes,
            'workFlowTaskExpedientes' => $workFlowTaskExpedientes,
            'workFlowTaskSuprema' => $workFlowTaskSuprema,
            'workFlowTaskIndecopi' => $workFlowTaskIndecopi,
            'workFlowTaskSinoe' => $workFlowTaskSinoe,
        ], 200);
    }

    // public function getDataCalendar()
    // {
    //     // Eventos de Sugerencia de la IA
    //     $eventSuggestion = EventSuggestion::select(
    //         'id',
    //         'fecha',
    //         'titulo',
    //         'descripcion',
    //         'entidad',
    //         'created_at',
    //         'metadata',
    //     )
    //         ->where('code_company', Auth::user()->code_company)
    //         ->get();


    //     // * Tareas de CEJ Judicial

    //     $taskExpedientes = TaskExpediente::join('user_partes', 'task_expedientes.id_exp', '=', 'user_partes.id_exp')
    //         ->join('expedientes', 'task_expedientes.id_exp', '=', 'expedientes.id')
    //         ->join('clientes', 'expedientes.id_client', '=', 'clientes.id')
    //         ->where('user_partes.code_company', Auth::user()->code_company)
    //         ->where('user_partes.code_user', Auth::user()->code_user)
    //         ->where('user_partes.entidad', 'judicial')
    //         ->where('task_expedientes.code_company', Auth::user()->code_company)
    //         ->select(
    //             'task_expedientes.*',
    //             'expedientes.n_expediente',
    //             'clientes.name',
    //             'clientes.last_name',
    //             'clientes.name_company',
    //             'clientes.type_contact',
    //         )
    //         ->get();



    //     $workFlowTaskExpedientes = WorkFlowTaskExpediente::join('user_partes', 'work_flow_task_expedientes.id_exp', '=', 'user_partes.id_exp')
    //         ->join('expedientes', 'work_flow_task_expedientes.id_exp', '=', 'expedientes.id')
    //         ->join('clientes', 'expedientes.id_client', '=', 'clientes.id')
    //         ->where('user_partes.code_company', Auth::user()->code_company)
    //         ->where('user_partes.code_user', Auth::user()->code_user)
    //         ->where('user_partes.entidad', 'judicial')
    //         ->where('work_flow_task_expedientes.code_company', Auth::user()->code_company)
    //         ->select(
    //             'work_flow_task_expedientes.*',
    //             'expedientes.n_expediente',
    //             'clientes.name',
    //             'clientes.last_name',
    //             'clientes.name_company',
    //             'clientes.type_contact',
    //         )
    //         ->get();

    //     // * Tareas de CEJ Suprema

    //     $taskSuprema = TaskSuprema::join('user_partes', 'task_supremas.id_exp', '=', 'user_partes.id_exp')
    //         ->join('corte_supremas', 'task_supremas.id_exp', '=', 'corte_supremas.id')
    //         ->join('clientes', 'corte_supremas.id_client', '=', 'clientes.id')
    //         ->where('user_partes.code_company', Auth::user()->code_company)
    //         ->where('user_partes.code_user', Auth::user()->code_user)
    //         ->where('user_partes.entidad', 'suprema')
    //         ->where('task_supremas.code_company', Auth::user()->code_company)
    //         ->select(
    //             'task_supremas.*',
    //             'corte_supremas.n_expediente',
    //             'clientes.name',
    //             'clientes.last_name',
    //             'clientes.name_company',
    //             'clientes.type_contact',
    //         )
    //         ->get();


    //     $workFlowTaskSuprema = WorkFlowTaskSuprema::join('user_partes', 'work_flow_task_supremas.id_exp', '=', 'user_partes.id_exp')
    //         ->join('corte_supremas', 'work_flow_task_supremas.id_exp', '=', 'corte_supremas.id')
    //         ->join('clientes', 'corte_supremas.id_client', '=', 'clientes.id')
    //         ->where('user_partes.code_company', Auth::user()->code_company)
    //         ->where('user_partes.code_user', Auth::user()->code_user)
    //         ->where('user_partes.entidad', 'suprema')
    //         ->where('work_flow_task_supremas.code_company', Auth::user()->code_company)
    //         ->select(
    //             'work_flow_task_supremas.*',
    //             'corte_supremas.n_expediente',
    //             'clientes.name',
    //             'clientes.last_name',
    //             'clientes.name_company',
    //             'clientes.type_contact',
    //         )
    //         ->get();

    //     // * Tareas de Indecopi

    //     $taskIndecopi = TaskIndecopi::join('user_partes', 'task_indecopis.id_exp', '=', 'user_partes.id_exp')
    //         ->join('indecopis', 'task_indecopis.id_exp', '=', 'indecopis.id')
    //         ->join('clientes', 'indecopis.id_client', '=', 'clientes.id')
    //         ->where('user_partes.code_company', Auth::user()->code_company)
    //         ->where('user_partes.code_user', Auth::user()->code_user)
    //         ->where('user_partes.entidad', 'indecopi')
    //         ->where('task_indecopis.code_company', Auth::user()->code_company)
    //         ->select(
    //             'task_indecopis.*',
    //             'indecopis.numero',
    //             'clientes.name',
    //             'clientes.last_name',
    //             'clientes.name_company',
    //             'clientes.type_contact',
    //         )
    //         ->get();


    //     $workFlowTaskIndecopi = WorkFlowTaskIndecopi::join('user_partes', 'work_flow_task_indecopis.id_exp', '=', 'user_partes.id_exp')
    //         ->join('indecopis', 'work_flow_task_indecopis.id_exp', '=', 'indecopis.id')
    //         ->join('clientes', 'indecopis.id_client', '=', 'clientes.id')
    //         ->where('user_partes.code_company', Auth::user()->code_company)
    //         ->where('user_partes.code_user', Auth::user()->code_user)
    //         ->where('user_partes.entidad', 'indecopi')
    //         ->where('work_flow_task_indecopis.code_company', Auth::user()->code_company)
    //         ->select(
    //             'work_flow_task_indecopis.*',
    //             'indecopis.numero',
    //             'clientes.name',
    //             'clientes.last_name',
    //             'clientes.name_company',
    //             'clientes.type_contact',
    //         )
    //         ->get();

    //     // * Tareas de Sinoe

    //     $taskSinoe = TaskExpedienteSinoe::join('user_partes', 'task_expediente_sinoes.id_exp', '=', 'user_partes.id_exp')
    //         ->join('expediente_sinoes', 'task_expediente_sinoes.id_exp', '=', 'expediente_sinoes.id')
    //         ->join('clientes', 'expediente_sinoes.id_client', '=', 'clientes.id')
    //         ->where('user_partes.code_company', Auth::user()->code_company)
    //         ->where('user_partes.code_user', Auth::user()->code_user)
    //         ->where('user_partes.entidad', 'sinoe')
    //         ->where('task_expediente_sinoes.code_company', Auth::user()->code_company)
    //         ->select(
    //             'task_expediente_sinoes.*',
    //             'expediente_sinoes.n_expediente',
    //             'clientes.name',
    //             'clientes.last_name',
    //             'clientes.name_company',
    //             'clientes.type_contact',
    //         )
    //         ->get();


    //     $workFlowTaskSinoe = WorkFlowTaskExpedienteSinoe::join('user_partes', 'work_flow_task_expediente_sinoes.id_exp', '=', 'user_partes.id_exp')
    //         ->join('expediente_sinoes', 'work_flow_task_expediente_sinoes.id_exp', '=', 'expediente_sinoes.id')
    //         ->join('clientes', 'expediente_sinoes.id_client', '=', 'clientes.id')
    //         ->where('user_partes.code_company', Auth::user()->code_company)
    //         ->where('user_partes.code_user', Auth::user()->code_user)
    //         ->where('user_partes.entidad', 'sinoe')
    //         ->where('work_flow_task_expediente_sinoes.code_company', Auth::user()->code_company)
    //         ->select(
    //             'work_flow_task_expediente_sinoes.*',
    //             'expediente_sinoes.n_expediente',
    //             'clientes.name',
    //             'clientes.last_name',
    //             'clientes.name_company',
    //             'clientes.type_contact',
    //         )
    //         ->get();

    //     $eventsExpedientes = [];

    //     foreach ($taskExpedientes as $key => $value) {
    //         $eventsExpedientes[] = [
    //             'idExp' => $value->id,
    //             'title' => $value->nombre,
    //             'start' => $value->fecha_limite,
    //             'fecha' => $value->fecha_limite,
    //             'prioridad' => $value->prioridad,
    //             'estado' => $value->estado,
    //             'description' => $value->descripcion,
    //             'name' => $value->name,
    //             'lastName' => $value->last_name,
    //             'nameCompany' => $value->name_company,
    //             'typeContact' => $value->type_contact,
    //             'nExpediente' => $value->n_expediente,
    //             'backgroundColor' => '#4c9ce2',
    //             'textColor' => '#FFF',
    //             'borderColor' => '#1c55b0',
    //             'editable' => false,
    //         ];
    //     }
    //     foreach ($taskIndecopi as $key => $value) {
    //         $eventsExpedientes[] = [
    //             'idExp' => $value->id,
    //             'title' => $value->nombre,
    //             'start' => $value->fecha_limite,
    //             'fecha' => $value->fecha_limite,
    //             'prioridad' => $value->prioridad,
    //             'estado' => $value->estado,
    //             'description' => $value->descripcion,
    //             'name' => $value->name,
    //             'lastName' => $value->last_name,
    //             'nameCompany' => $value->name_company,
    //             'typeContact' => $value->type_contact,
    //             'nExpediente' => $value->numero,
    //             'backgroundColor' => '#4c9ce2',
    //             'textColor' => '#FFF',
    //             'borderColor' => '#1c55b0',
    //             'editable' => false,
    //         ];
    //     }
    //     foreach ($taskSuprema as $key => $value) {
    //         $eventsExpedientes[] = [
    //             'idExp' => $value->id,
    //             'title' => $value->nombre,
    //             'start' => $value->fecha_limite,
    //             'fecha' => $value->fecha_limite,
    //             'prioridad' => $value->prioridad,
    //             'estado' => $value->estado,
    //             'description' => $value->descripcion,
    //             'name' => $value->name,
    //             'lastName' => $value->last_name,
    //             'nameCompany' => $value->name_company,
    //             'typeContact' => $value->type_contact,
    //             'nExpediente' => $value->n_expediente,
    //             'backgroundColor' => '#4c9ce2',
    //             'textColor' => '#FFF',
    //             'borderColor' => '#1c55b0',
    //             'editable' => false,
    //         ];
    //     }
    //     foreach ($taskSinoe as $key => $value) {
    //         $eventsExpedientes[] = [
    //             'idExp' => $value->id,
    //             'title' => $value->nombre,
    //             'start' => $value->fecha_limite,
    //             'fecha' => $value->fecha_limite,
    //             'prioridad' => $value->prioridad,
    //             'estado' => $value->estado,
    //             'description' => $value->descripcion,
    //             'name' => $value->name,
    //             'lastName' => $value->last_name,
    //             'nameCompany' => $value->name_company,
    //             'typeContact' => $value->type_contact,
    //             'nExpediente' => $value->n_expediente,
    //             'backgroundColor' => '#4c9ce2',
    //             'textColor' => '#FFF',
    //             'borderColor' => '#1c55b0',
    //             'editable' => false,
    //         ];
    //     }

    //     return response()->json([
    //         'eventSuggestion' => $eventSuggestion,
    //         'eventsExpedientes' => $eventsExpedientes,
    //         'workFlowTaskExpedientes' => $workFlowTaskExpedientes,
    //         'workFlowTaskSuprema' => $workFlowTaskSuprema,
    //         'workFlowTaskIndecopi' => $workFlowTaskIndecopi,
    //         'workFlowTaskSinoe' => $workFlowTaskSinoe
    //     ]);
    // }

    /**
     * Eliminar evento del calendario
     *
     * @OA\Delete(
     *     path="/api/delete-calendar-event/{id}",
     *     tags={"Calendario"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del evento",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evento eliminado con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="El evento se ha eliminado con éxito"),
     *             @OA\Property(property="event", type="object",
     *                 @OA\Property(property="id", type="integer", example=10),
     *                 @OA\Property(property="fecha", type="string", example="2023-12-30"),
     *                 @OA\Property(property="titulo", type="string", example="Evento creado para la demo"),
     *                 @OA\Property(property="descripcion", type="string", example="qwertyuiop"),
     *                 @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                 @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                 @OA\Property(property="entidad", type="string", example="calendar"),
     *                 @OA\Property(property="estado", type="string", example=null),
     *                 @OA\Property(property="metadata", type="string", example=null),
     *                 @OA\Property(property="created_at", type="string", example="2023-12-29T15:21:13.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2023-12-29T15:21:13.000000Z")
     *             ),
     *             @OA\Property(property="id", type="integer", example="1"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontró el evento",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Se produjo un error al intentar eliminar el evento"),
     *             @OA\Property(property="event", type="string", example=null),
     *             @OA\Property(property="id", type="string", example="10")
     *         )
     *     )
     * )
     */


    public function deleteEventCalendar($id)
    {
        $eventSuggestion = EventSuggestion::find($id);
        if ($eventSuggestion && $id) {
            EventSuggestion::where('code_company', Auth::user()->code_company)
                ->where('id', $id)
                ->delete();

            return response()->json([
                "status" => true,
                "message" => "El evento se a eliminado con éxito",
                "event" => $eventSuggestion,
                "id" => $id,
            ], 200);
        }
        return response()->json([
            "status" => false,
            "message" => "Se produjo un error al intentar eliminar el evento",
            "event" => "",
            "id" => $id,
        ], 404);
    }

    /**
     * Obtener datos del calendario por cliente
     *
     * @OA\Get(
     *     path="/api/calendar-client/{id}",
     *     tags={"Calendario"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del cliente",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos del calendario",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Eventos del cliente"),
     *             @OA\Property(property="cliente", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="type_contact", type="string", example="Empresa"),
     *                  @OA\Property(property="name", type="string", example=null),
     *                  @OA\Property(property="last_name", type="string", example=null),
     *                  @OA\Property(property="dni", type="string", example=null),
     *                  @OA\Property(property="birthdate", type="date", format="date", example=null),
     *                  @OA\Property(property="company", type="string", example=null),
     *                  @OA\Property(property="name_company", type="string", example="demo"),
     *                  @OA\Property(property="ruc", type="string", example="1234568790875746"),
     *                  @OA\Property(property="email", type="string", example="{'email':'alnuawd@sagssdg.adgs','type_email':'Trabajo','email2':null,'type_email2':'Trabajo'}"),
     *                  @OA\Property(property="phone", type="string", example="{'phone':'2354325','type_phone':'Trabajo','phone2':null,'type_phone2':'Trabajo'}"),
     *                  @OA\Property(property="address", type="string", example="{'country':'Perú','departamento':'20','provincia':'152','distrito':'1534','street':'qwetryuio'}"),
     *                  @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                  @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                  @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-25T21:49:39.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-25T21:49:40.000000Z")
     *             ),
     *             @OA\Property(property="eventSuggestion", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="fecha", type="string", format="date", example="2023-12-20"),
     *                     @OA\Property(property="titulo", type="string", example="Fecha demo"),
     *                     @OA\Property(property="descripcion", type="string", example="Se registró esta fecha como demo"),
     *                     @OA\Property(property="entidad", type="string", example="judicial"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-21T03:17:41.000000Z")
     *                 )
     *             ),
     *             @OA\Property(property="eventsExpedientes", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="title", type="string", example="Tarea 1"),
     *                     @OA\Property(property="start", type="string", format="date", example="2023-12-23"),
     *                     @OA\Property(property="fecha", type="string", format="date", example="2023-12-23"),
     *                     @OA\Property(property="prioridad", type="string", example="Media"),
     *                     @OA\Property(property="entidad", type="string", example="CEJ Judicial"),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="description", type="string", example="Demo de tarea en expediente"),
     *                     @OA\Property(property="name", type="string", nullable=true),
     *                     @OA\Property(property="lastName", type="string", nullable=true),
     *                     @OA\Property(property="nameCompany", type="string", example="demo"),
     *                     @OA\Property(property="typeContact", type="string", example="Empresa"),
     *                     @OA\Property(property="nExpediente", type="string", example="01667-2022-0-1001-JR-CI-06"),
     *                     @OA\Property(property="backgroundColor", type="string", example="#4c9ce2"),
     *                     @OA\Property(property="textColor", type="string", example="#FFF"),
     *                     @OA\Property(property="borderColor", type="string", example="#1c55b0"),
     *                     @OA\Property(property="editable", type="boolean", example=false)
     *                 )
     *             ),
     *             @OA\Property(property="workFlowTaskExpedientes", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=1),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="tareaa"),
     *                     @OA\Property(property="descripcion", type="string", example="Responsabilidad de todos"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=6),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=4),
     *                     @OA\Property(property="fecha_limite", type="string", format="date", example="2023-12-27"),
     *                     @OA\Property(property="fecha_alerta", type="string", format="date", example="2023-12-25"),
     *                     @OA\Property(property="fecha_finalizada", type="string", nullable=true, format="date-time", example=null),
     *                     @OA\Property(property="attached_files", type="string", nullable=true),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="prioridad", type="string", example="Baja"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="string", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-21T15:50:06.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-12-21T15:50:06.000000Z"),
     *                     @OA\Property(property="n_expediente", type="string", example="01667-2022-0-1001-JR-CI-06"),
     *                     @OA\Property(property="name", type="string", nullable=true),
     *                     @OA\Property(property="last_name", type="string", nullable=true),
     *                     @OA\Property(property="name_company", type="string", example="demo"),
     *                     @OA\Property(property="type_contact", type="string", example="Empresa")
     *                 )
     *             ),
     *             @OA\Property(property="workFlowTaskSuprema", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=1),
     *                     @OA\Property(property="id_exp", type="integer", example=9),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="tareaa"),
     *                     @OA\Property(property="descripcion", type="string", example="Responsabilidad de todos"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=6),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=4),
     *                     @OA\Property(property="fecha_limite", type="string", format="date", example="2024-01-26"),
     *                     @OA\Property(property="fecha_alerta", type="string", format="date", example="2024-01-24"),
     *                     @OA\Property(property="fecha_finalizada", type="string", nullable=true, format="date-time", example=null),
     *                     @OA\Property(property="attached_files", type="string", nullable=true),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="prioridad", type="string", example="Baja"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="string", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-21T00:58:49.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-21T00:58:49.000000Z"),
     *                     @OA\Property(property="n_expediente", type="string", example="02398-2010-0-5001-SU-PE-01"),
     *                     @OA\Property(property="name", type="string", nullable=true),
     *                     @OA\Property(property="last_name", type="string", nullable=true),
     *                     @OA\Property(property="name_company", type="string", example="demo"),
     *                     @OA\Property(property="type_contact", type="string", example="Empresa")
     *                 )
     *             ),
     *             @OA\Property(property="workFlowTaskIndecopi", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=1),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="tareaa"),
     *                     @OA\Property(property="descripcion", type="string", example="Responsabilidad de todos"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=6),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=4),
     *                     @OA\Property(property="fecha_limite", type="string", format="date", example="2024-01-26"),
     *                     @OA\Property(property="fecha_alerta", type="string", format="date", example="2024-01-24"),
     *                     @OA\Property(property="fecha_finalizada", type="string", nullable=true, format="date-time", example=null),
     *                     @OA\Property(property="attached_files", type="string", nullable=true),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="prioridad", type="string", example="Baja"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="string", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-21T00:58:49.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-21T00:58:49.000000Z"),
     *                     @OA\Property(property="n_expediente", type="string", example="02398-2010-0-5001-SU-PE-01"),
     *                     @OA\Property(property="name", type="string", nullable=true),
     *                     @OA\Property(property="last_name", type="string", nullable=true),
     *                     @OA\Property(property="name_company", type="string", example="demo"),
     *                     @OA\Property(property="type_contact", type="string", example="Empresa")
     *                 )
     *             ),
     *             @OA\Property(property="workFlowTaskSinoe", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=1),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="tareaa"),
     *                     @OA\Property(property="descripcion", type="string", example="Responsabilidad de todos"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=6),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=4),
     *                     @OA\Property(property="fecha_limite", type="string", format="date", example="2024-01-26"),
     *                     @OA\Property(property="fecha_alerta", type="string", format="date", example="2024-01-24"),
     *                     @OA\Property(property="fecha_finalizada", type="string", nullable=true, format="date-time", example=null),
     *                     @OA\Property(property="attached_files", type="string", nullable=true),
     *                     @OA\Property(property="estado", type="string", example="Aprobada"),
     *                     @OA\Property(property="prioridad", type="string", example="Baja"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="string", example="finalizado"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-21T00:58:49.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-21T00:58:49.000000Z"),
     *                     @OA\Property(property="n_expediente", type="string", example="02398-2010-0-5001-SU-PE-01"),
     *                     @OA\Property(property="name", type="string", nullable=true),
     *                     @OA\Property(property="last_name", type="string", nullable=true),
     *                     @OA\Property(property="name_company", type="string", example="demo"),
     *                     @OA\Property(property="type_contact", type="string", example="Empresa")
     *                 )
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontró el evento del cliente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cliente no encontrado"),
     *             @OA\Property(property="id", type="string", example="2")
     *         )
     *     )
     * )
     */
    public function getCalendarClient($id)
    {
        $cliente = Cliente::where('id', $id)
            ->where('code_company', Auth::user()->code_company)
            ->first();

        if ($cliente) {
            // // Eventos de Sugerencia de la IA
            // $eventSuggestion = EventSuggestion::select(
            //     'fecha',
            //     'titulo',
            //     'descripcion',
            //     'entidad',
            //     'created_at',
            // )
            //     ->where('code_company', Auth::user()->code_company)
            //     ->get();
            // * Tareas de CEJ Judicial
            $taskExpedientes = TaskExpediente::join('user_partes', 'task_expedientes.id_exp', '=', 'user_partes.id_exp')
                ->join('expedientes', 'task_expedientes.id_exp', '=', 'expedientes.id')
                ->join('clientes', 'expedientes.id_client', '=', 'clientes.id')
                ->where('clientes.id', $id)
                ->where('user_partes.code_company', Auth::user()->code_company)
                ->where('user_partes.code_user', Auth::user()->code_user)
                ->where('user_partes.entidad', 'judicial')
                ->where('task_expedientes.code_company', Auth::user()->code_company)
                ->select(
                    'task_expedientes.*',
                    'expedientes.n_expediente',
                    'clientes.name',
                    'clientes.last_name',
                    'clientes.name_company',
                    'clientes.type_contact',
                )
                ->get();

            $workFlowTaskExpedientes = WorkFlowTaskExpediente::join('user_partes', 'work_flow_task_expedientes.id_exp', '=', 'user_partes.id_exp')
                ->join('expedientes', 'work_flow_task_expedientes.id_exp', '=', 'expedientes.id')
                ->join('clientes', 'expedientes.id_client', '=', 'clientes.id')
                ->where('clientes.id', $id)
                ->where('user_partes.code_company', Auth::user()->code_company)
                ->where('user_partes.code_user', Auth::user()->code_user)
                ->where('user_partes.entidad', 'judicial')
                ->where('work_flow_task_expedientes.code_company', Auth::user()->code_company)
                ->select(
                    'work_flow_task_expedientes.*',
                    'expedientes.n_expediente',
                    'clientes.name',
                    'clientes.last_name',
                    'clientes.name_company',
                    'clientes.type_contact',
                )
                ->get();

            // * Tareas de CEJ Suprema

            $taskSuprema = TaskSuprema::join('user_partes', 'task_supremas.id_exp', '=', 'user_partes.id_exp')
                ->join('corte_supremas', 'task_supremas.id_exp', '=', 'corte_supremas.id')
                ->join('clientes', 'corte_supremas.id_client', '=', 'clientes.id')
                ->where('clientes.id', $id)
                ->where('user_partes.code_company', Auth::user()->code_company)
                ->where('user_partes.code_user', Auth::user()->code_user)
                ->where('user_partes.entidad', 'suprema')
                ->where('task_supremas.code_company', Auth::user()->code_company)
                ->select(
                    'task_supremas.*',
                    'corte_supremas.n_expediente',
                    'clientes.name',
                    'clientes.last_name',
                    'clientes.name_company',
                    'clientes.type_contact',
                )
                ->get();

            $workFlowTaskSuprema = WorkFlowTaskSuprema::join('user_partes', 'work_flow_task_supremas.id_exp', '=', 'user_partes.id_exp')
                ->join('corte_supremas', 'work_flow_task_supremas.id_exp', '=', 'corte_supremas.id')
                ->join('clientes', 'corte_supremas.id_client', '=', 'clientes.id')
                ->where('clientes.id', $id)
                ->where('user_partes.code_company', Auth::user()->code_company)
                ->where('user_partes.code_user', Auth::user()->code_user)
                ->where('user_partes.entidad', 'suprema')
                ->where('work_flow_task_supremas.code_company', Auth::user()->code_company)
                ->select(
                    'work_flow_task_supremas.*',
                    'corte_supremas.n_expediente',
                    'clientes.name',
                    'clientes.last_name',
                    'clientes.name_company',
                    'clientes.type_contact',
                )
                ->get();

            // * Tareas de Indecopi

            $taskIndecopi = TaskIndecopi::join('user_partes', 'task_indecopis.id_exp', '=', 'user_partes.id_exp')
                ->join('indecopis', 'task_indecopis.id_exp', '=', 'indecopis.id')
                ->join('clientes', 'indecopis.id_client', '=', 'clientes.id')
                ->where('clientes.id', $id)
                ->where('user_partes.code_company', Auth::user()->code_company)
                ->where('user_partes.code_user', Auth::user()->code_user)
                ->where('user_partes.entidad', 'indecopi')
                ->where('task_indecopis.code_company', Auth::user()->code_company)
                ->select(
                    'task_indecopis.*',
                    'indecopis.numero',
                    'clientes.name',
                    'clientes.last_name',
                    'clientes.name_company',
                    'clientes.type_contact',
                )
                ->get();


            $workFlowTaskIndecopi = WorkFlowTaskIndecopi::join('user_partes', 'work_flow_task_indecopis.id_exp', '=', 'user_partes.id_exp')
                ->join('indecopis', 'work_flow_task_indecopis.id_exp', '=', 'indecopis.id')
                ->join('clientes', 'indecopis.id_client', '=', 'clientes.id')
                ->where('clientes.id', $id)
                ->where('user_partes.code_company', Auth::user()->code_company)
                ->where('user_partes.code_user', Auth::user()->code_user)
                ->where('user_partes.entidad', 'indecopi')
                ->where('work_flow_task_indecopis.code_company', Auth::user()->code_company)
                ->select(
                    'work_flow_task_indecopis.*',
                    'indecopis.numero',
                    'clientes.name',
                    'clientes.last_name',
                    'clientes.name_company',
                    'clientes.type_contact',
                )
                ->get();

            // * Tareas de Sinoe

            $taskSinoe = TaskExpedienteSinoe::join('user_partes', 'task_expediente_sinoes.id_exp', '=', 'user_partes.id_exp')
                ->join('expediente_sinoes', 'task_expediente_sinoes.id_exp', '=', 'expediente_sinoes.id')
                ->join('clientes', 'expediente_sinoes.id_client', '=', 'clientes.id')
                ->where('clientes.id', $id)
                ->where('user_partes.code_company', Auth::user()->code_company)
                ->where('user_partes.code_user', Auth::user()->code_user)
                ->where('user_partes.entidad', 'sinoe')
                ->where('task_expediente_sinoes.code_company', Auth::user()->code_company)
                ->select(
                    'task_expediente_sinoes.*',
                    'expediente_sinoes.n_expediente',
                    'clientes.name',
                    'clientes.last_name',
                    'clientes.name_company',
                    'clientes.type_contact',
                )
                ->get();


            $workFlowTaskSinoe = WorkFlowTaskExpedienteSinoe::join('user_partes', 'work_flow_task_expediente_sinoes.id_exp', '=', 'user_partes.id_exp')
                ->join('expediente_sinoes', 'work_flow_task_expediente_sinoes.id_exp', '=', 'expediente_sinoes.id')
                ->join('clientes', 'expediente_sinoes.id_client', '=', 'clientes.id')
                ->where('clientes.id', $id)
                ->where('user_partes.code_company', Auth::user()->code_company)
                ->where('user_partes.code_user', Auth::user()->code_user)
                ->where('user_partes.entidad', 'sinoe')
                ->where('work_flow_task_expediente_sinoes.code_company', Auth::user()->code_company)
                ->select(
                    'work_flow_task_expediente_sinoes.*',
                    'expediente_sinoes.n_expediente',
                    'clientes.name',
                    'clientes.last_name',
                    'clientes.name_company',
                    'clientes.type_contact',
                )
                ->get();

            $eventsExpedientes = [];

            foreach ($taskExpedientes as $key => $value) {
                $eventsExpedientes[] = [
                    'title' => $value->nombre,
                    'start' => $value->fecha_limite,
                    'fecha' => $value->fecha_limite,
                    'prioridad' => $value->prioridad,
                    'entidad' => 'CEJ Judicial',
                    'estado' => $value->estado,
                    'description' => $value->descripcion,
                    'name' => $value->name,
                    'lastName' => $value->last_name,
                    'nameCompany' => $value->name_company,
                    'typeContact' => $value->type_contact,
                    'nExpediente' => $value->n_expediente,
                    'backgroundColor' => '#4c9ce2',
                    'textColor' => '#FFF',
                    'borderColor' => '#1c55b0',
                    'editable' => false,
                ];
            }
            foreach ($taskIndecopi as $key => $value) {
                $eventsExpedientes[] = [
                    'title' => $value->nombre,
                    'start' => $value->fecha_limite,
                    'fecha' => $value->fecha_limite,
                    'prioridad' => $value->prioridad,
                    'entidad' => 'Indecopi',
                    'estado' => $value->estado,
                    'description' => $value->descripcion,
                    'name' => $value->name,
                    'lastName' => $value->last_name,
                    'nameCompany' => $value->name_company,
                    'typeContact' => $value->type_contact,
                    'nExpediente' => $value->numero,
                    'backgroundColor' => '#4c9ce2',
                    'textColor' => '#FFF',
                    'borderColor' => '#1c55b0',
                    'editable' => false,
                ];
            }
            foreach ($taskSuprema as $key => $value) {
                $eventsExpedientes[] = [
                    'title' => $value->nombre,
                    'start' => $value->fecha_limite,
                    'fecha' => $value->fecha_limite,
                    'prioridad' => $value->prioridad,
                    'entidad' => 'CEJ Suprema',
                    'estado' => $value->estado,
                    'description' => $value->descripcion,
                    'name' => $value->name,
                    'lastName' => $value->last_name,
                    'nameCompany' => $value->name_company,
                    'typeContact' => $value->type_contact,
                    'nExpediente' => $value->n_expediente,
                    'backgroundColor' => '#4c9ce2',
                    'textColor' => '#FFF',
                    'borderColor' => '#1c55b0',
                    'editable' => false,
                ];
            }
            foreach ($taskSinoe as $key => $value) {
                $eventsExpedientes[] = [
                    'title' => $value->nombre,
                    'start' => $value->fecha_limite,
                    'fecha' => $value->fecha_limite,
                    'prioridad' => $value->prioridad,
                    'entidad' => 'Sinoe',
                    'estado' => $value->estado,
                    'description' => $value->descripcion,
                    'name' => $value->name,
                    'lastName' => $value->last_name,
                    'nameCompany' => $value->name_company,
                    'typeContact' => $value->type_contact,
                    'nExpediente' => $value->n_expediente,
                    'backgroundColor' => '#4c9ce2',
                    'textColor' => '#FFF',
                    'borderColor' => '#1c55b0',
                    'editable' => false,
                ];
            }
            return response()->json([
                "status" => true,
                "message" => "Eventos del cliente",
                "cliente" => $cliente,
                // 'eventSuggestion' => $eventSuggestion,
                'eventsExpedientes' => $eventsExpedientes,
                'workFlowTaskExpedientes' => $workFlowTaskExpedientes,
                'workFlowTaskSuprema' => $workFlowTaskSuprema,
                'workFlowTaskIndecopi' => $workFlowTaskIndecopi,
                'workFlowTaskSinoe' => $workFlowTaskSinoe,
            ], 200);
        }
        return response()->json([
            "status" => false,
            "message" => "Cliente no encontrado",
            "id" => $id,
        ], 404);
    }

    /**
     * Obtener datos del calendario por expediente
     *
     * @OA\Get(
     *     path="/api/calendar-proceso/{entidad}/{id}",
     *     tags={"Calendario"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="entidad",
     *         in="path",
     *         required=true,
     *         description="enitdad",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del proceso",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos del calendario",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Eventos del cliente"),
     *             @OA\Property(property="cliente", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="type_contact", type="string", example="Empresa"),
     *                  @OA\Property(property="name", type="string", example=null),
     *                  @OA\Property(property="last_name", type="string", example=null),
     *                  @OA\Property(property="dni", type="string", example=null),
     *                  @OA\Property(property="birthdate", type="date", format="date", example=null),
     *                  @OA\Property(property="company", type="string", example=null),
     *                  @OA\Property(property="name_company", type="string", example="demo"),
     *                  @OA\Property(property="ruc", type="string", example="1234568790875746"),
     *                  @OA\Property(property="email", type="string", example="{'email':'alnuawd@sagssdg.adgs','type_email':'Trabajo','email2':null,'type_email2':'Trabajo'}"),
     *                  @OA\Property(property="phone", type="string", example="{'phone':'2354325','type_phone':'Trabajo','phone2':null,'type_phone2':'Trabajo'}"),
     *                  @OA\Property(property="address", type="string", example="{'country':'Perú','departamento':'20','provincia':'152','distrito':'1534','street':'qwetryuio'}"),
     *                  @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                  @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                  @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-25T21:49:39.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-25T21:49:40.000000Z")
     *             ),
     *             @OA\Property(property="eventSuggestion", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="fecha", type="string", format="date", example="2023-12-20"),
     *                     @OA\Property(property="titulo", type="string", example="Fecha demo"),
     *                     @OA\Property(property="descripcion", type="string", example="Se registró esta fecha como demo"),
     *                     @OA\Property(property="entidad", type="string", example="judicial"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-21T03:17:41.000000Z")
     *                 )
     *             ),
     *             @OA\Property(property="eventsExpedientes", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="title", type="string", example="Tarea 1"),
     *                     @OA\Property(property="start", type="string", format="date", example="2023-12-23"),
     *                     @OA\Property(property="fecha", type="string", format="date", example="2023-12-23"),
     *                     @OA\Property(property="prioridad", type="string", example="Media"),
     *                     @OA\Property(property="entidad", type="string", example="CEJ Judicial"),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="description", type="string", example="Demo de tarea en expediente"),
     *                     @OA\Property(property="name", type="string", nullable=true),
     *                     @OA\Property(property="lastName", type="string", nullable=true),
     *                     @OA\Property(property="nameCompany", type="string", example="demo"),
     *                     @OA\Property(property="typeContact", type="string", example="Empresa"),
     *                     @OA\Property(property="nExpediente", type="string", example="01667-2022-0-1001-JR-CI-06"),
     *                     @OA\Property(property="backgroundColor", type="string", example="#4c9ce2"),
     *                     @OA\Property(property="textColor", type="string", example="#FFF"),
     *                     @OA\Property(property="borderColor", type="string", example="#1c55b0"),
     *                     @OA\Property(property="editable", type="boolean", example=false)
     *                 )
     *             ),
     *             @OA\Property(property="workFlowTaskExpedientes", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=1),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="tareaa"),
     *                     @OA\Property(property="descripcion", type="string", example="Responsabilidad de todos"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=6),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=4),
     *                     @OA\Property(property="fecha_limite", type="string", format="date", example="2023-12-27"),
     *                     @OA\Property(property="fecha_alerta", type="string", format="date", example="2023-12-25"),
     *                     @OA\Property(property="fecha_finalizada", type="string", nullable=true, format="date-time", example=null),
     *                     @OA\Property(property="attached_files", type="string", nullable=true),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="prioridad", type="string", example="Baja"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="string", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-21T15:50:06.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-12-21T15:50:06.000000Z"),
     *                     @OA\Property(property="n_expediente", type="string", example="01667-2022-0-1001-JR-CI-06"),
     *                     @OA\Property(property="name", type="string", nullable=true),
     *                     @OA\Property(property="last_name", type="string", nullable=true),
     *                     @OA\Property(property="name_company", type="string", example="demo"),
     *                     @OA\Property(property="type_contact", type="string", example="Empresa")
     *                 )
     *             ),
     *             @OA\Property(property="workFlowTaskSuprema", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=1),
     *                     @OA\Property(property="id_exp", type="integer", example=9),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="tareaa"),
     *                     @OA\Property(property="descripcion", type="string", example="Responsabilidad de todos"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=6),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=4),
     *                     @OA\Property(property="fecha_limite", type="string", format="date", example="2024-01-26"),
     *                     @OA\Property(property="fecha_alerta", type="string", format="date", example="2024-01-24"),
     *                     @OA\Property(property="fecha_finalizada", type="string", nullable=true, format="date-time", example=null),
     *                     @OA\Property(property="attached_files", type="string", nullable=true),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="prioridad", type="string", example="Baja"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="string", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-21T00:58:49.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-21T00:58:49.000000Z"),
     *                     @OA\Property(property="n_expediente", type="string", example="02398-2010-0-5001-SU-PE-01"),
     *                     @OA\Property(property="name", type="string", nullable=true),
     *                     @OA\Property(property="last_name", type="string", nullable=true),
     *                     @OA\Property(property="name_company", type="string", example="demo"),
     *                     @OA\Property(property="type_contact", type="string", example="Empresa")
     *                 )
     *             ),
     *             @OA\Property(property="workFlowTaskIndecopi", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=1),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="tareaa"),
     *                     @OA\Property(property="descripcion", type="string", example="Responsabilidad de todos"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=6),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=4),
     *                     @OA\Property(property="fecha_limite", type="string", format="date", example="2024-01-26"),
     *                     @OA\Property(property="fecha_alerta", type="string", format="date", example="2024-01-24"),
     *                     @OA\Property(property="fecha_finalizada", type="string", nullable=true, format="date-time", example=null),
     *                     @OA\Property(property="attached_files", type="string", nullable=true),
     *                     @OA\Property(property="estado", type="string", example="En progreso"),
     *                     @OA\Property(property="prioridad", type="string", example="Baja"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="string", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-21T00:58:49.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-21T00:58:49.000000Z"),
     *                     @OA\Property(property="n_expediente", type="string", example="02398-2010-0-5001-SU-PE-01"),
     *                     @OA\Property(property="name", type="string", nullable=true),
     *                     @OA\Property(property="last_name", type="string", nullable=true),
     *                     @OA\Property(property="name_company", type="string", example="demo"),
     *                     @OA\Property(property="type_contact", type="string", example="Empresa")
     *                 )
     *             ),
     *             @OA\Property(property="workFlowTaskSinoe", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="id_workflow", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_stage", type="integer", example=1),
     *                     @OA\Property(property="id_workflow_task", type="integer", example=1),
     *                     @OA\Property(property="id_exp", type="integer", example=1),
     *                     @OA\Property(property="nombre_etapa", type="string", example="responsabilidad"),
     *                     @OA\Property(property="nombre_flujo", type="string", example="Demanda de divorcio"),
     *                     @OA\Property(property="nombre", type="string", example="tareaa"),
     *                     @OA\Property(property="descripcion", type="string", example="Responsabilidad de todos"),
     *                     @OA\Property(property="dias_duracion", type="integer", example=6),
     *                     @OA\Property(property="dias_antes_venc", type="integer", example=4),
     *                     @OA\Property(property="fecha_limite", type="string", format="date", example="2024-01-26"),
     *                     @OA\Property(property="fecha_alerta", type="string", format="date", example="2024-01-24"),
     *                     @OA\Property(property="fecha_finalizada", type="string", nullable=true, format="date-time", example=null),
     *                     @OA\Property(property="attached_files", type="string", nullable=true),
     *                     @OA\Property(property="estado", type="string", example="Aprobada"),
     *                     @OA\Property(property="prioridad", type="string", example="Baja"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="metadata", type="string", example="finalizado"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-21T00:58:49.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-21T00:58:49.000000Z"),
     *                     @OA\Property(property="n_expediente", type="string", example="02398-2010-0-5001-SU-PE-01"),
     *                     @OA\Property(property="name", type="string", nullable=true),
     *                     @OA\Property(property="last_name", type="string", nullable=true),
     *                     @OA\Property(property="name_company", type="string", example="demo"),
     *                     @OA\Property(property="type_contact", type="string", example="Empresa")
     *                 )
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontró el evento del cliente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cliente no encontrado"),
     *             @OA\Property(property="id", type="string", example="2")
     *         )
     *     )
     * )
     */

    public function getCalendarProceso($entidad, $id)
    {
        // $cliente = Cliente::where('id', $id)
        //     ->where('code_company', Auth::user()->code_company)
        //     ->first();



        if ($entidad) {

            $eventSuggestion = [];
            $taskExpedientes = [];
            $workFlowTaskExpedientes = [];
            $taskSuprema = [];
            $workFlowTaskSuprema = [];
            $taskIndecopi = [];
            $workFlowTaskIndecopi = [];
            $taskSinoe = [];
            $workFlowTaskSinoe = [];

            // Eventos de Sugerencia de la IA
            if ($entidad == "judicial" || $entidad ==  "sinoe") {
                $eventSuggestion = EventSuggestion::select(
                    'fecha',
                    'titulo',
                    'descripcion',
                    'entidad',
                    'created_at',
                )
                    ->where('code_company', Auth::user()->code_company)
                    ->where('entidad', $entidad)
                    ->get();
            }

            if ($entidad == "judicial") {

                // * Tareas de CEJ Judicial
                $taskExpedientes = TaskExpediente::join('expedientes', 'task_expedientes.id_exp', '=', 'expedientes.id')
                    ->where('expedientes.id', $id)
                    ->where('task_expedientes.code_company', Auth::user()->code_company)
                    ->select(
                        'task_expedientes.*',
                        'expedientes.n_expediente',
                    )
                    ->get();

                $workFlowTaskExpedientes = WorkFlowTaskExpediente::join('expedientes', 'work_flow_task_expedientes.id_exp', '=', 'expedientes.id')
                    ->where('expedientes.id', $id)
                    ->where('work_flow_task_expedientes.code_company', Auth::user()->code_company)
                    ->select(
                        'work_flow_task_expedientes.*',
                        'expedientes.n_expediente',
                    )
                    ->get();
            }

            if ($entidad == "suprema") {

                // * Tareas de CEJ Suprema

                $taskSuprema = TaskSuprema::join('corte_supremas', 'task_supremas.id_exp', '=', 'corte_supremas.id')
                    ->where('corte_supremas.id', $id)
                    ->where('task_supremas.code_company', Auth::user()->code_company)
                    ->select(
                        'task_supremas.*',
                        'corte_supremas.n_expediente',
                    )
                    ->get();

                $workFlowTaskSuprema = WorkFlowTaskSuprema::join('corte_supremas', 'work_flow_task_supremas.id_exp', '=', 'corte_supremas.id')
                    ->where('corte_supremas.id', $id)
                    ->where('work_flow_task_supremas.code_company', Auth::user()->code_company)
                    ->select(
                        'work_flow_task_supremas.*',
                        'corte_supremas.n_expediente',
                    )
                    ->get();
            }

            if ($entidad == "indecopi") {

                // * Tareas de Indecopi

                $taskIndecopi = TaskIndecopi::join('indecopis', 'task_indecopis.id_exp', '=', 'indecopis.id')
                    ->where('indecopis.id', $id)
                    ->where('task_indecopis.code_company', Auth::user()->code_company)
                    ->select(
                        'task_indecopis.*',
                        'indecopis.numero',
                    )
                    ->get();


                $workFlowTaskIndecopi = WorkFlowTaskIndecopi::join('indecopis', 'work_flow_task_indecopis.id_exp', '=', 'indecopis.id')
                    ->where('indecopis.id', $id)
                    ->where('work_flow_task_indecopis.code_company', Auth::user()->code_company)
                    ->select(
                        'work_flow_task_indecopis.*',
                        'indecopis.numero',
                    )
                    ->get();
            }

            if ($entidad == "sinoe") {

                // * Tareas de Sinoe

                $taskSinoe = TaskExpedienteSinoe::join('expediente_sinoes', 'task_expediente_sinoes.id_exp', '=', 'expediente_sinoes.id')
                    ->where('expediente_sinoes.id', $id)
                    ->where('task_expediente_sinoes.code_company', Auth::user()->code_company)
                    ->select(
                        'task_expediente_sinoes.*',
                        'expediente_sinoes.n_expediente',
                    )
                    ->get();


                $workFlowTaskSinoe = WorkFlowTaskExpedienteSinoe::join('expediente_sinoes', 'work_flow_task_expediente_sinoes.id_exp', '=', 'expediente_sinoes.id')
                    ->where('expediente_sinoes.id', $id)
                    ->where('work_flow_task_expediente_sinoes.code_company', Auth::user()->code_company)
                    ->select(
                        'work_flow_task_expediente_sinoes.*',
                        'expediente_sinoes.n_expediente',
                    )
                    ->get();
            }

            $eventsExpedientes = [];

            foreach ($taskExpedientes as $key => $value) {
                $eventsExpedientes[] = [
                    'title' => $value->nombre,
                    'start' => $value->fecha_limite,
                    'fecha' => $value->fecha_limite,
                    'prioridad' => $value->prioridad,
                    'entidad' => 'CEJ Judicial',
                    'estado' => $value->estado,
                    'description' => $value->descripcion,
                    'name' => $value->name,
                    'lastName' => $value->last_name,
                    'nameCompany' => $value->name_company,
                    'typeContact' => $value->type_contact,
                    'nExpediente' => $value->n_expediente,
                    'backgroundColor' => '#4c9ce2',
                    'textColor' => '#FFF',
                    'borderColor' => '#1c55b0',
                    'editable' => false,
                ];
            }
            foreach ($taskIndecopi as $key => $value) {
                $eventsExpedientes[] = [
                    'title' => $value->nombre,
                    'start' => $value->fecha_limite,
                    'fecha' => $value->fecha_limite,
                    'prioridad' => $value->prioridad,
                    'entidad' => 'Indecopi',
                    'estado' => $value->estado,
                    'description' => $value->descripcion,
                    'name' => $value->name,
                    'lastName' => $value->last_name,
                    'nameCompany' => $value->name_company,
                    'typeContact' => $value->type_contact,
                    'nExpediente' => $value->numero,
                    'backgroundColor' => '#4c9ce2',
                    'textColor' => '#FFF',
                    'borderColor' => '#1c55b0',
                    'editable' => false,
                ];
            }
            foreach ($taskSuprema as $key => $value) {
                $eventsExpedientes[] = [
                    'title' => $value->nombre,
                    'start' => $value->fecha_limite,
                    'fecha' => $value->fecha_limite,
                    'prioridad' => $value->prioridad,
                    'entidad' => 'CEJ Suprema',
                    'estado' => $value->estado,
                    'description' => $value->descripcion,
                    'name' => $value->name,
                    'lastName' => $value->last_name,
                    'nameCompany' => $value->name_company,
                    'typeContact' => $value->type_contact,
                    'nExpediente' => $value->n_expediente,
                    'backgroundColor' => '#4c9ce2',
                    'textColor' => '#FFF',
                    'borderColor' => '#1c55b0',
                    'editable' => false,
                ];
            }
            foreach ($taskSinoe as $key => $value) {
                $eventsExpedientes[] = [
                    'title' => $value->nombre,
                    'start' => $value->fecha_limite,
                    'fecha' => $value->fecha_limite,
                    'prioridad' => $value->prioridad,
                    'entidad' => 'Sinoe',
                    'estado' => $value->estado,
                    'description' => $value->descripcion,
                    'name' => $value->name,
                    'lastName' => $value->last_name,
                    'nameCompany' => $value->name_company,
                    'typeContact' => $value->type_contact,
                    'nExpediente' => $value->n_expediente,
                    'backgroundColor' => '#4c9ce2',
                    'textColor' => '#FFF',
                    'borderColor' => '#1c55b0',
                    'editable' => false,
                ];
            }
            return response()->json([
                "status" => true,
                "entidad" => $entidad,
                "message" => "Eventos del proceso",
                // "cliente" => $cliente,
                'eventSuggestion' => $eventSuggestion,
                'eventsExpedientes' => $eventsExpedientes,
                'workFlowTaskExpedientes' => $workFlowTaskExpedientes,
                'workFlowTaskSuprema' => $workFlowTaskSuprema,
                'workFlowTaskIndecopi' => $workFlowTaskIndecopi,
                'workFlowTaskSinoe' => $workFlowTaskSinoe,
            ], 200);
        }
        return response()->json([
            "status" => false,
            "message" => "Eventos no encontrado",
            "id" => $id,
            "entidad" => $entidad,
        ], 404);
    }
}
