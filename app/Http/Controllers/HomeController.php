<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\Expedientes;
use App\Models\ExpedienteSinoe;
use App\Models\CorteSuprema;
use App\Models\Indecopi;
use App\Models\EconomicExpenses;
use App\Models\EconomicExpensesIndecopi;
use App\Models\EconomicExpensesSuprema;
use App\Models\EconomicExpensesSinoe;
use App\Models\Suscripcion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    /**
     * Mostrar estado de sus procesos para el home [cej judicial, suprema, indecopi, sinoe]
     *
     * @OA\Get (
     *     path="/api/home",
     *     tags={"Home"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Información extraída con éxito"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="diasFaltantes",
     *                     type="integer",
     *                     example=306
     *                 ),
     *                 @OA\Property(
     *                     property="listExpedientes",
     *                     type="integer", example={2, 1, 1, 2}
     *                 ),
     *                 @OA\Property(
     *                     property="namesProcesos",
     *                     type="string", example={"CEJ Judicial","CEJ Suprema","Indecopi","Sinoe"}
     *                 ),
     *                 @OA\Property(
     *                     property="listExpedientesAbierto",
     *                     type="integer", example={2, 0, 0, 2}
     *                 ),
     *                 @OA\Property(
     *                     property="listExpedientesCerrado",
     *                     type="integer", example={0, 0, 0, 0}
     *                 ),
     *                 @OA\Property(
     *                     property="listExpedientesPendiente",
     *                     type="integer", example={0, 1, 1, 0}
     *                 ),
     *                 @OA\Property(
     *                     property="listSumaGastosMountSol",
     *                     type="integer", example={20623, 0, 0, 2500}
     *                 ),
     *                 @OA\Property(
     *                     property="listSumaComisionesMountSol",
     *                     type="integer", example={0, 0, 0, 0}
     *                 ),
     *                 @OA\Property(
     *                     property="listSumaRecaudacionMountSol",
     *                     type="integer", example={600, 0, 0, 0}
     *                 ),
     *                 @OA\Property(
     *                     property="listSumaGastosMountDolar",
     *                     type="integer", example={0, 0, 0, 0}
     *                 ),
     *                 @OA\Property(
     *                     property="listSumaComisionesMountDolar",
     *                     type="integer", example={1100, 0, 0, 0}
     *                 ),
     *                 @OA\Property(
     *                     property="listSumaRecaudacionMountDolar",
     *                     type="integer", example={1100, 0, 0, 0}
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     */





    public function index()
    {
        $dataUser = Auth::user();

        $totalExpediente = Expedientes::where('code_company', $dataUser->code_company)->count();
        $abiertoExpediente = Expedientes::where('code_company', $dataUser->code_company)->where('state', 'Abierto')->count();
        $cerradoExpediente = Expedientes::where('code_company', $dataUser->code_company)->where('state', 'Cerrado')->count();
        $pendienteExpediente = Expedientes::where('code_company', $dataUser->code_company)->where('state', 'Pendiente')->count();

        $totalExpedienteSuprema = CorteSuprema::where('code_company', $dataUser->code_company)->count();
        $abiertoExpedienteSuprema = CorteSuprema::where('code_company', $dataUser->code_company)->where('state', 'Abierto')->count();
        $cerradoExpedienteSuprema = CorteSuprema::where('code_company', $dataUser->code_company)->where('state', 'Cerrado')->count();
        $pendienteExpedienteSuprema = CorteSuprema::where('code_company', $dataUser->code_company)->where('state', 'Pendiente')->count();

        $totalExpedienteIndecopi = Indecopi::where('code_company', $dataUser->code_company)->count();
        $abiertoExpedienteIndecopi = Indecopi::where('code_company', $dataUser->code_company)->where('state', 'Abierto')->count();
        $cerradoExpedienteIndecopi = Indecopi::where('code_company', $dataUser->code_company)->where('state', 'Cerrado')->count();
        $pendienteExpedienteIndecopi = Indecopi::where('code_company', $dataUser->code_company)->where('state', 'Pendiente')->count();

        $totalExpedienteSinoe = ExpedienteSinoe::where('code_company', $dataUser->code_company)->count();
        $abiertoExpedienteSinoe = ExpedienteSinoe::where('code_company', $dataUser->code_company)->where('state', 'Abierto')->count();
        $cerradoExpedienteSinoe = ExpedienteSinoe::where('code_company', $dataUser->code_company)->where('state', 'Cerrado')->count();
        $pendienteExpedienteSinoe = ExpedienteSinoe::where('code_company', $dataUser->code_company)->where('state', 'Pendiente')->count();

        $economic = EconomicExpenses::join('expedientes', 'economic_expenses.id_exp', '=', 'expedientes.id')
            ->select(
                'economic_expenses.id',
                'economic_expenses.type',
                'economic_expenses.date_time',
                'economic_expenses.moneda',
                'economic_expenses.mount',
                'economic_expenses.titulo',
                'economic_expenses.descripcion',
                'economic_expenses.status',
                'economic_expenses.attached_files',
                'economic_expenses.metadata',
                'economic_expenses.code_user',
                'economic_expenses.code_company',
                'economic_expenses.id_exp',
                'economic_expenses.entidad',
            )
            ->orderBy('economic_expenses.id')
            ->where('economic_expenses.code_company', $dataUser->code_company)
            ->get();

        // Calcular las sumas
        // ? gastos
        $sumaGastosMountSol = $economic->where('moneda', 'Sol')->where('type', 'Gastos')->sum('mount');
        // $sumaGastosMountSolNoPagado = $economic->where('moneda', 'Sol')->where('type', 'Gastos')->where('status', 'No')->sum('mount');
        $sumaGastosMountDolar = $economic->where('moneda', 'Dólar')->where('type', 'Gastos')->sum('mount');
        // $sumaGastosMountDolarNoPagado = $economic->where('moneda', 'Dólar')->where('type', 'Gastos')->where('status', 'No')->sum('mount');
        // ? Ingresos
        $sumaRecaudacionesMountSol = $economic->where('moneda', 'Sol')->where('type', 'Recaudaciones')->sum('mount');
        $sumaRecaudacionesMountDolar = $economic->where('moneda', 'Dólar')->where('type', 'Recaudaciones')->sum('mount');
        // ? Comisiones
        $sumaComisionesMountSol = $economic->where('moneda', 'Sol')->where('type', 'Comisiones')->sum('mount');
        $sumaComisionesMountDolar = $economic->where('moneda', 'Dólar')->where('type', 'Comisiones')->sum('mount');

        $sumaMountTotalSol = $economic->where('moneda', 'Sol')->sum('mount');
        $sumaMountTotalDolar = $economic->where('moneda', 'Dólar')->sum('mount');


        $economicIndecopi = EconomicExpensesIndecopi::join('indecopis', 'economic_expenses_indecopis.id_indecopi', '=', 'indecopis.id')
            ->select(
                'economic_expenses_indecopis.id',
                'economic_expenses_indecopis.type',
                'economic_expenses_indecopis.date_time',
                'economic_expenses_indecopis.moneda',
                'economic_expenses_indecopis.mount',
                'economic_expenses_indecopis.titulo',
                'economic_expenses_indecopis.descripcion',
                'economic_expenses_indecopis.status',
                'economic_expenses_indecopis.attached_files',
                'economic_expenses_indecopis.metadata',
                'economic_expenses_indecopis.code_user',
                'economic_expenses_indecopis.code_company',
                'economic_expenses_indecopis.id_indecopi',
                'economic_expenses_indecopis.entidad',
            )
            ->orderBy('economic_expenses_indecopis.id')
            ->where('economic_expenses_indecopis.code_company', $dataUser->code_company)
            ->get();

        // Calcular las sumas
        // ? gastos
        $indecopiSumaGastosMountSol = $economicIndecopi->where('moneda', 'Sol')->where('type', 'Gastos')->sum('mount');
        $indecopiSumaGastosMountDolar = $economicIndecopi->where('moneda', 'Dólar')->where('type', 'Gastos')->sum('mount');
        // ? Ingresos
        $indecopiSumaRecaudacionesMountSol = $economicIndecopi->where('moneda', 'Sol')->where('type', 'Recaudaciones')->sum('mount');
        $indecopiSumaRecaudacionesMountDolar = $economicIndecopi->where('moneda', 'Dólar')->where('type', 'Recaudaciones')->sum('mount');
        // ? Comisiones
        $indecopiSumaComisionesMountSol = $economicIndecopi->where('moneda', 'Sol')->where('type', 'Comisiones')->sum('mount');
        $indecopiSumaComisionesMountDolar = $economicIndecopi->where('moneda', 'Dólar')->where('type', 'Comisiones')->sum('mount');

        $indecopiSumaMountTotalSol = $economicIndecopi->where('moneda', 'Sol')->sum('mount');
        $indecopiSumaMountTotalDolar = $economicIndecopi->where('moneda', 'Dólar')->sum('mount');


        $economicSuprema = EconomicExpensesSuprema::join('corte_supremas', 'economic_expenses_supremas.id_exp', '=', 'corte_supremas.id')
            ->select(
                'economic_expenses_supremas.id',
                'economic_expenses_supremas.type',
                'economic_expenses_supremas.date_time',
                'economic_expenses_supremas.moneda',
                'economic_expenses_supremas.mount',
                'economic_expenses_supremas.titulo',
                'economic_expenses_supremas.descripcion',
                'economic_expenses_supremas.status',
                'economic_expenses_supremas.attached_files',
                'economic_expenses_supremas.metadata',
                'economic_expenses_supremas.code_user',
                'economic_expenses_supremas.code_company',
                'economic_expenses_supremas.id_exp',
                'economic_expenses_supremas.entidad',
            )
            ->orderBy('economic_expenses_supremas.id')
            ->where('economic_expenses_supremas.code_company', $dataUser->code_company)
            ->get();

        // Calcular las sumas
        // ? gastos
        $supremaSumaGastosMountSol = $economicSuprema->where('moneda', 'Sol')->where('type', 'Gastos')->sum('mount');
        $supremaSumaGastosMountDolar = $economicSuprema->where('moneda', 'Dólar')->where('type', 'Gastos')->sum('mount');
        // ? Ingresos
        $supremaSumaRecaudacionesMountSol = $economicSuprema->where('moneda', 'Sol')->where('type', 'Recaudaciones')->sum('mount');
        $supremaSumaRecaudacionesMountDolar = $economicSuprema->where('moneda', 'Dólar')->where('type', 'Recaudaciones')->sum('mount');
        // ? Comisiones
        $supremaSumaComisionesMountSol = $economicSuprema->where('moneda', 'Sol')->where('type', 'Comisiones')->sum('mount');
        $supremaSumaComisionesMountDolar = $economicSuprema->where('moneda', 'Dólar')->where('type', 'Comisiones')->sum('mount');

        $supremaSumaMountTotalSol = $economicSuprema->where('moneda', 'Sol')->sum('mount');
        $supremaSumaMountTotalDolar = $economicSuprema->where('moneda', 'Dólar')->sum('mount');


        $economicSinoe = EconomicExpensesSinoe::join('expediente_sinoes', 'economic_expenses_sinoes.id_exp', '=', 'expediente_sinoes.id')
            ->select(
                'economic_expenses_sinoes.id',
                'economic_expenses_sinoes.type',
                'economic_expenses_sinoes.date_time',
                'economic_expenses_sinoes.moneda',
                'economic_expenses_sinoes.mount',
                'economic_expenses_sinoes.titulo',
                'economic_expenses_sinoes.descripcion',
                'economic_expenses_sinoes.status',
                'economic_expenses_sinoes.attached_files',
                'economic_expenses_sinoes.metadata',
                'economic_expenses_sinoes.code_user',
                'economic_expenses_sinoes.code_company',
                'economic_expenses_sinoes.id_exp',
                'economic_expenses_sinoes.entidad',
            )
            ->orderBy('economic_expenses_sinoes.id')
            ->where('economic_expenses_sinoes.code_company', $dataUser->code_company)
            ->get();

        // Calcular las sumas
        // ? gastos
        $sinoeSumaGastosMountSol = $economicSinoe->where('moneda', 'Sol')->where('type', 'Gastos')->sum('mount');
        $sinoeSumaGastosMountDolar = $economicSinoe->where('moneda', 'Dólar')->where('type', 'Gastos')->sum('mount');
        // ? Ingresos
        $sinoeSumaRecaudacionesMountSol = $economicSinoe->where('moneda', 'Sol')->where('type', 'Recaudaciones')->sum('mount');
        $sinoeSumaRecaudacionesMountDolar = $economicSinoe->where('moneda', 'Dólar')->where('type', 'Recaudaciones')->sum('mount');
        // ? Comisiones
        $sinoeSumaComisionesMountSol = $economicSinoe->where('moneda', 'Sol')->where('type', 'Comisiones')->sum('mount');
        $sinoeSumaComisionesMountDolar = $economicSinoe->where('moneda', 'Dólar')->where('type', 'Comisiones')->sum('mount');

        $sinoeSumaMountTotalSol = $economicSinoe->where('moneda', 'Sol')->sum('mount');
        $sinoeSumaMountTotalDolar = $economicSinoe->where('moneda', 'Dólar')->sum('mount');



        $dataCompany = Company::where("code_company", $dataUser->code_company)->first();
        $dataSuscripcion = Suscripcion::where('id', $dataCompany->id_suscripcion)->first();
        // Verificando si la cuenta esta en demo

        $diasFaltantes = null;
        if ($dataSuscripcion) {
            // Verificando si la cuenta está en demo o tiene suscripción personalizada
            if ($dataSuscripcion->id == 1 || $dataSuscripcion->id > 6) {
                $fechaCreada = ($dataSuscripcion->id == 1)
                    ? Carbon::parse($dataCompany->created_at)->format('Y-m-d')
                    : Carbon::createFromFormat('Y-m-d', $dataSuscripcion->current_period_start)->format('Y-m-d');
                $fechaFin = ($dataSuscripcion->id == 1) ?
                    Carbon::parse($fechaCreada)->addDays($dataSuscripcion->dias_suscripcion)->format('Y-m-d') :
                    Carbon::parse($dataSuscripcion->current_period_end)->format('Y-m-d');

                $fechaActual = Carbon::now()->format('Y-m-d');
                $diff = strtotime($fechaFin) - strtotime($fechaActual); // Calcula la diferencia en segundos
                $diasFaltantes = intval(round($diff / (60 * 60 * 24)));
            }
        }

        $listExpedientes = [$totalExpediente, $totalExpedienteSuprema, $totalExpedienteIndecopi, $totalExpedienteSinoe];
        $listExpedientesAbierto = [$abiertoExpediente, $abiertoExpedienteSuprema, $abiertoExpedienteIndecopi, $abiertoExpedienteSinoe];
        $listExpedientesPendiente = [$pendienteExpediente, $pendienteExpedienteSuprema, $pendienteExpedienteIndecopi, $pendienteExpedienteSinoe];
        $listExpedientesCerrado = [$cerradoExpediente, $cerradoExpedienteSuprema, $cerradoExpedienteIndecopi, $cerradoExpedienteSinoe];

        $listSumaGastosMountSol = [$sumaGastosMountSol, $supremaSumaGastosMountSol, $indecopiSumaGastosMountSol, $sinoeSumaGastosMountSol];
        $listSumaComisionesMountSol = [$sumaComisionesMountSol, $supremaSumaComisionesMountSol, $indecopiSumaComisionesMountSol, $sinoeSumaComisionesMountSol];
        $listSumaRecaudacionMountSol = [$sumaRecaudacionesMountSol, $supremaSumaRecaudacionesMountSol, $indecopiSumaRecaudacionesMountSol, $sinoeSumaRecaudacionesMountSol];

        $listSumaGastosMountDolar = [$sumaGastosMountDolar, $supremaSumaGastosMountDolar, $indecopiSumaGastosMountDolar, $sinoeSumaGastosMountDolar];
        $listSumaComisionesMountDolar = [$sumaComisionesMountDolar, $supremaSumaComisionesMountDolar, $indecopiSumaComisionesMountDolar, $sinoeSumaComisionesMountDolar];
        $listSumaRecaudacionMountDolar = [$sumaRecaudacionesMountDolar, $supremaSumaRecaudacionesMountDolar, $indecopiSumaRecaudacionesMountDolar, $sinoeSumaRecaudacionesMountDolar];
        $namesProcesos = [
            "CEJ Judicial",
            "CEJ Suprema",
            "Indecopi",
            "Sinoe",
        ];

        $responseData = [
            'diasFaltantes' => $diasFaltantes,
            'namesProcesos' => $namesProcesos,

            'listExpedientes' => $listExpedientes,
            'listExpedientesAbierto' => $listExpedientesAbierto,
            'listExpedientesCerrado' => $listExpedientesCerrado,
            'listExpedientesPendiente' => $listExpedientesPendiente,

            'listSumaGastosMountSol' => $listSumaGastosMountSol,
            'listSumaComisionesMountSol' => $listSumaComisionesMountSol,
            'listSumaRecaudacionMountSol' => $listSumaRecaudacionMountSol,

            'listSumaGastosMountDolar' => $listSumaGastosMountDolar,
            'listSumaComisionesMountDolar' => $listSumaComisionesMountDolar,
            'listSumaRecaudacionMountDolar' => $listSumaRecaudacionMountDolar,
        ];
        return response()->json([
            "status" => true,
            "message" => "Información extraída con éxito",
            "data" => $responseData
        ]);
    }
}
