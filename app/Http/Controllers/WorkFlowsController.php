<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AccountGoogleCalendar;
use App\Models\CommentTaskFlujoIndecopi;
use App\Models\CommentTaskFlujoJudicial;
use App\Models\CommentTaskFlujoSinoe;
use App\Models\CommentTaskFlujoSuprema;
use App\Models\Company;
use App\Models\FlujoAsociadoExpediente;
use App\Models\FlujoAsociadoExpedienteSinoe;
use App\Models\FlujoAsociadoIndecopi;
use App\Models\FlujoAsociadoSuprema;
use App\Models\Suscripcion;
use App\Models\TaskExpediente;
use App\Models\User;
use App\Models\UserParte;
use App\Models\WorkFlows;
use App\Models\WorkFlowsStage;
use App\Models\WorkFlowsTask;
use App\Models\WorkFlowTaskExpediente;
use App\Models\WorkFlowTaskExpedienteSinoe;
use App\Models\WorkFlowTaskIndecopi;
use App\Models\WorkFlowTaskSuprema;
use App\Models\WorkFlowTransitions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Ui\Presets\React;
use League\CommonMark\Extension\CommonMark\Node\Block\ListData;
use Google\Client as Google_Client;
use Google\Service\Calendar as Google_Service_Calendar;
use Google\Service\Calendar\Event as Google_Service_Calendar_Event;
use Google\Service\Calendar\EventCreator;
use Google\Service\Calendar\EventDateTime;

class WorkFlowsController extends Controller
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

    //
    public function index()
    {
        // $workFlowsAll = WorkFlows::where('code_company', Auth::user()->code_company)->get();
        $workFlowsAll = DB::table('work_flows')
            ->select(
                'work_flows.id',
                'work_flows.nombre',
                'work_flows.uid',
                DB::raw('COALESCE((SELECT COUNT(*) FROM work_flows_stages WHERE work_flows.id = work_flows_stages.id_workflow), 0) as etapas'),
                DB::raw('(SELECT COUNT(*) FROM flujo_asociado_expedientes WHERE table_pertenece = "flujo" AND id_workflow = work_flows.id AND code_company = "' . Auth::user()->code_company . '") as judicial'),
                DB::raw('(SELECT COUNT(*) FROM flujo_asociado_supremas WHERE table_pertenece = "flujo" AND id_workflow = work_flows.id AND code_company = "' . Auth::user()->code_company . '") as suprema'),
                DB::raw('(SELECT COUNT(*) FROM flujo_asociado_indecopis WHERE table_pertenece = "flujo" AND id_workflow = work_flows.id AND code_company = "' . Auth::user()->code_company . '") as indecopi'),
                DB::raw('(SELECT COUNT(*) FROM flujo_asociado_expediente_sinoes WHERE table_pertenece = "flujo" AND id_workflow = work_flows.id AND code_company = "' . Auth::user()->code_company . '") as sinoe')
            )
            ->where('code_company', Auth::user()->code_company)
            ->get();

        $dataCompany = Company::where('code_company', Auth::user()->code_company)->first();
        $dataSuscripcion = Suscripcion::where('id', $dataCompany->id_suscripcion)->first();
        $totalWorkflows = WorkFlows::where('code_company', Auth::user()->code_company)->count();
        $limitWorkflows = $dataSuscripcion->limit_workflows;

        return view('dashboard.sistema_expedientes.workflows.index', compact('workFlowsAll', "totalWorkflows", "limitWorkflows"));
    }

    /* **************************
    *
    *   WORK FLOWS CRUD
    *
    * ************************* */

    public function addWorkFlows(Request $request)
    {
        // dd($request);
        $name = request()->input('name-work-flows');
        if ($name && $name !== '') {
            $exist = WorkFlows::where('nombre', $name)->first();
            if ($exist) {
                return redirect()->back()->with('info', '¡Ya existe!');
            }
            $newData = [
                'uid' => DB::raw('UUID_SHORT()'),
                'nombre' => $name,
                'code_user' => Auth::user()->code_user,
                'code_company' => Auth::user()->code_company,
                'metadata' => null,
            ];

            $dataCompany = Company::where('code_company', Auth::user()->code_company)->first();
            $dataSuscripcion = Suscripcion::where('id', $dataCompany->id_suscripcion)->first();
            $totalWorkflows = WorkFlows::where('code_company', Auth::user()->code_company)->count();
            $limitWorkflows = $dataSuscripcion->limit_workflows;

            // Verificar si ya alcanso el maximo
            if ($totalWorkflows >= $limitWorkflows && $limitWorkflows !== null) {
                return redirect()->route('usuarios.index')->with('error', "Error al crear Flujo de Trabajo (Maximo alcanzado).");
            }

            $id = WorkFlows::insertGetId($newData);

            $workflow = WorkFlows::where('id', $id)->first();

            return redirect()->route('workflows.uid', ['uid' => $workflow->uid, 'idStage' => 1]);
        }
        return redirect()->back()->with('error', '¡No se pudo registrar!');
    }

    public function updateWorkFlows()
    {
        $name = request()->input('name-work-flows-edit');
        $id = request()->input('id-work-flows-edit');
        if ($id && $id !== '') {
            $newData = [
                'nombre' => $name,
            ];

            $exist = WorkFlows::where('nombre', $name)->where('code_company', Auth::user()->code_company)->first();
            if ($exist) {
                return redirect()->back()->with('info', '¡Ya existe!');
            }

            WorkFlows::where('id', $id)->update($newData);

            WorkFlowTaskExpediente::where('id_workflow', $id)
                ->update([
                    'nombre_flujo' => $name
                ]);
            WorkFlowTaskIndecopi::where('id_workflow', $id)
                ->update([
                    'nombre_flujo' => $name
                ]);
            WorkFlowTaskSuprema::where('id_workflow', $id)
                ->update([
                    'nombre_flujo' => $name
                ]);

            WorkFlowTaskExpedienteSinoe::where('id_workflow', $id)
                ->update([
                    'nombre_flujo' => $name
                ]);

            return redirect()->back()->with('success', '¡Nombre de flujo de trabajo actualizado!');
        }
        return redirect()->back()->with('error', '¡No se pudo actualizar!');
    }

    public function deleteWorkFlows()
    {
        $id = request()->input('id');
        if ($id && $id !== '') {
            WorkFlows::where('id', $id)->delete();
            WorkFlowsStage::where('id_workflow', $id)->delete();
            WorkFlowsTask::where('id_workflow', $id)->delete();
            // return redirect()->back()->with('success','¡Eliminado con éxito!');
            return response()->json("Eliminado");
        }
        return response()->json('error');
    }

    public function selectWorkFlows($uid, $idStage)
    {
        // dd($uid);
        $workflow = WorkFlows::where('uid', $uid)->first();
        $workflowStage = WorkFlowsStage::select(
            'id',
            'uid',
            'nombre',
            'id_workflow',
            'code_user',
            'code_company',
            'metadata',
            DB::raw('COALESCE((SELECT COUNT(*) FROM work_flows_tasks WHERE work_flows_stages.id = work_flows_tasks.id_workflow_stage), 0) as tareas')
        )
            ->where('id_workflow', $workflow->id)->get();

        if ($workflow) {
            // return view('dashboard.sistema_expedientes.workflows.workFlow', ['workflow' => $workflow , 'workflowStage' => $workflowStage]);
            if ($idStage && $idStage !== '') {
                $workflowTask = WorkFlowsTask::where('id_workflow', $workflow->id)->get();
                $workflowTransitions = WorkFlowTransitions::where('id_workflow', $workflow->id)->get();
                return view('dashboard.sistema_expedientes.workflows.workFlow', ['workflow' => $workflow, 'workflowStage' => $workflowStage, 'workflowTask' => $workflowTask, 'keyStage' => $idStage, 'workflowTransitions' => $workflowTransitions]);
            }
        } else {
            return redirect()->route('sistema_expedientes.workflows.index')->with('error', 'Flujo de trabajo no encontrado.');
        }
    }

    /* **************************
    *
    *   WORK FLOWS STAGE CRUD
    *
    * ************************* */

    public function addWorkFlowsStage(Request $request)
    {
        // dd($request);
        $name = request()->input('name-work-flows-stage');
        $id = request()->input('id-work-flows');
        if ($name && $name !== '') {
            $exist = WorkFlowsStage::where('nombre', $name)->where('id_workflow', $id)->where('code_company', Auth::user()->code_company)->first();
            if ($exist) {
                return redirect()->back()->with('info', '¡Ya existe!');
            }
            $newData = [
                'uid' => DB::raw('UUID_SHORT()'),
                'nombre' => $name,
                'id_workflow' => $id,
                'code_user' => Auth::user()->code_user,
                'code_company' => Auth::user()->code_company,
                'metadata' => null,
            ];

            // $ultStage = WorkFlowsStage::where("code_company", Auth::user()->code_company)
            // ->where("id_workflow", $id)
            // ->orderBy("id", "desc")
            // ->first();

            // if ($ultStage){
            //     // Verificar en el flujo del expediente si se ecnuenta una etapa
            //     $existStageFlujoExp = FlujoAsociadoExpediente::select(
            //         'id',
            //         'id_exp',
            //         'id_workflow',
            //         'id_workflow_stage'
            //     )
            //     ->where("code_company", Auth::user()->code_company)
            //     ->where("id_workflow_stage", $ultStage->id)
            //     ->where("id_workflow", $id)
            //     ->where("table_pertenece", "normal")
            //     ->groupBy("id", "id_exp", "id_workflow", "id_workflow_stage")
            //     ->get();

            //     // dd($existStageFlujoExp);
            //     $dataTimeNow = Carbon::now();

            //     if ($existStageFlujoExp){
            //         foreach ($existStageFlujoExp as $key => $valueSF) {
            //             FlujoAsociadoExpediente::where("id", $valueSF->id)
            //             ->update([
            //                 'estado' => 'activo',
            //                 'id_exp' => $valueSF->id_exp,
            //                 'id_workflow' => $id,
            //                 'id_workflow_stage' => $valueSF->id_workflow_stage,
            //                 'date_time' => $dataTimeNow,
            //                 'code_user' => Auth::user()->code_user,
            //                 'code_company' => Auth::user()->code_company,
            //                 'metadata' => null,
            //                 'etapa' => $name ?? null,
            //                 'condicion' => null,
            //                 'estado_transition' => 'pendiente',
            //                 'table_pertenece' => 'normal',
            //             ]);
            //         }
            //     }

            //     // INDECOPI
            //     $existStageFlujoExp = FlujoAsociadoIndecopi::select(
            //         'id',
            //         'id_exp',
            //         'id_workflow',
            //         'id_workflow_stage'
            //     )
            //     ->where("code_company", Auth::user()->code_company)
            //     ->where("id_workflow_stage", $ultStage->id)
            //     ->where("id_workflow", $id)
            //     ->where("table_pertenece", "normal")
            //     ->groupBy("id", "id_exp", "id_workflow", "id_workflow_stage")
            //     ->get();

            //     // dd($existStageFlujoExp);
            //     $dataTimeNow = Carbon::now();

            //     if ($existStageFlujoExp){
            //         foreach ($existStageFlujoExp as $key => $valueSF) {
            //             FlujoAsociadoIndecopi::where("id", $valueSF->id)
            //             ->update([
            //                 'estado' => 'activo',
            //                 'id_exp' => $valueSF->id_exp,
            //                 'id_workflow' => $id,
            //                 'id_workflow_stage' => $valueSF->id_workflow_stage,
            //                 'date_time' => $dataTimeNow,
            //                 'code_user' => Auth::user()->code_user,
            //                 'code_company' => Auth::user()->code_company,
            //                 'metadata' => null,
            //                 'etapa' => $name ?? null,
            //                 'condicion' => null,
            //                 'estado_transition' => 'pendiente',
            //                 'table_pertenece' => 'normal',
            //             ]);
            //         }
            //     }

            //     // Suprema
            //     $existStageFlujoExp = FlujoAsociadoSuprema::select(
            //         'id',
            //         'id_exp',
            //         'id_workflow',
            //         'id_workflow_stage'
            //     )
            //     ->where("code_company", Auth::user()->code_company)
            //     ->where("id_workflow_stage", $ultStage->id)
            //     ->where("id_workflow", $id)
            //     ->where("table_pertenece", "normal")
            //     ->groupBy("id", "id_exp", "id_workflow", "id_workflow_stage")
            //     ->get();

            //     // dd($existStageFlujoExp);
            //     $dataTimeNow = Carbon::now();

            //     if ($existStageFlujoExp){
            //         foreach ($existStageFlujoExp as $key => $valueSF) {
            //             FlujoAsociadoSuprema::where("id", $valueSF->id)
            //             ->update([
            //                 'estado' => 'activo',
            //                 'id_exp' => $valueSF->id_exp,
            //                 'id_workflow' => $id,
            //                 'id_workflow_stage' => $valueSF->id_workflow_stage,
            //                 'date_time' => $dataTimeNow,
            //                 'code_user' => Auth::user()->code_user,
            //                 'code_company' => Auth::user()->code_company,
            //                 'metadata' => null,
            //                 'etapa' => $name ?? null,
            //                 'condicion' => null,
            //                 'estado_transition' => 'pendiente',
            //                 'table_pertenece' => 'normal',
            //             ]);
            //         }
            //     }

            //     // SINOE
            //     $existStageFlujoExp = FlujoAsociadoExpedienteSinoe::select(
            //         'id',
            //         'id_exp',
            //         'id_workflow',
            //         'id_workflow_stage'
            //     )
            //     ->where("code_company", Auth::user()->code_company)
            //     ->where("id_workflow_stage", $ultStage->id)
            //     ->where("id_workflow", $id)
            //     ->where("table_pertenece", "normal")
            //     ->groupBy("id", "id_exp", "id_workflow", "id_workflow_stage")
            //     ->get();

            //     // dd($existStageFlujoExp);
            //     $dataTimeNow = Carbon::now();

            //     if ($existStageFlujoExp){
            //         foreach ($existStageFlujoExp as $key => $valueSF) {
            //             FlujoAsociadoExpedienteSinoe::where("id", $valueSF->id)
            //             ->update([
            //                 'estado' => 'activo',
            //                 'id_exp' => $valueSF->id_exp,
            //                 'id_workflow' => $id,
            //                 'id_workflow_stage' => $valueSF->id_workflow_stage,
            //                 'date_time' => $dataTimeNow,
            //                 'code_user' => Auth::user()->code_user,
            //                 'code_company' => Auth::user()->code_company,
            //                 'metadata' => null,
            //                 'etapa' => $name ?? null,
            //                 'condicion' => null,
            //                 'estado_transition' => 'pendiente',
            //                 'table_pertenece' => 'normal',
            //             ]);
            //         }
            //     }
            // }

            $idWFS = WorkFlowsStage::insertGetId($newData);
            $workflow = WorkFlows::where('id', $id)->first();
            $idStage = WorkFlowsStage::where('id_workflow', $id)->count();
            // $workflowStage = WorkFlowsStage::where('id', $idWFS)->get();
            // $workflowTask = WorkFlowsTask::where('id_workflow', $id)->where('id_workflow_stage', $idStage)->get();
            return redirect()->route('workflows.uid', ['uid' => $workflow->uid, 'idStage' => $idStage]);
            // return view('dashboard.sistema_expedientes.workflows.workFlow', ['workflow' => $workflow , 'workflowStage' => $workflowStage, 'workflowTask' => $workflowTask, 'keyStage' => $idStage]);
            // return redirect()->back()->with('success','¡Etapa registrada!');
        }
        return redirect()->back()->with('error', '¡No se pudo registrar!');
    }

    public function updateWorkFlowsStage()
    {
        $name = request()->input('name-work-flows-stage-edit');
        $id = request()->input('id-work-flows-stage-edit');
        if ($id && $id !== '') {
            $oldDataStage = WorkFlowsStage::where('id', $id)->first();
            $newData = [
                'nombre' => $name,
            ];

            $data = WorkFlowsStage::where('id', $id)->first();
            $exist = WorkFlowsStage::where('nombre', $name)->where('code_company', Auth::user()->code_company)->where('id_workflow', $data->id)->first();
            if ($exist) {
                return redirect()->back()->with('info', '¡Ya existe!');
            }

            WorkFlowsStage::where('id', $id)->update($newData);

            $dataWorkFlowStage = WorkFlowsStage::where('id', $id)->first();
            // actualizar en work_flow_task_indecopis and expedientes
            // actualizar en flujo_asociado_indecopis and expedientes

            WorkFlowTaskExpediente::where('id_workflow', $dataWorkFlowStage->id_workflow)
                ->where('id_workflow_stage', $dataWorkFlowStage->id)
                ->update([
                    'nombre_etapa' => $name
                ]);
            WorkFlowTaskIndecopi::where('id_workflow', $dataWorkFlowStage->id_workflow)
                ->where('id_workflow_stage', $dataWorkFlowStage->id)
                ->update([
                    'nombre_etapa' => $name
                ]);
            WorkFlowTaskSuprema::where('id_workflow', $dataWorkFlowStage->id_workflow)
                ->where('id_workflow_stage', $dataWorkFlowStage->id)
                ->update([
                    'nombre_etapa' => $name
                ]);
            WorkFlowTaskExpedienteSinoe::where('id_workflow', $dataWorkFlowStage->id_workflow)
                ->where('id_workflow_stage', $dataWorkFlowStage->id)
                ->update([
                    'nombre_etapa' => $name
                ]);
            WorkFlowTransitions::where('id_workflow', $dataWorkFlowStage->id_workflow)
                ->where('etapa', $oldDataStage->nombre)
                ->where('id_workflow_stage', $dataWorkFlowStage->id)
                ->update([
                    'etapa' => $name
                ]);
            // FlujoAsociadoExpediente::where('id_workflow', $dataWorkFlowStage->id_workflow)
            //                     ->where('etapa', $oldDataStage->nombre)
            //                     // ->where('id_workflow_stage', $dataWorkFlowStage->id)
            //                     // ->where('table_pertenece', 'transition')
            //                     ->where('code_company', Auth::user()->code_company)
            //                     ->update([
            //                         'etapa' =>$name
            //                     ]);
            // FlujoAsociadoIndecopi::where('id_workflow', $dataWorkFlowStage->id_workflow)
            //                     ->where('etapa', $oldDataStage->nombre)
            //                     // ->where('id_workflow_stage', $dataWorkFlowStage->id)
            //                     // ->where('table_pertenece', 'transition')
            //                     ->where('code_company', Auth::user()->code_company)
            //                     ->update([
            //                         'etapa' =>$name
            //                     ]);
            // FlujoAsociadoSuprema::where('id_workflow', $dataWorkFlowStage->id_workflow)
            //                     ->where('etapa', $oldDataStage->nombre)
            //                     // ->where('id_workflow_stage', $dataWorkFlowStage->id)
            //                     // ->where('table_pertenece', 'transition')
            //                     ->where('code_company', Auth::user()->code_company)
            //                     ->update([
            //                         'etapa' =>$name
            //                     ]);
            // FlujoAsociadoExpedienteSinoe::where('id_workflow', $dataWorkFlowStage->id_workflow)
            //                     ->where('etapa', $oldDataStage->nombre)
            //                     // ->where('id_workflow_stage', $dataWorkFlowStage->id)
            //                     // ->where('table_pertenece', 'transition')
            //                     ->where('code_company', Auth::user()->code_company)
            //                     ->update([
            //                         'etapa' =>$name
            //                     ]);

            return redirect()->back()->with('success', '¡Se actualizó correctamente!');
        }
        return redirect()->back()->with('error', '¡No se pudo actualizar!');
    }

    public function deleteWorkFlowsStage()
    {
        $id = request()->input('id');
        if ($id && $id !== '') {
            $dataWorkFlowStage = WorkFlowsStage::where('id', $id)
                ->where("code_company", Auth::user()->code_company)
                ->first();


            WorkFlowTaskExpediente::where('id_workflow', $dataWorkFlowStage->id_workflow)
                ->where('id_workflow_stage', $dataWorkFlowStage->id)
                ->where("code_company", Auth::user()->code_company)
                ->delete();
            CommentTaskFlujoJudicial::where('id_flujo', $dataWorkFlowStage->id_workflow)
                ->where('id_stage', $dataWorkFlowStage->id)
                ->where('code_company', Auth::user()->code_company)
                ->delete();

            WorkFlowTaskIndecopi::where('id_workflow', $dataWorkFlowStage->id_workflow)
                ->where('id_workflow_stage', $dataWorkFlowStage->id)
                ->where("code_company", Auth::user()->code_company)
                ->delete();
            CommentTaskFlujoIndecopi::where('id_flujo', $dataWorkFlowStage->id_workflow)
                ->where('id_stage', $dataWorkFlowStage->id)
                ->where('code_company', Auth::user()->code_company)
                ->delete();

            WorkFlowTaskSuprema::where('id_workflow', $dataWorkFlowStage->id_workflow)
                ->where('id_workflow_stage', $dataWorkFlowStage->id)
                ->where("code_company", Auth::user()->code_company)
                ->delete();
            CommentTaskFlujoSuprema::where('id_flujo', $dataWorkFlowStage->id_workflow)
                ->where('id_stage', $dataWorkFlowStage->id)
                ->where('code_company', Auth::user()->code_company)
                ->delete();

            WorkFlowTaskExpedienteSinoe::where('id_workflow', $dataWorkFlowStage->id_workflow)
                ->where('id_workflow_stage', $dataWorkFlowStage->id)
                ->where("code_company", Auth::user()->code_company)
                ->delete();
            CommentTaskFlujoSinoe::where('id_flujo', $dataWorkFlowStage->id_workflow)
                ->where('id_stage', $dataWorkFlowStage->id)
                ->where('code_company', Auth::user()->code_company)
                ->delete();

            WorkFlowTransitions::where('id_workflow', $dataWorkFlowStage->id_workflow)
                ->where('etapa', $dataWorkFlowStage->nombre)
                ->where("code_company", Auth::user()->code_company)
                ->delete();

            // FlujoAsociadoExpediente::where('id_workflow', $dataWorkFlowStage->id_workflow)
            //                     ->where('etapa', $dataWorkFlowStage->nombre)
            //                     // ->where('id_workflow_stage', $dataWorkFlowStage->id)
            //                     // ->where('table_pertenece', 'transition')
            //                     ->where("code_company", Auth::user()->code_company)
            //                     ->delete();

            // FlujoAsociadoIndecopi::where('id_workflow', $dataWorkFlowStage->id_workflow)
            //                     ->where('etapa', $dataWorkFlowStage->nombre)
            //                     // ->where('id_workflow_stage', $dataWorkFlowStage->id)
            //                     // ->where('table_pertenece', 'transition')
            //                     ->where("code_company", Auth::user()->code_company)
            //                     ->delete();
            // FlujoAsociadoSuprema::where('id_workflow', $dataWorkFlowStage->id_workflow)
            //                     ->where('etapa', $dataWorkFlowStage->nombre)
            //                     // ->where('id_workflow_stage', $dataWorkFlowStage->id)
            //                     // ->where('table_pertenece', 'transition')
            //                     ->where("code_company", Auth::user()->code_company)
            //                     ->delete();
            // FlujoAsociadoExpedienteSinoe::where('id_workflow', $dataWorkFlowStage->id_workflow)
            //                     ->where('etapa', $dataWorkFlowStage->nombre)
            //                     // ->where('id_workflow_stage', $dataWorkFlowStage->id)
            //                     // ->where('table_pertenece', 'transition')
            //                     ->where("code_company", Auth::user()->code_company)
            //                     ->delete();

            WorkFlowsStage::where('id', $id)
                ->where("code_company", Auth::user()->code_company)
                ->delete();
            WorkFlowsTask::where('id_workflow_stage', $id)
                ->where("code_company", Auth::user()->code_company)
                ->delete();
            // return redirect()->back()->with('success','¡Eliminado con éxito!');
            return response()->json("Eliminado");
        }
        return response()->json('error');
    }

    /* **************************
    *
    *   WORK FLOWS TASK CRUD
    *
    * ************************* */

    public function addWorkFlowsTask(Request $request)
    {
        $name = request()->input('name-work-flows-task');
        $descripcion = request()->input('descipcion-work-flows-task');
        $prioridad = request()->input('prioridad-work-flows-task');
        $diasDuracion = request()->input('dias-d-work-flows-task');
        $diasAntes = request()->input('dias-a-work-flows-task');
        $idWFS = request()->input('id-work-flows-stage');
        $idWF = request()->input('id-work-flows');

        if ($name && $name !== '') {
            $exist = WorkFlowsTask::where('nombre', $name)
                ->where("code_company", Auth::user()->code_company)
                ->where('id_workflow_stage', $idWFS)
                ->first();
            if ($exist) {
                return redirect()->back()->with('info', '¡Ya existe una tarea con ese nombre!');
            }
            $newData = [
                'uid' => DB::raw('UUID_SHORT()'),
                'nombre' => $name,
                'descripcion' => $descripcion,
                'prioridad' => $prioridad,
                'dias_duracion' => $diasDuracion,
                'dias_antes_venc' => $diasAntes,
                'id_workflow' => $idWF,
                'id_workflow_stage' => $idWFS,
                'code_user' => Auth::user()->code_user,
                'code_company' => Auth::user()->code_company,
                'attached_files' => null,
                'metadata' => null,
            ];

            $idWFT = WorkFlowsTask::insertGetId($newData);
            // Lista de expedientes que estan activado el flujo de trabajo
            $dataWFStage = WorkFlowsStage::where('id', $idWFS)->first();
            $dataWF = WorkFlows::where('id', $idWF)->first();
            $listData = WorkFlowTaskExpediente::where('id_workflow', $idWF)
                ->where('id_workflow_stage', $idWFS)
                ->where("code_company", Auth::user()->code_company)
                ->groupBy('id_exp')
                ->select('id_exp')
                ->get();


            $fechaHoy = Carbon::now();
            $fechaHoyAlerta = Carbon::now();
            if (count($listData) > 0) {
                foreach ($listData as $item) {
                    $WorkFlowTaskExpedienteTable = new WorkFlowTaskExpediente();
                    $WorkFlowTaskExpedienteTable->id_workflow = $idWF;
                    $WorkFlowTaskExpedienteTable->id_workflow_stage = $idWFS;
                    $WorkFlowTaskExpedienteTable->id_workflow_task = $idWFT;
                    $WorkFlowTaskExpedienteTable->id_exp = $item->id_exp;
                    $WorkFlowTaskExpedienteTable->nombre_etapa = $dataWFStage->nombre;
                    $WorkFlowTaskExpedienteTable->nombre_flujo = $dataWF->nombre;
                    $WorkFlowTaskExpedienteTable->nombre = $name;
                    $WorkFlowTaskExpedienteTable->descripcion = $descripcion;
                    $WorkFlowTaskExpedienteTable->dias_duracion = $diasDuracion;
                    $WorkFlowTaskExpedienteTable->dias_antes_venc = $diasAntes;

                    $fechaDuracion = $fechaHoy->addDays($diasDuracion);
                    $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                    $fechaAlerta = $fechaHoyAlerta->addDays($diasAntes);
                    $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                    $WorkFlowTaskExpedienteTable->fecha_limite = $fechaFormateadaDuracion;
                    $WorkFlowTaskExpedienteTable->fecha_alerta = $fechaFormateadaAlerta;
                    $WorkFlowTaskExpedienteTable->fecha_finalizada = null;

                    $WorkFlowTaskExpedienteTable->attached_files = null;
                    $WorkFlowTaskExpedienteTable->estado = 'En progreso';
                    $WorkFlowTaskExpedienteTable->prioridad = $prioridad;
                    $WorkFlowTaskExpedienteTable->code_user = Auth::user()->code_user;
                    $WorkFlowTaskExpedienteTable->code_company = Auth::user()->code_company;
                    $WorkFlowTaskExpedienteTable->save();
                }
            } else {
                $listDataFlujo = FlujoAsociadoExpediente::where('id_workflow', $idWF)
                    ->where('id_workflow_stage', $idWFS)
                    ->where("code_company", Auth::user()->code_company)
                    ->groupBy('id_exp')
                    ->select('id_exp')
                    ->get();
                if (count($listDataFlujo) > 0) {
                    foreach ($listDataFlujo as $item) {
                        $WorkFlowTaskExpedienteTable = new WorkFlowTaskExpediente();
                        $WorkFlowTaskExpedienteTable->id_workflow = $idWF;
                        $WorkFlowTaskExpedienteTable->id_workflow_stage = $idWFS;
                        $WorkFlowTaskExpedienteTable->id_workflow_task = $idWFT;
                        $WorkFlowTaskExpedienteTable->id_exp = $item->id_exp;
                        $WorkFlowTaskExpedienteTable->nombre_etapa = $dataWFStage->nombre;
                        $WorkFlowTaskExpedienteTable->nombre_flujo = $dataWF->nombre;
                        $WorkFlowTaskExpedienteTable->nombre = $name;
                        $WorkFlowTaskExpedienteTable->descripcion = $descripcion;
                        $WorkFlowTaskExpedienteTable->dias_duracion = $diasDuracion;
                        $WorkFlowTaskExpedienteTable->dias_antes_venc = $diasAntes;

                        $fechaDuracion = $fechaHoy->addDays($diasDuracion);
                        $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                        $fechaAlerta = $fechaHoyAlerta->addDays($diasAntes);
                        $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                        $WorkFlowTaskExpedienteTable->fecha_limite = $fechaFormateadaDuracion;
                        $WorkFlowTaskExpedienteTable->fecha_alerta = $fechaFormateadaAlerta;
                        $WorkFlowTaskExpedienteTable->fecha_finalizada = null;

                        $WorkFlowTaskExpedienteTable->attached_files = null;
                        $WorkFlowTaskExpedienteTable->estado = 'En progreso';
                        $WorkFlowTaskExpedienteTable->prioridad = $prioridad;
                        $WorkFlowTaskExpedienteTable->code_user = Auth::user()->code_user;
                        $WorkFlowTaskExpedienteTable->code_company = Auth::user()->code_company;
                        $WorkFlowTaskExpedienteTable->save();
                    }
                }
            }
            $listDataIndecopi = WorkFlowTaskIndecopi::where('id_workflow', $idWF)
                ->where('id_workflow_stage', $idWFS)
                ->where("code_company", Auth::user()->code_company)
                ->groupBy('id_exp')
                ->select('id_exp')
                ->get();

            $fechaHoyIndecopi = Carbon::now();
            $fechaHoyAlertaIndecopi = Carbon::now();
            if (count($listDataIndecopi) > 0) {
                foreach ($listDataIndecopi as $item) {
                    $WorkFlowTaskExpedienteTable = new WorkFlowTaskIndecopi();
                    $WorkFlowTaskExpedienteTable->id_workflow = $idWF;
                    $WorkFlowTaskExpedienteTable->id_workflow_stage = $idWFS;
                    $WorkFlowTaskExpedienteTable->id_workflow_task = $idWFT;
                    $WorkFlowTaskExpedienteTable->id_exp = $item->id_exp;
                    $WorkFlowTaskExpedienteTable->nombre_etapa = $dataWFStage->nombre;
                    $WorkFlowTaskExpedienteTable->nombre_flujo = $dataWF->nombre;
                    $WorkFlowTaskExpedienteTable->nombre = $name;
                    $WorkFlowTaskExpedienteTable->descripcion = $descripcion;
                    $WorkFlowTaskExpedienteTable->dias_duracion = $diasDuracion;
                    $WorkFlowTaskExpedienteTable->dias_antes_venc = $diasAntes;

                    $fechaDuracion = $fechaHoyIndecopi->addDays($diasDuracion);
                    $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                    $fechaAlerta = $fechaHoyAlertaIndecopi->addDays($diasAntes);
                    $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                    $WorkFlowTaskExpedienteTable->fecha_limite = $fechaFormateadaDuracion;
                    $WorkFlowTaskExpedienteTable->fecha_alerta = $fechaFormateadaAlerta;
                    $WorkFlowTaskExpedienteTable->fecha_finalizada = null;

                    $WorkFlowTaskExpedienteTable->attached_files = null;
                    $WorkFlowTaskExpedienteTable->estado = 'En progreso';
                    $WorkFlowTaskExpedienteTable->prioridad = $prioridad;
                    $WorkFlowTaskExpedienteTable->code_user = Auth::user()->code_user;
                    $WorkFlowTaskExpedienteTable->code_company = Auth::user()->code_company;
                    $WorkFlowTaskExpedienteTable->save();
                }
            } else {
                $listDataIndecopiFlujo = FlujoAsociadoIndecopi::where('id_workflow', $idWF)
                    ->where('id_workflow_stage', $idWFS)
                    ->where("code_company", Auth::user()->code_company)
                    ->groupBy('id_exp')
                    ->select('id_exp')
                    ->get();
                if (count($listDataIndecopiFlujo) > 0) {
                    foreach ($listDataIndecopiFlujo as $item) {
                        $WorkFlowTaskExpedienteTable = new WorkFlowTaskIndecopi();
                        $WorkFlowTaskExpedienteTable->id_workflow = $idWF;
                        $WorkFlowTaskExpedienteTable->id_workflow_stage = $idWFS;
                        $WorkFlowTaskExpedienteTable->id_workflow_task = $idWFT;
                        $WorkFlowTaskExpedienteTable->id_exp = $item->id_exp;
                        $WorkFlowTaskExpedienteTable->nombre_etapa = $dataWFStage->nombre;
                        $WorkFlowTaskExpedienteTable->nombre_flujo = $dataWF->nombre;
                        $WorkFlowTaskExpedienteTable->nombre = $name;
                        $WorkFlowTaskExpedienteTable->descripcion = $descripcion;
                        $WorkFlowTaskExpedienteTable->dias_duracion = $diasDuracion;
                        $WorkFlowTaskExpedienteTable->dias_antes_venc = $diasAntes;

                        $fechaDuracion = $fechaHoyIndecopi->addDays($diasDuracion);
                        $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                        $fechaAlerta = $fechaHoyAlertaIndecopi->addDays($diasAntes);
                        $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                        $WorkFlowTaskExpedienteTable->fecha_limite = $fechaFormateadaDuracion;
                        $WorkFlowTaskExpedienteTable->fecha_alerta = $fechaFormateadaAlerta;
                        $WorkFlowTaskExpedienteTable->fecha_finalizada = null;

                        $WorkFlowTaskExpedienteTable->attached_files = null;
                        $WorkFlowTaskExpedienteTable->estado = 'En progreso';
                        $WorkFlowTaskExpedienteTable->prioridad = $prioridad;
                        $WorkFlowTaskExpedienteTable->code_user = Auth::user()->code_user;
                        $WorkFlowTaskExpedienteTable->code_company = Auth::user()->code_company;
                        $WorkFlowTaskExpedienteTable->save();
                    }
                }
            }

            $listDataSuprema = WorkFlowTaskSuprema::where('id_workflow', $idWF)
                ->where('id_workflow_stage', $idWFS)
                ->where("code_company", Auth::user()->code_company)
                ->groupBy('id_exp')
                ->select('id_exp')
                ->get();

            $fechaHoySuprema = Carbon::now();
            $fechaHoyAlertaSuprema = Carbon::now();
            if (count($listDataSuprema) > 0) {
                foreach ($listDataIndecopi as $item) {
                    $WorkFlowTaskSupremaTable = new WorkFlowTaskSuprema();
                    $WorkFlowTaskSupremaTable->id_workflow = $idWF;
                    $WorkFlowTaskSupremaTable->id_workflow_stage = $idWFS;
                    $WorkFlowTaskSupremaTable->id_workflow_task = $idWFT;
                    $WorkFlowTaskSupremaTable->id_exp = $item->id_exp;
                    $WorkFlowTaskSupremaTable->nombre_etapa = $dataWFStage->nombre;
                    $WorkFlowTaskSupremaTable->nombre_flujo = $dataWF->nombre;
                    $WorkFlowTaskSupremaTable->nombre = $name;
                    $WorkFlowTaskSupremaTable->descripcion = $descripcion;
                    $WorkFlowTaskSupremaTable->dias_duracion = $diasDuracion;
                    $WorkFlowTaskSupremaTable->dias_antes_venc = $diasAntes;

                    $fechaDuracion = $fechaHoySuprema->addDays($diasDuracion);
                    $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                    $fechaAlerta = $fechaHoyAlertaSuprema->addDays($diasAntes);
                    $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                    $WorkFlowTaskSupremaTable->fecha_limite = $fechaFormateadaDuracion;
                    $WorkFlowTaskSupremaTable->fecha_alerta = $fechaFormateadaAlerta;
                    $WorkFlowTaskSupremaTable->fecha_finalizada = null;

                    $WorkFlowTaskSupremaTable->attached_files = null;
                    $WorkFlowTaskSupremaTable->estado = 'En progreso';
                    $WorkFlowTaskSupremaTable->prioridad = $prioridad;
                    $WorkFlowTaskSupremaTable->code_user = Auth::user()->code_user;
                    $WorkFlowTaskSupremaTable->code_company = Auth::user()->code_company;
                    $WorkFlowTaskSupremaTable->save();
                }
            } else {
                $listDataSupremaFlujo = FlujoAsociadoSuprema::where('id_workflow', $idWF)
                    ->where('id_workflow_stage', $idWFS)
                    ->where("code_company", Auth::user()->code_company)
                    ->groupBy('id_exp')
                    ->select('id_exp')
                    ->get();
                if (count($listDataSupremaFlujo) > 0) {
                    foreach ($listDataSupremaFlujo as $item) {
                        $WorkFlowTaskSupremaTable = new WorkFlowTaskSuprema();
                        $WorkFlowTaskSupremaTable->id_workflow = $idWF;
                        $WorkFlowTaskSupremaTable->id_workflow_stage = $idWFS;
                        $WorkFlowTaskSupremaTable->id_workflow_task = $idWFT;
                        $WorkFlowTaskSupremaTable->id_exp = $item->id_exp;
                        $WorkFlowTaskSupremaTable->nombre_etapa = $dataWFStage->nombre;
                        $WorkFlowTaskSupremaTable->nombre_flujo = $dataWF->nombre;
                        $WorkFlowTaskSupremaTable->nombre = $name;
                        $WorkFlowTaskSupremaTable->descripcion = $descripcion;
                        $WorkFlowTaskSupremaTable->dias_duracion = $diasDuracion;
                        $WorkFlowTaskSupremaTable->dias_antes_venc = $diasAntes;

                        $fechaDuracion = $fechaHoySuprema->addDays($diasDuracion);
                        $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                        $fechaAlerta = $fechaHoyAlertaSuprema->addDays($diasAntes);
                        $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                        $WorkFlowTaskSupremaTable->fecha_limite = $fechaFormateadaDuracion;
                        $WorkFlowTaskSupremaTable->fecha_alerta = $fechaFormateadaAlerta;
                        $WorkFlowTaskSupremaTable->fecha_finalizada = null;

                        $WorkFlowTaskSupremaTable->attached_files = null;
                        $WorkFlowTaskSupremaTable->estado = 'En progreso';
                        $WorkFlowTaskSupremaTable->prioridad = $prioridad;
                        $WorkFlowTaskSupremaTable->code_user = Auth::user()->code_user;
                        $WorkFlowTaskSupremaTable->code_company = Auth::user()->code_company;
                        $WorkFlowTaskSupremaTable->save();
                    }
                }
            }
            return redirect()->back()->with('success', '¡Se registro correctamente!');
        }
        return redirect()->back()->with('error', '¡No se pudo registrar!');
    }

    public function updateWorkFlowsTask()
    {
        $id = request()->input('id-work-flows-task-edit');
        $name = request()->input('name-work-flows-task-edit');
        $descripcion = request()->input('descripcion-work-flows-task-edit');
        $prioridad = request()->input('prioridad-work-flows-task-edit');
        $diasDuracion = request()->input('dias-d-work-flows-task-edit');
        $diasAntes = request()->input('dias-a-work-flows-task-edit');


        if ($id && $id !== '') {
            $newData = [
                'nombre' => $name,
                'descripcion' => $descripcion,
                'prioridad' => $prioridad,
                'dias_duracion' => $diasDuracion,
                'dias_antes_venc' => $diasAntes,
            ];

            $data = WorkFlowsTask::where('id', $id)
                ->where("code_company", Auth::user()->code_company)
                ->first();
            $dataExist = WorkFlowsTask::where('nombre', $name)
                ->where('code_company', Auth::user()->code_company)
                ->where('id_workflow', $data->id_workflow)
                ->where('id_workflow_stage', $data->id_workflow_stage)
                ->first();
            if ($dataExist) {
                $upData = [
                    'descripcion' => $descripcion,
                    'prioridad' => $prioridad,
                    'dias_duracion' => $diasDuracion,
                    'dias_antes_venc' => $diasAntes,
                ];
                WorkFlowsTask::where('id', $id)->update($upData);
                // Lista de expedientes que estan activado el flujo de trabajo
                $dataWFT = WorkFlowsTask::where('id', $id)->first();
                $dataWFStage = WorkFlowsStage::where('id', $dataWFT->id_workflow_stage)->first();
                $dataWF = WorkFlows::where('id', $dataWFT->id_workflow)->first();

                $listData = WorkFlowTaskExpediente::where('id_workflow', $dataWFT->id_workflow)
                    ->where('id_workflow_stage', $dataWFT->id_workflow_stage)
                    ->where('code_company', Auth::user()->code_company)
                    ->get();

                if (count($listData) > 0) {
                    foreach ($listData as $item) {
                        $WorkFlowTaskExpedienteTable = WorkFlowTaskExpediente::find($item->id);
                        $WorkFlowTaskExpedienteTable->id_workflow = $dataWFT->id_workflow;
                        $WorkFlowTaskExpedienteTable->id_workflow_stage = $dataWFT->id_workflow_stage;
                        $WorkFlowTaskExpedienteTable->id_workflow_task = $id;
                        $WorkFlowTaskExpedienteTable->id_exp = $item->id_exp;
                        $WorkFlowTaskExpedienteTable->nombre_etapa = $dataWFStage->nombre;
                        $WorkFlowTaskExpedienteTable->nombre_flujo = $dataWF->nombre;
                        $WorkFlowTaskExpedienteTable->nombre = $name;
                        $WorkFlowTaskExpedienteTable->descripcion = $descripcion;
                        $WorkFlowTaskExpedienteTable->dias_duracion = $diasDuracion;
                        $WorkFlowTaskExpedienteTable->dias_antes_venc = $diasAntes;

                        $WorkFlowTaskExpedienteTable->attached_files = null;
                        $WorkFlowTaskExpedienteTable->prioridad = $prioridad;
                        $WorkFlowTaskExpedienteTable->code_user = Auth::user()->code_user;
                        $WorkFlowTaskExpedienteTable->code_company = Auth::user()->code_company;
                        $WorkFlowTaskExpedienteTable->save();
                    }
                }

                $listDataIndecopi = WorkFlowTaskIndecopi::where('id_workflow', $dataWFT->id_workflow)
                    ->where('id_workflow_stage', $dataWFT->id_workflow_stage)
                    ->where('code_company', Auth::user()->code_company)
                    ->get();

                // $fechaHoyIndecopi = Carbon::now();
                // $fechaHoyAlertaIndecopi = Carbon::now();
                if (count($listDataIndecopi) > 0) {
                    foreach ($listDataIndecopi as $itemIndecopi) {
                        $WorkFlowTaskExpedienteTable = WorkFlowTaskIndecopi::find($itemIndecopi->id);
                        $WorkFlowTaskExpedienteTable->id_workflow = $dataWFT->id_workflow;
                        $WorkFlowTaskExpedienteTable->id_workflow_stage = $dataWFT->id_workflow_stage;
                        $WorkFlowTaskExpedienteTable->id_workflow_task = $id;
                        $WorkFlowTaskExpedienteTable->id_exp = $itemIndecopi->id_exp;
                        $WorkFlowTaskExpedienteTable->nombre_etapa = $dataWFStage->nombre;
                        $WorkFlowTaskExpedienteTable->nombre_flujo = $dataWF->nombre;
                        $WorkFlowTaskExpedienteTable->nombre = $name;
                        $WorkFlowTaskExpedienteTable->descripcion = $descripcion;
                        $WorkFlowTaskExpedienteTable->dias_duracion = $diasDuracion;
                        $WorkFlowTaskExpedienteTable->dias_antes_venc = $diasAntes;

                        // $fechaDuracion = $fechaHoyIndecopi->addDays($diasDuracion);
                        // $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                        // $fechaAlerta = $fechaHoyAlertaIndecopi->addDays($diasAntes);
                        // $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                        // $WorkFlowTaskExpedienteTable->fecha_limite = $fechaFormateadaDuracion;
                        // $WorkFlowTaskExpedienteTable->fecha_alerta = $fechaFormateadaAlerta;
                        // $WorkFlowTaskExpedienteTable->fecha_finalizada = null;

                        $WorkFlowTaskExpedienteTable->attached_files = null;
                        // $WorkFlowTaskExpedienteTable->estado = 'En progreso';
                        $WorkFlowTaskExpedienteTable->prioridad = $prioridad;
                        $WorkFlowTaskExpedienteTable->code_user = Auth::user()->code_user;
                        $WorkFlowTaskExpedienteTable->code_company = Auth::user()->code_company;
                        $WorkFlowTaskExpedienteTable->save();
                    }
                }

                $listDataSuprema = WorkFlowTaskSuprema::where('id_workflow', $dataWFT->id_workflow)
                    ->where('id_workflow_stage', $dataWFT->id_workflow_stage)
                    ->where('code_company', Auth::user()->code_company)
                    ->get();

                if (count($listDataSuprema) > 0) {
                    foreach ($listDataSuprema as $itemSuprema) {
                        $WorkFlowTaskSupremaTable = WorkFlowTaskSuprema::find($itemSuprema->id);
                        $WorkFlowTaskSupremaTable->id_workflow = $dataWFT->id_workflow;
                        $WorkFlowTaskSupremaTable->id_workflow_stage = $dataWFT->id_workflow_stage;
                        $WorkFlowTaskSupremaTable->id_workflow_task = $id;
                        $WorkFlowTaskSupremaTable->id_exp = $itemSuprema->id_exp;
                        $WorkFlowTaskSupremaTable->nombre_etapa = $dataWFStage->nombre;
                        $WorkFlowTaskSupremaTable->nombre_flujo = $dataWF->nombre;
                        $WorkFlowTaskSupremaTable->nombre = $name;
                        $WorkFlowTaskSupremaTable->descripcion = $descripcion;
                        $WorkFlowTaskSupremaTable->dias_duracion = $diasDuracion;
                        $WorkFlowTaskSupremaTable->dias_antes_venc = $diasAntes;
                        $WorkFlowTaskSupremaTable->attached_files = null;
                        $WorkFlowTaskSupremaTable->prioridad = $prioridad;
                        $WorkFlowTaskSupremaTable->code_user = Auth::user()->code_user;
                        $WorkFlowTaskSupremaTable->code_company = Auth::user()->code_company;
                        $WorkFlowTaskSupremaTable->save();
                    }
                }

                $listDataSinoe = WorkFlowTaskExpedienteSinoe::where('id_workflow', $dataWFT->id_workflow)
                    ->where('id_workflow_stage', $dataWFT->id_workflow_stage)
                    ->where('code_company', Auth::user()->code_company)
                    ->get();

                if (count($listDataSinoe) > 0) {
                    foreach ($listDataSinoe as $itemSinoe) {
                        $WorkFlowTaskSinoeTable = WorkFlowTaskExpedienteSinoe::find($itemSinoe->id);
                        $WorkFlowTaskSinoeTable->id_workflow = $dataWFT->id_workflow;
                        $WorkFlowTaskSinoeTable->id_workflow_stage = $dataWFT->id_workflow_stage;
                        $WorkFlowTaskSinoeTable->id_workflow_task = $id;
                        $WorkFlowTaskSinoeTable->id_exp = $itemSinoe->id_exp;
                        $WorkFlowTaskSinoeTable->nombre_etapa = $dataWFStage->nombre;
                        $WorkFlowTaskSinoeTable->nombre_flujo = $dataWF->nombre;
                        $WorkFlowTaskSinoeTable->nombre = $name;
                        $WorkFlowTaskSinoeTable->descripcion = $descripcion;
                        $WorkFlowTaskSinoeTable->dias_duracion = $diasDuracion;
                        $WorkFlowTaskSinoeTable->dias_antes_venc = $diasAntes;
                        $WorkFlowTaskSinoeTable->attached_files = null;
                        $WorkFlowTaskSinoeTable->prioridad = $prioridad;
                        $WorkFlowTaskSinoeTable->code_user = Auth::user()->code_user;
                        $WorkFlowTaskSinoeTable->code_company = Auth::user()->code_company;
                        $WorkFlowTaskSinoeTable->save();
                    }
                }

                return redirect()->back()->with('info', '¡Se actualizaron todos los campos, excepto el nombre, debido a que ya existe una tarea registrada con ese nombre.!');
            }

            WorkFlowsTask::where('id', $id)->update($newData);

            // Lista de expedientes que estan activado el flujo de trabajo
            $dataWFT = WorkFlowsTask::where('id', $id)->first();
            $dataWFStage = WorkFlowsStage::where('id', $dataWFT->id_workflow_stage)->first();
            $dataWF = WorkFlows::where('id', $dataWFT->id_workflow)->first();

            $listData = WorkFlowTaskExpediente::where('id_workflow', $dataWFT->id_workflow)
                ->where('id_workflow_stage', $dataWFT->id_workflow_stage)
                ->where('code_company', Auth::user()->code_company)
                ->get();

            // $fechaHoy = Carbon::now();
            // $fechaHoyAlerta = Carbon::now();
            if (count($listData) > 0) {
                foreach ($listData as $item) {
                    $WorkFlowTaskExpedienteTable = WorkFlowTaskExpediente::find($item->id);
                    $WorkFlowTaskExpedienteTable->id_workflow = $dataWFT->id_workflow;
                    $WorkFlowTaskExpedienteTable->id_workflow_stage = $dataWFT->id_workflow_stage;
                    $WorkFlowTaskExpedienteTable->id_workflow_task = $id;
                    $WorkFlowTaskExpedienteTable->id_exp = $item->id_exp;
                    $WorkFlowTaskExpedienteTable->nombre_etapa = $dataWFStage->nombre;
                    $WorkFlowTaskExpedienteTable->nombre_flujo = $dataWF->nombre;
                    $WorkFlowTaskExpedienteTable->nombre = $name;
                    $WorkFlowTaskExpedienteTable->descripcion = $descripcion;
                    $WorkFlowTaskExpedienteTable->dias_duracion = $diasDuracion;
                    $WorkFlowTaskExpedienteTable->dias_antes_venc = $diasAntes;

                    // $fechaDuracion = $fechaHoy->addDays($diasDuracion);
                    // $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                    // $fechaAlerta = $fechaHoyAlerta->addDays($diasAntes);
                    // $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                    // $WorkFlowTaskExpedienteTable->fecha_limite = $fechaFormateadaDuracion;
                    // $WorkFlowTaskExpedienteTable->fecha_alerta = $fechaFormateadaAlerta;
                    // $WorkFlowTaskExpedienteTable->fecha_finalizada = null;

                    $WorkFlowTaskExpedienteTable->attached_files = null;
                    // $WorkFlowTaskExpedienteTable->estado = 'En progreso';
                    $WorkFlowTaskExpedienteTable->prioridad = $prioridad;
                    $WorkFlowTaskExpedienteTable->code_user = Auth::user()->code_user;
                    $WorkFlowTaskExpedienteTable->code_company = Auth::user()->code_company;
                    $WorkFlowTaskExpedienteTable->save();
                }
            }

            $listDataIndecopi = WorkFlowTaskIndecopi::where('id_workflow', $dataWFT->id_workflow)
                ->where('id_workflow_stage', $dataWFT->id_workflow_stage)
                ->where('code_company', Auth::user()->code_company)
                ->get();

            // $fechaHoyIndecopi = Carbon::now();
            // $fechaHoyAlertaIndecopi = Carbon::now();
            if (count($listDataIndecopi) > 0) {
                foreach ($listDataIndecopi as $itemIndecopi) {
                    $WorkFlowTaskIndecopiTable = WorkFlowTaskIndecopi::find($itemIndecopi->id);
                    $WorkFlowTaskIndecopiTable->id_workflow = $dataWFT->id_workflow;
                    $WorkFlowTaskIndecopiTable->id_workflow_stage = $dataWFT->id_workflow_stage;
                    $WorkFlowTaskIndecopiTable->id_workflow_task = $id;
                    $WorkFlowTaskIndecopiTable->id_exp = $itemIndecopi->id_exp;
                    $WorkFlowTaskIndecopiTable->nombre_etapa = $dataWFStage->nombre;
                    $WorkFlowTaskIndecopiTable->nombre_flujo = $dataWF->nombre;
                    $WorkFlowTaskIndecopiTable->nombre = $name;
                    $WorkFlowTaskIndecopiTable->descripcion = $descripcion;
                    $WorkFlowTaskIndecopiTable->dias_duracion = $diasDuracion;
                    $WorkFlowTaskIndecopiTable->dias_antes_venc = $diasAntes;

                    // $fechaDuracion = $fechaHoyIndecopi->addDays($diasDuracion);
                    // $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                    // $fechaAlerta = $fechaHoyAlertaIndecopi->addDays($diasAntes);
                    // $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                    // $WorkFlowTaskExpedienteTable->fecha_limite = $fechaFormateadaDuracion;
                    // $WorkFlowTaskExpedienteTable->fecha_alerta = $fechaFormateadaAlerta;
                    // $WorkFlowTaskExpedienteTable->fecha_finalizada = null;

                    $WorkFlowTaskExpedienteTable->attached_files = null;
                    // $WorkFlowTaskExpedienteTable->estado = 'En progreso';
                    $WorkFlowTaskExpedienteTable->prioridad = $prioridad;
                    $WorkFlowTaskExpedienteTable->code_user = Auth::user()->code_user;
                    $WorkFlowTaskExpedienteTable->code_company = Auth::user()->code_company;
                    $WorkFlowTaskExpedienteTable->save();
                }
            }

            $listDataSuprema = WorkFlowTaskSuprema::where('id_workflow', $dataWFT->id_workflow)
                ->where('id_workflow_stage', $dataWFT->id_workflow_stage)
                ->where('code_company', Auth::user()->code_company)
                ->get();

            if (count($listDataSuprema) > 0) {
                foreach ($listDataSuprema as $itemSuprema) {
                    $WorkFlowTaskSupremaTable = WorkFlowTaskSuprema::find($itemSuprema->id);
                    $WorkFlowTaskSupremaTable->id_workflow = $dataWFT->id_workflow;
                    $WorkFlowTaskSupremaTable->id_workflow_stage = $dataWFT->id_workflow_stage;
                    $WorkFlowTaskSupremaTable->id_workflow_task = $id;
                    $WorkFlowTaskSupremaTable->id_exp = $itemSuprema->id_exp;
                    $WorkFlowTaskSupremaTable->nombre_etapa = $dataWFStage->nombre;
                    $WorkFlowTaskSupremaTable->nombre_flujo = $dataWF->nombre;
                    $WorkFlowTaskSupremaTable->nombre = $name;
                    $WorkFlowTaskSupremaTable->descripcion = $descripcion;
                    $WorkFlowTaskSupremaTable->dias_duracion = $diasDuracion;
                    $WorkFlowTaskSupremaTable->dias_antes_venc = $diasAntes;


                    $WorkFlowTaskSupremaTable->attached_files = null;
                    $WorkFlowTaskSupremaTable->prioridad = $prioridad;
                    $WorkFlowTaskSupremaTable->code_user = Auth::user()->code_user;
                    $WorkFlowTaskSupremaTable->code_company = Auth::user()->code_company;
                    $WorkFlowTaskSupremaTable->save();
                }
            }

            $listDataSinoe = WorkFlowTaskExpedienteSinoe::where('id_workflow', $dataWFT->id_workflow)
                ->where('id_workflow_stage', $dataWFT->id_workflow_stage)
                ->where('code_company', Auth::user()->code_company)
                ->get();

            if (count($listDataSinoe) > 0) {
                foreach ($listDataSinoe as $itemSinoe) {
                    $WorkFlowTaskSinoeTable = WorkFlowTaskExpedienteSinoe::find($itemSinoe->id);
                    $WorkFlowTaskSinoeTable->id_workflow = $dataWFT->id_workflow;
                    $WorkFlowTaskSinoeTable->id_workflow_stage = $dataWFT->id_workflow_stage;
                    $WorkFlowTaskSinoeTable->id_workflow_task = $id;
                    $WorkFlowTaskSinoeTable->id_exp = $itemSinoe->id_exp;
                    $WorkFlowTaskSinoeTable->nombre_etapa = $dataWFStage->nombre;
                    $WorkFlowTaskSinoeTable->nombre_flujo = $dataWF->nombre;
                    $WorkFlowTaskSinoeTable->nombre = $name;
                    $WorkFlowTaskSinoeTable->descripcion = $descripcion;
                    $WorkFlowTaskSinoeTable->dias_duracion = $diasDuracion;
                    $WorkFlowTaskSinoeTable->dias_antes_venc = $diasAntes;
                    $WorkFlowTaskSinoeTable->attached_files = null;
                    $WorkFlowTaskSinoeTable->prioridad = $prioridad;
                    $WorkFlowTaskSinoeTable->code_user = Auth::user()->code_user;
                    $WorkFlowTaskSinoeTable->code_company = Auth::user()->code_company;
                    $WorkFlowTaskSinoeTable->save();
                }
            }

            return redirect()->back()->with('success', '¡Se actualizó correctamente!');
        }
        return redirect()->back()->with('error', '¡No se pudo actualizar!');
    }

    public function deleteWorkFlowsTask()
    {
        $id = request()->input('id');
        if ($id && $id !== '') {
            $dataWFT = WorkFlowsTask::where('id', $id)->first();

            // return redirect()->back()->with('success','¡Eliminado con éxito!');

            // Lista de expedientes que estan activado el flujo de trabajo
            $listData = WorkFlowTaskExpediente::where('id_workflow', $dataWFT->id_workflow)
                ->where('id_workflow_stage', $dataWFT->id_workflow_stage)
                ->where('id_workflow_task', $id)
                ->where('code_company', Auth::user()->code_company)
                ->get();

            // eliminar tarea del flujo a todos lso expedientes que lo utilizan
            if (count($listData) > 0) {
                foreach ($listData as $item) {
                    WorkFlowTaskExpediente::where('id', $item->id)->delete();
                }
            }

            // INDECOPI Lista de expedientes que estan activado el flujo de trabajo
            $listDataIndecopi = WorkFlowTaskIndecopi::where('id_workflow', $dataWFT->id_workflow)
                ->where('id_workflow_stage', $dataWFT->id_workflow_stage)
                ->where('id_workflow_task', $id)
                ->where('code_company', Auth::user()->code_company)
                ->get();

            // INDECOPI eliminar tarea del flujo a todos lso expedientes que lo utilizan
            if (count($listDataIndecopi) > 0) {
                foreach ($listDataIndecopi as $itemIndecopi) {
                    WorkFlowTaskIndecopi::where('id', $itemIndecopi->id)->delete();
                }
            }

            // SUPREMA Lista de expedientes que estan activado el flujo de trabajo
            $listDataSuprema = WorkFlowTaskSuprema::where('id_workflow', $dataWFT->id_workflow)
                ->where('id_workflow_stage', $dataWFT->id_workflow_stage)
                ->where('id_workflow_task', $id)
                ->where('code_company', Auth::user()->code_company)
                ->get();

            // SUPREMA eliminar tarea del flujo a todos lso expedientes que lo utilizan
            if (count($listDataSuprema) > 0) {
                foreach ($listDataSuprema as $itemSuprema) {
                    WorkFlowTaskSuprema::where('id', $itemSuprema->id)->delete();
                }
            }

            // SINOE Lista de expedientes que estan activado el flujo de trabajo
            $listDataSinoe = WorkFlowTaskExpedienteSinoe::where('id_workflow', $dataWFT->id_workflow)
                ->where('id_workflow_stage', $dataWFT->id_workflow_stage)
                ->where('id_workflow_task', $id)
                ->where('code_company', Auth::user()->code_company)
                ->get();

            // SINOE eliminar tarea del flujo a todos lso expedientes que lo utilizan
            if (count($listDataSinoe) > 0) {
                foreach ($listDataSinoe as $itemSinoe) {
                    WorkFlowTaskExpedienteSinoe::where('id', $itemSinoe->id)->delete();
                }
            }

            WorkFlowsTask::where('id', $id)->delete();

            return response()->json("Eliminado");
        }
        return response()->json('error');
    }


    /* **************************
    *
    *   WORK FLOWS TRANSITION CRUD
    *
    * ************************* */

    public function addWorkFlowsTransition(Request $request)
    {
        // dd($request);
        $etapa = request()->input('transicion-etapa');
        $condicion = request()->input('transicion-condicion');

        $idWFS = request()->input('id-work-flows-stage');
        $idWF = request()->input('id-work-flows');
        if ($etapa && $etapa !== '') {
            $exist = WorkFlowTransitions::where('etapa', $etapa)
                ->where('id_workflow_stage', $idWFS)
                ->where('id_workflow', $idWF)
                ->first();
            if ($exist) {
                return redirect()->back()->with('info', '¡Ya existe!');
            }
            $newData = [
                'etapa' => $etapa,
                'condicion' => $condicion,
                'id_workflow' => $idWF,
                'id_workflow_stage' => $idWFS,
                'code_user' => Auth::user()->code_user,
                'code_company' => Auth::user()->code_company,
                'metadata' => null,
            ];

            WorkFlowTransitions::insertGetId($newData);

            // $dataTransitonExp = FlujoAsociadoExpediente::where('id_workflow', $idWF)
            //                     ->where('id_workflow_stage', $idWFS)
            //                     ->select('id_exp')
            //                     ->orderBy('id_exp')
            //                     ->get();
            // if ($dataTransitonExp){
            //     foreach ($dataTransitonExp as $key => $value) {
            //         $dateNow = Carbon::now();
            //         FlujoAsociadoExpediente::insert([
            //             'estado' => 'activo',
            //             'id_exp' => $value->id_exp,
            //             'id_workflow' => $idWF,
            //             'id_workflow_stage' => $idWFS,
            //             'date_time' => $dateNow,
            //             'code_user' => Auth::user()->code_user,
            //             'code_company' => Auth::user()->code_company,
            //             'etapa' => $etapa,
            //             'condicion'  => $condicion,
            //             'estado_transition' => 'pendiente',
            //             'table_pertenece' => 'transition',
            //             'metadata' => null,
            //         ]);
            //     }
            // }

            // $dataTransitonIndecopi = FlujoAsociadoIndecopi::where('id_workflow', $idWF)
            //                     ->where('id_workflow_stage', $idWFS)
            //                     ->get();
            // if ($dataTransitonIndecopi){
            //     foreach ($dataTransitonIndecopi as $key => $value) {
            //         $dateNow = Carbon::now();
            //         FlujoAsociadoIndecopi::insert([
            //             'estado' => 'activo',
            //             'id_exp' => $value->id_exp,
            //             'id_workflow' => $idWF,
            //             'id_workflow_stage' => $idWFS,
            //             'date_time' => $dateNow,
            //             'code_user' => Auth::user()->code_user,
            //             'code_company' => Auth::user()->code_company,
            //             'etapa' => $etapa,
            //             'condicion'  => $condicion,
            //             'estado_transition' => 'pendiente',
            //             'table_pertenece' => 'transition',
            //             'metadata' => null,
            //         ]);
            //     }
            // }

            // $dataTransitonSuprema = FlujoAsociadoSuprema::where('id_workflow', $idWF)
            //                     ->where('id_workflow_stage', $idWFS)
            //                     ->get();
            // if ($dataTransitonSuprema){
            //     foreach ($dataTransitonSuprema as $key => $value) {
            //         $dateNow = Carbon::now();
            //         FlujoAsociadoSuprema::insert([
            //             'estado' => 'activo',
            //             'id_exp' => $value->id_exp,
            //             'id_workflow' => $idWF,
            //             'id_workflow_stage' => $idWFS,
            //             'date_time' => $dateNow,
            //             'code_user' => Auth::user()->code_user,
            //             'code_company' => Auth::user()->code_company,
            //             'etapa' => $etapa,
            //             'condicion'  => $condicion,
            //             'estado_transition' => 'pendiente',
            //             'table_pertenece' => 'transition',
            //             'metadata' => null,
            //         ]);
            //     }
            // }

            // $dataTransitonSinoe = FlujoAsociadoExpedienteSinoe::where('id_workflow', $idWF)
            //                     ->where('id_workflow_stage', $idWFS)
            //                     ->get();
            // if ($dataTransitonSinoe){
            //     foreach ($dataTransitonSinoe as $key => $value) {
            //         $dateNow = Carbon::now();
            //         FlujoAsociadoExpedienteSinoe::insert([
            //             'estado' => 'activo',
            //             'id_exp' => $value->id_exp,
            //             'id_workflow' => $idWF,
            //             'id_workflow_stage' => $idWFS,
            //             'date_time' => $dateNow,
            //             'code_user' => Auth::user()->code_user,
            //             'code_company' => Auth::user()->code_company,
            //             'etapa' => $etapa,
            //             'condicion'  => $condicion,
            //             'estado_transition' => 'pendiente',
            //             'table_pertenece' => 'transition',
            //             'metadata' => null,
            //         ]);
            //     }
            // }

            return redirect()->back()->with('success', '¡Se registro correctamente!');
        }
        return redirect()->back()->with('error', '¡No se pudo registrar!');
    }

    public function updateWorkFlowsTransition()
    {
        $id = request()->input('id-work-flows-transition-edit');
        $etapa = request()->input('transicion-etapa-edit');
        $condicion = request()->input('transicion-condicion-edit');
        // $idWorkFlows = request()->input('id-work-flows');
        // $idWorkFlowsStage = request()->input('id-work-flows-stage');


        if ($id && $id !== '') {
            $newData = [
                'etapa' => $etapa,
                'condicion' => $condicion,
            ];

            $data = WorkFlowTransitions::where('id', $id)->where("code_company", Auth::user()->code_company)->first();
            $exist = WorkFlowTransitions::where('code_company', Auth::user()->code_company)
                ->where('etapa', $etapa)
                ->where('id', $id)
                ->first();
            if ($exist) {
                if ($condicion == $exist->condicion) {
                    return redirect()->back()->with('info', '¡Ya existe!');
                } else {
                    WorkFlowTransitions::where('id', $id)->update($newData);
                    // FlujoAsociadoExpediente::where('id_workflow', $data->id_workflow)
                    //     ->where('id_workflow_stage', $data->id_workflow_stage)
                    //     ->where('etapa', $etapa)
                    //     ->where('condicion', $exist->condicion)
                    //     ->update([
                    //         'etapa' => $etapa,
                    //         'condicion' => $condicion,
                    //     ]);
                    // FlujoAsociadoIndecopi::where('id_workflow', $data->id_workflow)
                    //     ->where('id_workflow_stage', $data->id_workflow_stage)
                    //     ->where('etapa', $etapa)
                    //     ->where('condicion', $exist->condicion)
                    //     ->update([
                    //         'etapa' => $etapa,
                    //         'condicion' => $condicion,
                    //     ]);
                    // FlujoAsociadoSuprema::where('id_workflow', $data->id_workflow)
                    //     ->where('id_workflow_stage', $data->id_workflow_stage)
                    //     ->where('etapa', $etapa)
                    //     ->where('condicion', $exist->condicion)
                    //     ->update([
                    //         'etapa' => $etapa,
                    //         'condicion' => $condicion,
                    //     ]);
                    // FlujoAsociadoExpedienteSinoe::where('id_workflow', $data->id_workflow)
                    //     ->where('id_workflow_stage', $data->id_workflow_stage)
                    //     ->where('etapa', $etapa)
                    //     ->where('condicion', $exist->condicion)
                    //     ->update([
                    //         'etapa' => $etapa,
                    //         'condicion' => $condicion,
                    //     ]);
                    return redirect()->back()->with('success', '¡Se actualizó correctamente!');
                }
            }
        }
        return redirect()->back()->with('error', '¡No se pudo actualizar!');
    }

    public function deleteWorkFlowsTransition()
    {
        $id = request()->input('id');
        if ($id && $id !== '') {
            $data = WorkFlowTransitions::where('id', $id)->first();

            FlujoAsociadoExpediente::where('id_workflow', $data->id_workflow)
                ->where('id_workflow_stage', $data->id_workflow_stage)
                ->where('etapa', $data->etapa)
                ->where("code_company", Auth::user()->code_company)
                ->where('condicion', $data->condicion)
                ->delete();
            FlujoAsociadoIndecopi::where('id_workflow', $data->id_workflow)
                ->where('id_workflow_stage', $data->id_workflow_stage)
                ->where('etapa', $data->etapa)
                ->where("code_company", Auth::user()->code_company)
                ->where('condicion', $data->condicion)
                ->delete();
            FlujoAsociadoSuprema::where('id_workflow', $data->id_workflow)
                ->where('id_workflow_stage', $data->id_workflow_stage)
                ->where('etapa', $data->etapa)
                ->where("code_company", Auth::user()->code_company)
                ->where('condicion', $data->condicion)
                ->delete();
            FlujoAsociadoExpedienteSinoe::where('id_workflow', $data->id_workflow)
                ->where('id_workflow_stage', $data->id_workflow_stage)
                ->where('etapa', $data->etapa)
                ->where("code_company", Auth::user()->code_company)
                ->where('condicion', $data->condicion)
                ->delete();

            WorkFlowTransitions::where('id', $id)->delete();
            // return redirect()->back()->with('success','¡Eliminado con éxito!');
            return response()->json("Eliminado");
        }
        return response()->json('error');
    }

    /* **************************
    *
    *   WORK GET
    *
    * ************************* */

    public function getWorkFlowsAll()
    {
        $data = WorkFlows::all();
        return response()->json($data);
    }

    public function getStagesFromWorkFlow()
    {
        $id = request()->input('id');
        $uid = request()->input('uid');
        $data = WorkFlowsStage::where('id_workflow', $id)->get();
        return response()->json($data);
    }

    /* **************************
    *
    *   ADD WORK FLOW IN EXPEDIENTE
    *
    * ************************* */


    public function addWorkFlowTaskExpediente()
    {
        $idExp = request()->input("idExp");
        $nameFlujo = request()->input("nameFlujo");
        $nameEtapaInicial = request()->input("nameEtapaInicial");
        $idFlujo = request()->input("idFlujo");
        $idEtapaInicial = request()->input("idEtapaInicial");

        if ($idExp && $idEtapaInicial && $idFlujo) {
            $dataTaskStage = WorkFlowsTask::where('id_workflow', $idFlujo)
                ->where('id_workflow_stage', $idEtapaInicial)
                ->get();

            $dataTimeNow = Carbon::now();
            $dataNewFlujoAsociado = [
                'estado' => 'activo',
                'id_exp' => $idExp,
                'id_workflow' => $idFlujo,
                'id_workflow_stage' => $idEtapaInicial,
                'date_time' => $dataTimeNow,
                'code_user' => Auth::user()->code_user,
                'code_company' => Auth::user()->code_company,
                'metadata' => null,
                'table_pertenece' => 'flujo',
            ];
            FlujoAsociadoExpediente::insert($dataNewFlujoAsociado);

            // Ver si un usuario vinculado con el expediente
            // acepto el consetimiento en google calendar
            $dataUser = User::where('id', Auth::user()->id)->first();

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

            if (count($dataTaskStage) > 0) {
                foreach ($dataTaskStage as $item) {
                    $WorkFlowTaskExpediente = new WorkFlowTaskExpediente();
                    $WorkFlowTaskExpediente->id_workflow = $item->id_workflow;
                    $WorkFlowTaskExpediente->id_workflow_stage = $item->id_workflow_stage;
                    $WorkFlowTaskExpediente->id_workflow_task = $item->id;
                    $WorkFlowTaskExpediente->id_exp = $idExp;
                    $WorkFlowTaskExpediente->nombre_etapa = $nameEtapaInicial;
                    $WorkFlowTaskExpediente->nombre_flujo = $nameFlujo;
                    $WorkFlowTaskExpediente->nombre = $item->nombre;
                    $WorkFlowTaskExpediente->descripcion = $item->descripcion;
                    $WorkFlowTaskExpediente->dias_duracion = $item->dias_duracion;
                    $WorkFlowTaskExpediente->dias_antes_venc = $item->dias_antes_venc;

                    $fechaDuracion = Carbon::now()->addDays($item->dias_duracion);
                    $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                    $fechaAlerta = Carbon::now()->addDays($item->dias_antes_venc);
                    $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                    $WorkFlowTaskExpediente->fecha_limite = $fechaFormateadaDuracion;
                    $WorkFlowTaskExpediente->fecha_alerta = $fechaFormateadaAlerta;
                    $WorkFlowTaskExpediente->fecha_finalizada = null;

                    $WorkFlowTaskExpediente->attached_files = $item->attached_files;
                    $WorkFlowTaskExpediente->estado = 'En progreso';
                    $WorkFlowTaskExpediente->prioridad = $item->prioridad;
                    $WorkFlowTaskExpediente->code_user = Auth::user()->code_user;
                    $WorkFlowTaskExpediente->code_company = Auth::user()->code_company;
                    $WorkFlowTaskExpediente->save();

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
                    //     $evento->setColorId(9);

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
                    //     $WorkFlowTaskExpediente->metadata = json_encode($metaData);
                    //     $WorkFlowTaskExpediente->save();
                    // }
                }
            }
            // // creacion de transicion por default
            // $dataAllStagesInFlujo = WorkFlowsStage::where("code_company", Auth::user()->code_company)
            // ->where("id_workflow", $idFlujo)
            // ->orderBy("id")
            // ->get();

            // foreach ($dataAllStagesInFlujo as $key => $valueS) {
            //     FlujoAsociadoExpediente::insert([
            //         'estado' => 'activo',
            //         'id_exp' => $idExp,
            //         'id_workflow' => $valueS->id_workflow,
            //         'id_workflow_stage' => $valueS->id,
            //         'date_time' => $dataTimeNow,
            //         'code_user' => Auth::user()->code_user,
            //         'code_company' => Auth::user()->code_company,
            //         'metadata' => null,
            //         'etapa' => $dataAllStagesInFlujo[$key + 1]->nombre ?? null,
            //         'condicion' => null,
            //         'estado_transition' => 'pendiente',
            //         'table_pertenece' => 'normal',
            //     ]);
            // }

            // // creacion de transicion por condicion
            // $dataTransitionExp = WorkFlowTransitions::where('id_workflow', $idFlujo)
            //     ->where('id_workflow_stage', $idEtapaInicial)
            //     ->get();
            // foreach ($dataTransitionExp as $key => $value) {
            //     FlujoAsociadoExpediente::insert([
            //         'estado' => 'activo',
            //         'id_exp' => $idExp,
            //         'id_workflow' => $value->id_workflow,
            //         'id_workflow_stage' => $value->id_workflow_stage,
            //         'date_time' => $dataTimeNow,
            //         'code_user' => Auth::user()->code_user,
            //         'code_company' => Auth::user()->code_company,
            //         'metadata' => null,
            //         'etapa' => $value->etapa,
            //         'condicion' => $value->condicion,
            //         'estado_transition' => 'pendiente',
            //         'table_pertenece' => 'transition',
            //     ]);
            // }
            return response()->json($dataTaskStage);
        }
        return response()->json([]);
    }

    public function getWorkFlowsExpedientes()
    {
        $idExp = request()->input('idExp');
        if ($idExp) {
            $data = WorkFlowTaskExpediente::get();
            return response()->json($data);
        }
    }

    /* **************************
    *
    *   DESTROY WORK FLOW IN EXPEDIENTE
    *
    * ************************* */
    public function destroyWorkFlowTaskExpediente($company, $id)
    {
        if (Auth::user()->code_company == $company) {
            try {
                FlujoAsociadoExpediente::where('id_exp', $id)
                    ->where('code_company', $company)
                    ->delete();
                WorkFlowTaskExpediente::where('id_exp', $id)
                    ->where('code_company', $company)
                    ->delete();
                return response()->json([
                    'status' => true,
                    'code_comany' => $company,
                    'id_exp' => $id,
                    'msg' => "El Flujo de Trabajo se ha desactivado exitosamente en este proceso."
                ], 200);
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'code_comany' => $company,
                    'id_exp' => $id,
                    'msg' => "Error al eliminar Flujo de Trabajo."
                ], 404);
            }
        } else {
            return response()->json([
                'status' => false,
                'code_comany' => $company,
                'id_exp' => $id,
                'msg' => "Flujo de Trabajo no encontrado."
            ], 404);
        }
    }

    public function destroyWorkFlowTaskSuprema($company, $id)
    {
        if (Auth::user()->code_company == $company) {
            try {
                FlujoAsociadoSuprema::where('id_exp', $id)
                    ->where('code_company', $company)
                    ->delete();
                WorkFlowTaskSuprema::where('id_exp', $id)
                    ->where('code_company', $company)
                    ->delete();
                return response()->json([
                    'status' => true,
                    'code_comany' => $company,
                    'id_exp' => $id,
                    'msg' => "El Flujo de Trabajo se ha desactivado exitosamente en este proceso."
                ], 200);
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'code_comany' => $company,
                    'id_exp' => $id,
                    'msg' => "Error al eliminar Flujo de Trabajo."
                ], 404);
            }
        } else {
            return response()->json([
                'status' => false,
                'code_comany' => $company,
                'id_exp' => $id,
                'msg' => "Flujo de Trabajo no encontrado."
            ], 404);
        }
    }

    public function destroyWorkFlowTaskIndecopi($company, $id)
    {
        if (Auth::user()->code_company == $company) {
            try {
                FlujoAsociadoIndecopi::where('id_exp', $id)
                    ->where('code_company', $company)
                    ->delete();
                WorkFlowTaskIndecopi::where('id_exp', $id)
                    ->where('code_company', $company)
                    ->delete();
                return response()->json([
                    'status' => true,
                    'code_comany' => $company,
                    'id_exp' => $id,
                    'msg' => "El Flujo de Trabajo se ha desactivado exitosamente en este proceso."
                ], 200);
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'code_comany' => $company,
                    'id_exp' => $id,
                    'msg' => "Error al eliminar Flujo de Trabajo."
                ], 404);
            }
        } else {
            return response()->json([
                'status' => false,
                'code_comany' => $company,
                'id_exp' => $id,
                'msg' => "Flujo de Trabajo no encontrado."
            ], 404);
        }
    }

    public function destroyWorkFlowTaskSinoe($company, $id)
    {
        if (Auth::user()->code_company == $company) {
            try {
                FlujoAsociadoExpedienteSinoe::where('id_exp', $id)
                    ->where('code_company', $company)
                    ->delete();
                WorkFlowTaskExpedienteSinoe::where('id_exp', $id)
                    ->where('code_company', $company)
                    ->delete();
                return response()->json([
                    'status' => true,
                    'code_comany' => $company,
                    'id_exp' => $id,
                    'msg' => "El Flujo de Trabajo se ha desactivado exitosamente en este proceso."
                ], 200);
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'code_comany' => $company,
                    'id_exp' => $id,
                    'msg' => "Error al eliminar Flujo de Trabajo."
                ], 404);
            }
        } else {
            return response()->json([
                'status' => false,
                'code_comany' => $company,
                'id_exp' => $id,
                'msg' => "Flujo de Trabajo no encontrado."
            ], 404);
        }
    }

    public function destroyWorkFlowTaskPenal($company, $id)
    {
        if (Auth::user()->code_company == $company) {
            try {
                FlujoAsociadoExpedienteSinoe::where('id_exp', $id)
                    ->where('code_company', $company)
                    ->delete();
                WorkFlowTaskExpedienteSinoe::where('id_exp', $id)
                    ->where('code_company', $company)
                    ->delete();
                return response()->json([
                    'status' => true,
                    'code_comany' => $company,
                    'id_exp' => $id,
                    'msg' => "El Flujo de Trabajo se ha desactivado exitosamente en este proceso."
                ], 200);
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'code_comany' => $company,
                    'id_exp' => $id,
                    'msg' => "Error al eliminar Flujo de Trabajo."
                ], 404);
            }
        } else {
            return response()->json([
                'status' => false,
                'code_comany' => $company,
                'id_exp' => $id,
                'msg' => "Flujo de Trabajo no encontrado."
            ], 404);
        }
    }


    /* **************************
    *
    *   ADD WORK FLOW IN INDECOPI
    *
    * ************************* */

    public function addWorkFlowTaskIndecopi()
    {
        $idExp = request()->input("idExp");
        $nameFlujo = request()->input("nameFlujo");
        $nameEtapaInicial = request()->input("nameEtapaInicial");
        $idFlujo = request()->input("idFlujo");
        $idEtapaInicial = request()->input("idEtapaInicial");

        if ($idExp && $idEtapaInicial && $idFlujo) {
            $dataTaskStage = WorkFlowsTask::where('id_workflow', $idFlujo)
                ->where('id_workflow_stage', $idEtapaInicial)
                ->get();

            $dataTimeNow = Carbon::now();
            $dataNewFlujoAsociado = [
                'estado' => 'activo',
                'id_exp' => $idExp,
                'id_workflow' => $idFlujo,
                'id_workflow_stage' => $idEtapaInicial,
                'date_time' => $dataTimeNow,
                'code_user' => Auth::user()->code_user,
                'code_company' => Auth::user()->code_company,
                'metadata' => null,
                'table_pertenece' => 'flujo',
            ];
            FlujoAsociadoIndecopi::insert($dataNewFlujoAsociado);

            // Ver si un usuario vinculado con el expediente
            // acepto el consetimiento en google calendar
            $dataUser = User::where('id', Auth::user()->id)->first();

            $userPartes = UserParte::where('entidad', 'indecopi')
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

            if (count($dataTaskStage) > 0) {
                foreach ($dataTaskStage as $item) {
                    $WorkFlowTaskExpediente = new WorkFlowTaskIndecopi();
                    $WorkFlowTaskExpediente->id_workflow = $item->id_workflow;
                    $WorkFlowTaskExpediente->id_workflow_stage = $item->id_workflow_stage;
                    $WorkFlowTaskExpediente->id_workflow_task = $item->id;
                    $WorkFlowTaskExpediente->id_exp = $idExp;
                    $WorkFlowTaskExpediente->nombre_etapa = $nameEtapaInicial;
                    $WorkFlowTaskExpediente->nombre_flujo = $nameFlujo;
                    $WorkFlowTaskExpediente->nombre = $item->nombre;
                    $WorkFlowTaskExpediente->descripcion = $item->descripcion;
                    $WorkFlowTaskExpediente->dias_duracion = $item->dias_duracion;
                    $WorkFlowTaskExpediente->dias_antes_venc = $item->dias_antes_venc;

                    $fechaDuracion = Carbon::now()->addDays($item->dias_duracion);
                    $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                    $fechaAlerta = Carbon::now()->addDays($item->dias_antes_venc);
                    $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                    $WorkFlowTaskExpediente->fecha_limite = $fechaFormateadaDuracion;
                    $WorkFlowTaskExpediente->fecha_alerta = $fechaFormateadaAlerta;
                    $WorkFlowTaskExpediente->fecha_finalizada = null;

                    $WorkFlowTaskExpediente->attached_files = $item->attached_files;
                    $WorkFlowTaskExpediente->estado = 'En progreso';
                    $WorkFlowTaskExpediente->prioridad = $item->prioridad;
                    $WorkFlowTaskExpediente->code_user = Auth::user()->code_user;
                    $WorkFlowTaskExpediente->code_company = Auth::user()->code_company;
                    $WorkFlowTaskExpediente->save();

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
                    //     $evento->setColorId(9);

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
                    //     $WorkFlowTaskExpediente->metadata = json_encode($metaData);
                    //     $WorkFlowTaskExpediente->save();
                    // }
                }
            }
            return response()->json($dataTaskStage);
        }
        return response()->json([]);
    }

    public function getWorkFlowsIndecopi()
    {
        $idExp = request()->input('idExp');
        if ($idExp) {
            $data = WorkFlowTaskIndecopi::get();
            return response()->json($data);
        }
    }

    /* **************************
    *
    *   ADD WORK FLOW IN CORTE SUPREMA
    *
    * ************************* */

    public function addWorkFlowTaskSuprema()
    {
        $idExp = request()->input("idExp");
        $nameFlujo = request()->input("nameFlujo");
        $nameEtapaInicial = request()->input("nameEtapaInicial");
        $idFlujo = request()->input("idFlujo");
        $idEtapaInicial = request()->input("idEtapaInicial");

        if ($idExp && $idEtapaInicial && $idFlujo) {
            $dataTaskStage = WorkFlowsTask::where('id_workflow', $idFlujo)
                ->where('id_workflow_stage', $idEtapaInicial)
                ->get();

            $dataTimeNow = Carbon::now();
            $dataNewFlujoAsociado = [
                'estado' => 'activo',
                'id_exp' => $idExp,
                'id_workflow' => $idFlujo,
                'id_workflow_stage' => $idEtapaInicial,
                'date_time' => $dataTimeNow,
                'code_user' => Auth::user()->code_user,
                'code_company' => Auth::user()->code_company,
                'metadata' => null,
                'table_pertenece' => 'flujo',
            ];
            FlujoAsociadoSuprema::insert($dataNewFlujoAsociado);

            // Ver si un usuario vinculado con el expediente
            // acepto el consetimiento en google calendar
            $dataUser = User::where('id', Auth::user()->id)->first();

            $userPartes = UserParte::where('entidad', 'suprema')
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

            if (count($dataTaskStage) > 0) {
                foreach ($dataTaskStage as $item) {
                    $WorkFlowTaskSuprema = new WorkFlowTaskSuprema();
                    $WorkFlowTaskSuprema->id_workflow = $item->id_workflow;
                    $WorkFlowTaskSuprema->id_workflow_stage = $item->id_workflow_stage;
                    $WorkFlowTaskSuprema->id_workflow_task = $item->id;
                    $WorkFlowTaskSuprema->id_exp = $idExp;
                    $WorkFlowTaskSuprema->nombre_etapa = $nameEtapaInicial;
                    $WorkFlowTaskSuprema->nombre_flujo = $nameFlujo;
                    $WorkFlowTaskSuprema->nombre = $item->nombre;
                    $WorkFlowTaskSuprema->descripcion = $item->descripcion;
                    $WorkFlowTaskSuprema->dias_duracion = $item->dias_duracion;
                    $WorkFlowTaskSuprema->dias_antes_venc = $item->dias_antes_venc;

                    $fechaDuracion = Carbon::now()->addDays($item->dias_duracion);
                    $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                    $fechaAlerta = Carbon::now()->addDays($item->dias_antes_venc);
                    $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                    $WorkFlowTaskSuprema->fecha_limite = $fechaFormateadaDuracion;
                    $WorkFlowTaskSuprema->fecha_alerta = $fechaFormateadaAlerta;
                    $WorkFlowTaskSuprema->fecha_finalizada = null;

                    $WorkFlowTaskSuprema->attached_files = $item->attached_files;
                    $WorkFlowTaskSuprema->estado = 'En progreso';
                    $WorkFlowTaskSuprema->prioridad = $item->prioridad;
                    $WorkFlowTaskSuprema->code_user = Auth::user()->code_user;
                    $WorkFlowTaskSuprema->code_company = Auth::user()->code_company;
                    $WorkFlowTaskSuprema->save();

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
                    //     $evento->setColorId(9);

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
                    //     $WorkFlowTaskSuprema->metadata = json_encode($metaData);
                    //     $WorkFlowTaskSuprema->save();
                    // }
                }
            }
            return response()->json($dataTaskStage);
        }
        return response()->json([]);
    }

    public function getWorkFlowsSuprema()
    {
        $idExp = request()->input('idExp');
        if ($idExp) {
            $data = WorkFlowTaskSuprema::get();
            return response()->json($data);
        }
    }

    /* **************************
    *
    *   ADD WORK FLOW IN SINOE
    *
    * ************************* */

    public function addWorkFlowTaskSinoe()
    {
        $idExp = request()->input("idExp");
        $nameFlujo = request()->input("nameFlujo");
        $nameEtapaInicial = request()->input("nameEtapaInicial");
        $idFlujo = request()->input("idFlujo");
        $idEtapaInicial = request()->input("idEtapaInicial");

        if ($idExp && $idEtapaInicial && $idFlujo) {
            $dataTaskStage = WorkFlowsTask::where('id_workflow', $idFlujo)
                ->where('id_workflow_stage', $idEtapaInicial)
                ->get();

            $dataTimeNow = Carbon::now();
            $dataNewFlujoAsociado = [
                'estado' => 'activo',
                'id_exp' => $idExp,
                'id_workflow' => $idFlujo,
                'id_workflow_stage' => $idEtapaInicial,
                'date_time' => $dataTimeNow,
                'code_user' => Auth::user()->code_user,
                'code_company' => Auth::user()->code_company,
                'metadata' => null,
                'table_pertenece' => 'flujo',
            ];
            FlujoAsociadoExpedienteSinoe::insert($dataNewFlujoAsociado);

            // Ver si un usuario vinculado con el expediente
            // acepto el consetimiento en google calendar
            $dataUser = User::where('id', Auth::user()->id)->first();

            $userPartes = UserParte::where('entidad', 'sinoe')
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

            if (count($dataTaskStage) > 0) {
                foreach ($dataTaskStage as $item) {
                    $WorkFlowTaskSinoe = new WorkFlowTaskExpedienteSinoe();
                    $WorkFlowTaskSinoe->id_workflow = $item->id_workflow;
                    $WorkFlowTaskSinoe->id_workflow_stage = $item->id_workflow_stage;
                    $WorkFlowTaskSinoe->id_workflow_task = $item->id;
                    $WorkFlowTaskSinoe->id_exp = $idExp;
                    $WorkFlowTaskSinoe->nombre_etapa = $nameEtapaInicial;
                    $WorkFlowTaskSinoe->nombre_flujo = $nameFlujo;
                    $WorkFlowTaskSinoe->nombre = $item->nombre;
                    $WorkFlowTaskSinoe->descripcion = $item->descripcion;
                    $WorkFlowTaskSinoe->dias_duracion = $item->dias_duracion;
                    $WorkFlowTaskSinoe->dias_antes_venc = $item->dias_antes_venc;

                    $fechaDuracion = Carbon::now()->addDays($item->dias_duracion);
                    $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                    $fechaAlerta = Carbon::now()->addDays($item->dias_antes_venc);
                    $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                    $WorkFlowTaskSinoe->fecha_limite = $fechaFormateadaDuracion;
                    $WorkFlowTaskSinoe->fecha_alerta = $fechaFormateadaAlerta;
                    $WorkFlowTaskSinoe->fecha_finalizada = null;

                    $WorkFlowTaskSinoe->attached_files = $item->attached_files;
                    $WorkFlowTaskSinoe->estado = 'En progreso';
                    $WorkFlowTaskSinoe->prioridad = $item->prioridad;
                    $WorkFlowTaskSinoe->code_user = Auth::user()->code_user;
                    $WorkFlowTaskSinoe->code_company = Auth::user()->code_company;
                    $WorkFlowTaskSinoe->save();

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
                    //     $evento->setColorId(9);

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
                    //     $WorkFlowTaskSinoe->metadata = json_encode($metaData);
                    //     $WorkFlowTaskSinoe->save();
                    // }
                }
            }
            return response()->json($dataTaskStage);
        }
        return response()->json([]);
    }

    public function getWorkFlowsSinoe()
    {
        $idExp = request()->input('idExp');
        if ($idExp) {
            $data = WorkFlowTaskExpedienteSinoe::get();
            return response()->json($data);
        }
    }

    // /* * **********************************
    // *
    // *   COUNT ALL WORKFLOW IN EXPEDIENTE
    // *
    // * ********************************** */

    // public function countAllWorkFlow() {
    //     $idExp = request()->input('idExp');
    //     $countAll = WorkFlowTaskExpediente::where('id_exp', $idExp)->count();
    //     $countCheck = WorkFlowTaskExpediente::where('id_exp', $idExp)->where('metadata', 'finalizada')->count();
    //     $countAllTask = TaskExpediente::where('id_exp', $idExp)->count();
    //     $countAllTaskCheck = TaskExpediente::where('id_exp', $idExp)->where('metadata', 'finalizada')->count();

    //     // TOTAL
    //     $sumAll = $countAll + $countAllTask;
    //     // TOTAL AVANZADO
    //     $sumAllCheck = $countCheck + $countAllTaskCheck;

    //     return response()->json(['sumAll' => $sumAll, 'sumAllCheck' => $sumAllCheck]);
    // }

    /* **************************
    *
    *   ADD WORK FLOW IN PROCESO PENAL
    *
    * ************************* */

    public function addWorkFlowTaskPenal()
    {
        $idExp = request()->input("idExp");
        $nameFlujo = request()->input("nameFlujo");
        $nameEtapaInicial = request()->input("nameEtapaInicial");
        $idFlujo = request()->input("idFlujo");
        $idEtapaInicial = request()->input("idEtapaInicial");

        if ($idExp && $idEtapaInicial && $idFlujo) {
            $dataTaskStage = WorkFlowsTask::where('id_workflow', $idFlujo)
                ->where('id_workflow_stage', $idEtapaInicial)
                ->get();

            $dataTimeNow = Carbon::now();
            $dataNewFlujoAsociado = [
                'estado' => 'activo',
                'id_exp' => $idExp,
                'id_workflow' => $idFlujo,
                'id_workflow_stage' => $idEtapaInicial,
                'date_time' => $dataTimeNow,
                'code_user' => Auth::user()->code_user,
                'code_company' => Auth::user()->code_company,
                'metadata' => null,
                'table_pertenece' => 'flujo',
            ];
            FlujoAsociadoExpedienteSinoe::insert($dataNewFlujoAsociado);

            // Ver si un usuario vinculado con el expediente
            // acepto el consetimiento en google calendar
            $dataUser = User::where('id', Auth::user()->id)->first();

            $userPartes = UserParte::where('entidad', 'sinoe')
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

            if (count($dataTaskStage) > 0) {
                foreach ($dataTaskStage as $item) {
                    $WorkFlowTaskSinoe = new WorkFlowTaskExpedienteSinoe();
                    $WorkFlowTaskSinoe->id_workflow = $item->id_workflow;
                    $WorkFlowTaskSinoe->id_workflow_stage = $item->id_workflow_stage;
                    $WorkFlowTaskSinoe->id_workflow_task = $item->id;
                    $WorkFlowTaskSinoe->id_exp = $idExp;
                    $WorkFlowTaskSinoe->nombre_etapa = $nameEtapaInicial;
                    $WorkFlowTaskSinoe->nombre_flujo = $nameFlujo;
                    $WorkFlowTaskSinoe->nombre = $item->nombre;
                    $WorkFlowTaskSinoe->descripcion = $item->descripcion;
                    $WorkFlowTaskSinoe->dias_duracion = $item->dias_duracion;
                    $WorkFlowTaskSinoe->dias_antes_venc = $item->dias_antes_venc;

                    $fechaDuracion = Carbon::now()->addDays($item->dias_duracion);
                    $fechaFormateadaDuracion = $fechaDuracion->format('Y-m-d');
                    $fechaAlerta = Carbon::now()->addDays($item->dias_antes_venc);
                    $fechaFormateadaAlerta = $fechaAlerta->format('Y-m-d');
                    $WorkFlowTaskSinoe->fecha_limite = $fechaFormateadaDuracion;
                    $WorkFlowTaskSinoe->fecha_alerta = $fechaFormateadaAlerta;
                    $WorkFlowTaskSinoe->fecha_finalizada = null;

                    $WorkFlowTaskSinoe->attached_files = $item->attached_files;
                    $WorkFlowTaskSinoe->estado = 'En progreso';
                    $WorkFlowTaskSinoe->prioridad = $item->prioridad;
                    $WorkFlowTaskSinoe->code_user = Auth::user()->code_user;
                    $WorkFlowTaskSinoe->code_company = Auth::user()->code_company;
                    $WorkFlowTaskSinoe->save();

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
                    //     $evento->setColorId(9);

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
                    //     $WorkFlowTaskSinoe->metadata = json_encode($metaData);
                    //     $WorkFlowTaskSinoe->save();
                    // }
                }
            }
            return response()->json($dataTaskStage);
        }
        return response()->json([]);
    }

    public function getWorkFlowsPenal()
    {
        $idExp = request()->input('idExp');
        if ($idExp) {
            $data = WorkFlowTaskExpedienteSinoe::get();
            return response()->json($data);
        }
    }
}
