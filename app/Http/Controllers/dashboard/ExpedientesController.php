<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\AccionesIndecopi;
use App\Models\Alert;
use App\Models\AnexoNotificationSinoe;
use App\Models\Cliente;
use App\Models\CommentMovement;
use App\Models\CommentTaskFlujoJudicial;
use App\Models\CommentTaskJudicial;
use App\Models\Company;
use App\Models\CorteSuprema;
use App\Models\Credenciales;
use App\Models\DocumentosPresentadosSinoe;
use App\Models\EconomicExpenses;
use App\Models\Entidad;
use App\Models\EventSuggestion;
use App\Models\Expedientes;
use App\Models\ExpedienteSinoe;
use App\Models\FiltroExp;
use App\Models\FlujoAsociadoExpediente;
use App\Models\FollowUp;
use App\Models\HistorialDocumentosSinoe;
use App\Models\Indecopi;
use App\Models\NotificacionSeguimiento;
use App\Models\NotificationSinoe;
use App\Models\SeguimientoSuprema;
use App\Models\SuggestionChatJudicial;
use App\Models\Suscripcion;
use App\Models\TaskExpediente;
use App\Models\TaskIndecopi;
use App\Models\TempDocumentPresentado;
use App\Models\User;
use App\Models\UserParte;
use App\Models\VistaCausaSuprema;
use App\Models\WorkFlows;
use App\Models\WorkFlowsStage;
use App\Models\WorkFlowsTask;
use App\Models\WorkFlowTaskExpediente;
use App\Models\WorkFlowTransitions;
use Carbon\Carbon;
use DateTime;
use Facade\FlareClient\Http\Response;
// use App\Models\Observation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\WebM;


class ExpedientesController extends Controller
{
    protected $invalidIdResponse;

    public function __construct()
    {
        $this->invalidIdResponse = response()->json(['result' => 'sin resultado'], 400);
    }

    /**
     * Listado de todos los expedientes judiciales
     * @OA\Get (
     *     path="/api/judiciales",
     *     tags={"Expedientes"},
     *     @OA\Response(
     *         response=200,
     *         description="Ok",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 type="array",
     *                 property="rows",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="id",
     *                         type="number",
     *                         example="1"
     *                     ),
     *                     @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="Aderson Felix"
     *                     ),
     *                     @OA\Property(
     *                         property="slug",
     *                         type="string",
     *                         example="Jara Lazaro"
     *                     ),
     *                     @OA\Property(
     *                         property="description",
     *                         type="string",
     *                         example="Jara Lazaro"
     *                     ),
     *                     @OA\Property(
     *                         property="price",
     *                         type="decimal",
     *                         example="Jara Lazaro"
     *                     ),
     *                     @OA\Property(
     *                         property="created_at",
     *                         type="string",
     *                         example="2023-02-23T00:09:16.000000Z"
     *                     ),
     *                     @OA\Property(
     *                         property="updated_at",
     *                         type="string",
     *                         example="2023-02-23T12:33:45.000000Z"
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     * */

    public function mostrarExpedientes()
    {
        $expedientes = Expedientes::join('clientes', 'expedientes.id_client', '=', 'clientes.id')
            ->select(
                'expedientes.id',
                'expedientes.n_expediente',
                'expedientes.materia',
                'expedientes.proceso',
                'expedientes.lawyer_responsible',
                'expedientes.estado',
                'expedientes.sumilla',
                'expedientes.date_initial',
                'expedientes.update_date',
                'clientes.name',
                'clientes.last_name',
                'clientes.name_company',
                'clientes.type_contact',
                'clientes.ruc',
                'clientes.email',
                'clientes.phone',
                'follow_ups.fecha_ingreso',
                'follow_ups.fecha_resolucion',
                'follow_ups.u_date',
            )
            ->leftJoin('follow_ups', function ($join) {
                $join->on('expedientes.id', '=', 'follow_ups.id_exp')
                    ->where('follow_ups.id', '=', function ($query) {
                        $query->select(DB::raw('MAX(id)'))
                            ->from('follow_ups')
                            ->whereColumn('id_exp', 'expedientes.id');
                    });
            })
            ->orderBy('expedientes.id', 'desc')
            ->where('expedientes.code_company', Auth::user()->code_company)
            ->get();

        $dataCompany = Company::where('code_company', Auth::user()->code_company)->first();
        $dataSuscripcion = Suscripcion::where('id', $dataCompany->id_suscripcion)->first();
        $totalExpedientes = Expedientes::where('code_company', Auth::user()->code_company)->count();
        $limitExpedientes = $dataSuscripcion->limit_judicial;


        return view('dashboard.sistema_expedientes.expedientesRegistroExpedientes', compact('expedientes', 'totalExpedientes', 'limitExpedientes'));
    }

    public function query(Request $request)
    {
        $input = $request->all();

        $data = Cliente::select("name")
            ->where("name", "LIKE", "%{$input['query']}%")
            ->get();

        return response()->json($data);
    }

    public function addExpediente(Request $request)
    {
        $datosExpediente = request()->all();

        // dd($request);

        $entidad  = $datosExpediente["entidad-exp"];
        $rptaAbo = $datosExpediente["rpa-abo"];
        $entidadM = $datosExpediente["entidad-exp-m"];

        // id del usuario que registra el expediente
        $idUser = Auth::user()->id;
        $dataUser = User::where('id', $idUser)->get()->first();
        $state  = $datosExpediente["m-state"];
        $infoDate  = $datosExpediente["m-info-date"];

        $dataCompany = Company::where('code_company', Auth::user()->code_company)->first();
        $dataSuscripcion = Suscripcion::where('id', $dataCompany->id_suscripcion)->first();

        if ($rptaAbo == 'si') {
            // VERIFICAR entidad con la respiesta si con el nombre de entidad
            if ($entidad == "CEJ por Código de Expediente" || $entidad == "CEJ por Número y Juzgado") {

                $totalExpediente = Expedientes::where('code_company', Auth::user()->code_company)->count();
                $limitExpediente = $dataSuscripcion->limit_judicial;

                // Verificar si ya alcanso el maximo
                if ($totalExpediente >= $limitExpediente && $limitExpediente !== null) {
                    return redirect()->route('sistema_expedientes.expedientesRegistroClientes')->with('error', "Error al crear proceso (Maximo alcanzado).");
                }

                // Id del cliente
                $idClient = $datosExpediente["id-client"];
                $numExp = $datosExpediente["m-n-exp"];
                $oJuris  = $datosExpediente["m-o-juris"];
                $disJudi  = $datosExpediente["m-dis-judi"];
                $juez  = $datosExpediente["m-juez"];
                $ubi  = $datosExpediente["m-ubi"];
                $eProcesal  = $datosExpediente["m-e-procesal"];
                $sumilla  = $datosExpediente["m-sumilla"];
                $proceso  = $datosExpediente["m-proceso"];
                $especialidad  = $datosExpediente["m-especialidad"];
                $obs  = $datosExpediente["m-obs"];
                $estado  = $datosExpediente["m-estado"];
                $materia  = $datosExpediente["m-case"];
                $lawyerResponsible  = $datosExpediente["m-lawyer-responsible"];
                $dateInitial  = $datosExpediente["m-date-initial"];
                $dateConclusion  = $datosExpediente["m-date-conclusion"];
                $motivoConclusion  = $datosExpediente["m-motivo-conclusion"];
                $idEntidad  = $datosExpediente["id-entidad"];

                $timestamp = strtotime($dateConclusion);
                if ($timestamp !== false) {
                    $dateConclusion = date('Y-m-d', $timestamp);
                } else {
                    $dateConclusion = null;
                }

                $partesSeparadas = [];
                // ? ABOGADO VIRTUAL (AUTOMATIZADO)
                if ($rptaAbo == "si") {
                    $partesProcesales = json_decode($datosExpediente["partesProcesales"], true);
                    $partesSeparadas = [];
                    // Saltar el primer elemento que contiene los encabezados
                    array_shift($partesProcesales);

                    foreach ($partesProcesales as $parte) {
                        if (count($parte) === 5) {
                            $partesSeparadas[] = [$parte[0], $parte[1], $parte[2] . " " . $parte[3] . ", " . $parte[4]];
                        } else {
                            $partesSeparadas[] = [$parte[0], $parte[1], $parte[2]];
                        }
                    }
                }

                // if ($rptaAbo == "no"){
                //     $partes = request()->input('parte');
                //     $tipoPersonas = request()->input('tipoPersona');
                //     $nombresRazonSocial = request()->input('nombresRazonSocial');


                //     foreach ($partes as $index => $parte) {
                //         if (isset($tipoPersonas[$index]) && isset($nombresRazonSocial[$index])) {
                //             $partesSeparadas[] = [$parte, $tipoPersonas[$index], $nombresRazonSocial[$index]];
                //         }
                //     }
                // }

                // DATA EXPEDIENTE
                $newData = [
                    'n_expediente' => $numExp,
                    'o_jurisdicional' => $oJuris,
                    'd_judicial' => $disJudi,
                    'juez' => $juez,
                    'ubicacion' => $ubi,
                    'e_procesal' => $eProcesal,
                    'sumilla' => $sumilla,
                    'proceso' => $proceso,
                    'especialidad' => $especialidad,
                    'observacion' => $obs,
                    'estado' => $estado,
                    'materia' => $materia,
                    'demanding' => null,
                    'defendant' => null,
                    'lawyer_responsible' => $lawyerResponsible,
                    'update_date' => null,
                    'state' => $state,
                    'date_state' => $infoDate,
                    'date_initial' => $dateInitial,
                    'date_conclusion' => $dateConclusion,
                    'motivo_conclusion' => $motivoConclusion,
                    'partes_procesales' => json_encode($partesSeparadas),
                    'abogado_virtual' => $rptaAbo,
                    'id_client' => $idClient,
                    'entidad' => $entidad,
                    'code_user' => $dataUser->code_user,
                    'code_company' => $dataUser->code_company,
                ];

                // Expedientes::insert($newData);
                $dataId = DB::table('expedientes')->insertGetId($newData);

                $seguimientoExp = $datosExpediente["seguimientoExp"];
                $seguimientoAll = json_decode($seguimientoExp, true);
                $reversedData = array_reverse($seguimientoAll, true);
                $numObs = 0;
                $notiIds = [];
                foreach ($reversedData as $key => $entry) {
                    $obs = new FollowUp();

                    // Asignar valores a las columnas respectivas
                    $obs->n_seguimiento = $numObs++;
                    $fechaIngreso = isset($entry['Fecha de Ingreso']) ? DateTime::createFromFormat('d/m/Y H:i', $entry['Fecha de Ingreso']) : null;
                    $fechaResolucion = isset($entry['Fecha de Resolución']) ? DateTime::createFromFormat('d/m/Y', $entry['Fecha de Resolución']) : null;
                    $proveido = isset($entry['Proveido']) ? DateTime::createFromFormat('d/m/Y', $entry['Proveido']) : null;
                    $obs->fecha_ingreso = $fechaIngreso ? $fechaIngreso->format('Y-m-d H:i:s') : null;
                    $obs->fecha_resolucion = $fechaResolucion ? $fechaResolucion->format('Y-m-d') : null;
                    $obs->resolucion = isset($entry['Resolución']) ? $entry['Resolución'] : null;
                    $obs->type_notificacion = isset($entry['Tipo de Notificación']) ? $entry['Tipo de Notificación'] : null;
                    $obs->acto = isset($entry['Acto']) && !empty($entry['Acto']) ? $entry['Acto'] : null;
                    $obs->folios = isset($entry['Folios']) && !empty($entry['Folios']) ? $entry['Folios'] : null;
                    $obs->fojas = isset($entry['Fojas']) && !empty($entry['Fojas']) ? $entry['Fojas'] : null;
                    $obs->proveido = $proveido ? $proveido->format(('Y-m-d')) : null;
                    $obs->obs_sumilla = isset($entry['Sumilla']) ? $entry['Sumilla'] : null;
                    $obs->descripcion = isset($entry['Descripción de Usuario']) ? $entry['Descripción de Usuario'] : null;
                    $obs->file = isset($entry['Descarga resolucion']) ? $entry['Descarga resolucion'] : null;
                    $obs->id_exp = $dataId;
                    $obs->abog_virtual = $rptaAbo;
                    $obs->code_company = $dataUser->code_company;
                    $obs->save();


                    // Si hay notificaciones en el entry, también puedes iterar a través de ellas
                    if (isset($entry['notifi']) && is_array($entry['notifi'])) {
                        $notiIds = [];
                        foreach ($entry['notifi'] as $notificacionKey => $notificacion) {
                            $notificacionModel = new NotificacionSeguimiento();
                            // Asignar valores a la notificación y guárdala
                            $notificacionModel->name = $notificacionKey;
                            $notificacionModel->destinatario = isset($notificacion['Destinatario']) ? $notificacion['Destinatario'] : null;
                            $fechaEnvio = isset($notificacion['Fecha de envio']) ? DateTime::createFromFormat('d/m/Y H:i', $notificacion['Fecha de envio']) : null;
                            $notificacionModel->fecha_envio = $fechaEnvio ? $fechaEnvio->format('Y-m-d H:i:s') : null;
                            $notificacionModel->anexos = isset($notificacion['Anexo(s)']) ? $notificacion['Anexo(s)'] : null;
                            $notificacionModel->forma_entrega = isset($notificacion['Forma de entrega']) ? $notificacion['Forma de entrega'] : null;
                            $notificacionModel->id_exp = $dataId;
                            $notificacionModel->abog_virtual = $rptaAbo;
                            $notificacionModel->save();

                            $notiIds[] = $notificacionModel->id;
                        }
                        // Asignar el ID de notificación a la propiedad $obs->noti después del bucle de notificaciones
                        if ($notiIds != []) {
                            $obs->noti = json_encode($notiIds);
                            $obs->save(); // Actualizar el seguimiento con el ID de notificación
                        }
                    }
                }

                UserParte::insert([
                    'nombres' => $dataUser->name,
                    'apellidos' => $dataUser->lastname,
                    'email' => $dataUser->email,
                    'categoria' => null,
                    'rol' => $dataUser->type_user,
                    'id_exp' => $dataId,
                    'code_company' => $dataUser->code_company,
                    'code_user'    => $dataUser->code_user,
                    'entidad' => 'judicial',
                    'metadata' => 'si',
                ]);

                // $currentDateTime = Carbon::now();
                // $mysqlDateTime = $currentDateTime->format('Y-m-d H:i:s');
                // Alert::insert([
                //     'date_time'=> $mysqlDateTime,
                //     'message' =>"Se registro un nuevo expediente  N° " . $numExp,
                //     'alert' => 'success',
                //     'type' => 'expediente',
                //     'id_exp' => $dataId,
                // ]);
                return redirect()->route('sistema_expedientes.viewSeguimiento', ["Exp" => $dataId])->with('success', '¡Expediente registrado correctamente!');
            } else if ($entidad == "Indecopi - Búsqueda por número de Reclamo/Buen Oficio" || $entidad == "Indecopi - Búsqueda por Reclamante/Ciudadano" || $entidad == "Indecopi - Búsqueda por Reclamado/Proveedor") {

                $totalExpediente = Indecopi::where('code_company', Auth::user()->code_company)->count();
                $limitExpediente = $dataSuscripcion->limit_indecopi;

                // Verificar si ya alcanso el maximo
                if ($totalExpediente >= $limitExpediente && $limitExpediente !== null) {
                    return redirect()->route('sistema_expedientes.expedientesRegistroClientes')->with('error', "Error al crear proceso (Maximo alcanzado).");
                }

                $idClient = $datosExpediente["id-client"];
                $entidadExp = $datosExpediente["entidad-exp"];
                $iTipo = $datosExpediente["a-indecopi-tipo"];
                // $iFechaDesde = $datosExpediente["a-indecopi-fecha-desde"];
                // $iFechaHasta = $datosExpediente["a-indecopi-fecha-hasta"];
                // $iTypeDoc = $datosExpediente["a-indecopi-type-doc"];
                // $iNombreRazon = $datosExpediente["a-indecopi-nombre-razon"];
                // $iApellidoP = $datosExpediente["a-indecopi-apellido-p"];
                // $iApellidoM = $datosExpediente["a-indecopi-apellido-m"];
                $iNTipo = $datosExpediente["i-n-tipo"];
                // $iTipo = $datosExpediente["i-tipo"];
                $iEntidad = $datosExpediente["i-entidad"];
                $iOficina = $datosExpediente["i-oficina"];
                $iResponsable = $datosExpediente["i-responsable"];
                $iViaPresentacion = $datosExpediente["i-via-presentacion"];
                $iFechaInicio = $datosExpediente["i-fecha-inicio"];
                $iEstado = $datosExpediente["i-estado"];
                $iFecha = $datosExpediente["i-fecha"];
                $iFormaConclusion = $datosExpediente["i-forma-conclusion"];
                $iPartesProcesales = $datosExpediente["partesProcesales"];
                $iSeguimientos = $datosExpediente["seguimientoExp"];
                $iDetalle = $datosExpediente["h-indecopi-detalle"];
                // $state = $datosExpediente["m-state"];
                // $infoState = $datosExpediente["m-info-date"];

                // Crear un array para almacenar los datos de "data1" y "data2"
                $data1Values = [];
                $data2Values = [];

                if ($rptaAbo == 'si') {

                    // Decodificar la cadena JSON en un array asociativo
                    $dataArray = json_decode($iPartesProcesales, true);


                    // Iterar sobre las secciones "data1" y "data2"
                    foreach (['data1', 'data2'] as $section) {
                        if (isset($dataArray[$section])) {
                            $sectionArray = json_decode($dataArray[$section], true);
                            foreach ($sectionArray as $subkey => $subarray) {
                                $values = array_values($subarray);
                                // Agregar los valores al array correspondiente (data1Values o data2Values)
                                if ($section === 'data1') {
                                    $data1Values[] = $values;
                                } elseif ($section === 'data2') {
                                    $data2Values[] = $values;
                                }
                            }
                        }
                    }
                }
                // if ($rptaAbo == 'no'){
                //     // Ingesó los datos del expediente manualmente
                //     $iTipo = $datosExpediente["m-indecopi-tipo"];

                //     // Definir el número de registros en los campos (pueden ser diferentes)
                //     $numRegistros = count($datosExpediente['ip-condicion']);

                //     // Iterar a través de los registros
                //     for ($i = 0; $i < $numRegistros; $i++) {
                //         // Crear un subarray para cada registro
                //         $registro = [
                //             $datosExpediente['ip-condicion'][$i],
                //             $datosExpediente['ip-tipo-doc'][$i],
                //             $datosExpediente['ip-n-doc'][$i],
                //             $datosExpediente['ip-nombre'][$i],
                //             $datosExpediente['ip-pais-etc'][$i],
                //         ];

                //         // Agregar el subarray al array de datos finales
                //         $data1Values[] = $registro;
                //     }

                //     $numRegistros2 = count($datosExpediente['ip-condicion2']);

                //     // Iterar a través de los registros
                //     for ($i = 0; $i < $numRegistros2; $i++) {
                //         // Crear un subarray para cada registro
                //         $registro2 = [
                //             $datosExpediente['ip-condicion2'][$i],
                //             $datosExpediente['ip-tipo-doc2'][$i],
                //             $datosExpediente['ip-n-doc2'][$i],
                //             $datosExpediente['ip-nombre2'][$i],
                //             $datosExpediente['ip-direccion2'][$i],
                //             $datosExpediente['ip-pais-etc2'][$i],
                //         ];

                //         // Agregar el subarray al array de datos finales
                //         $data2Values[] = $registro2;
                //     }
                // }

                $newDataIndecopi = [
                    "tipo" => $iTipo,
                    "numero" => $iNTipo,
                    "oficina" => $iOficina,
                    "responsable" => $iResponsable,
                    "via_presentacion" => $iViaPresentacion,
                    "fecha_inicio" => $iFechaInicio,
                    "estado" => $iEstado,
                    "fecha" => $iFecha,
                    "forma_conclusion" => $iFormaConclusion,
                    "partes_procesales1" => json_encode($data1Values),
                    "partes_procesales2" => json_encode($data2Values),
                    "acciones_realizadas" => Null,
                    "state" => $state,
                    "date_state" => $infoDate,
                    "i_entidad" => $iEntidad,
                    "entidad" => $entidadExp,
                    "abogado_virtual" => $rptaAbo,
                    "id_client" => $idClient,
                    "code_user" => $dataUser->code_user,
                    "code_company" => $dataUser->code_company,
                    "metadata"     => $iDetalle,
                ];

                $dataIdIndecopi = DB::table('indecopis')->insertGetId($newDataIndecopi);

                // Decodificar la cadena JSON en un array asociativo
                $dataArray2 = json_decode($iSeguimientos, true);
                $dataArray2Reverse = array_reverse($dataArray2, true);

                // Contador para el número de acciones
                $contadorAcciones = 0;

                // Iterar sobre las claves del array (que representan las acciones "R1", "R2", "R3", etc.)
                foreach ($dataArray2Reverse as $clave => $accion) {
                    $accionesIndecopi = new AccionesIndecopi();

                    $accionesIndecopi->n_accion = $contadorAcciones++;
                    $accionesIndecopi->fecha = Carbon::createFromFormat('d/m/Y', $accion['Fecha'])->format('Y-m-d') ?? Null;
                    $accionesIndecopi->accion_realizada = $accion['Acción realizada'];
                    $accionesIndecopi->anotaciones = $accion['Anotaciones'];
                    $accionesIndecopi->abog_virtual = $rptaAbo;
                    $accionesIndecopi->metadata = Null;
                    $accionesIndecopi->code_user = $dataUser->code_user;
                    $accionesIndecopi->code_company = $dataUser->code_company;
                    $accionesIndecopi->update_date = Null;
                    $accionesIndecopi->id_indecopi = $dataIdIndecopi;
                    $accionesIndecopi->save();
                }

                UserParte::insert([
                    'nombres' => $dataUser->name,
                    'apellidos' => $dataUser->lastname,
                    'email' => $dataUser->email,
                    'categoria' => null,
                    'rol' => $dataUser->type_user,
                    'id_exp' => $dataIdIndecopi,
                    'code_company' => $dataUser->code_company,
                    'code_user'    => $dataUser->code_user,
                    'entidad' => 'indecopi',
                    'metadata' => 'si',
                ]);

                return redirect()->route('sistema_expedientes.indecopi.viewAcciones', ["Exp" => $dataIdIndecopi])->with('success', '¡Proceso registrado correctamente!');
            } else if ($entidad == "Consulta de Expedientes Judiciales Supremo (CEJ Supremo)") {

                $totalExpediente = CorteSuprema::where('code_company', Auth::user()->code_company)->count();
                $limitExpediente = $dataSuscripcion->limit_suprema;

                // Verificar si ya alcanso el maximo
                if ($totalExpediente >= $limitExpediente && $limitExpediente !== null) {
                    return redirect()->route('sistema_expedientes.expedientesRegistroClientes')->with('error', "Error al crear proceso (Maximo alcanzado).");
                }

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
                $partesProcesalesSuprema = $datosExpediente["partesProcesales"];
                $seguimientoSuprema = $datosExpediente["seguimientoExp"];
                $vistaCausaSuprema = $datosExpediente["vistaCausaExp"];
                $urlSuprema = $datosExpediente["urlSuprema"];
                $idClient = $datosExpediente["id-client"];
                $entidadExp = $datosExpediente["entidad-exp"];

                $partesProcesalesS = json_decode($partesProcesalesSuprema, true);
                $partesSeparadasS = [];

                if ($partesProcesalesS) {
                    foreach ($partesProcesalesS as $key => $parte) {
                        $partesSeparadasS[] = [$key, $parte[0], $parte[1]];
                    }
                }

                // $fechaInSuprema = Carbon::createFromFormat('d/m/Y H:i', $fechaIngresoSuprema);
                // $fechaIngresoFormat= $fechaInSuprema->format('Y-m-d H:i:s');

                $newDataSuprema = [
                    'n_expediente' => $nExpSuprema,
                    'instancia' => $instanciaSuprema,
                    'recurso_sala' => $recursoSalaSuprema,
                    'fecha_ingreso' => $fechaIngresoSuprema,
                    'organo_procedencia' => $organoProcedenciaSuprema,
                    'relator' => $relatorSuprema,
                    'distrito_judicial' => $distritoJudicialSuprema,
                    'numero_procedencia' => $nProcedenciaSuprema,
                    'secretario' => $secretarioSuprema,
                    'delito' => $delitoSuprema,
                    'ubicacion' => $ubicacionSuprema,
                    'estado' => $estadoSuprema,
                    'update_date' => null,
                    'url_suprema' => $urlSuprema,
                    'state' => $state,
                    'date_state' => $infoDate,
                    'partes_procesales' => json_encode($partesSeparadasS),
                    'vista_causas' => null,
                    'abogado_virtual' => $rptaAbo,
                    'entidad' => $entidadExp,
                    'code_user' => Auth::user()->code_user,
                    'code_company' => Auth::user()->code_company,
                    'metadata' => null,
                    'id_client' => $idClient,
                ];

                $idSuprema = CorteSuprema::insertGetId($newDataSuprema);

                $vistaCausaS = json_decode($vistaCausaSuprema, true);
                $numVistaCausa = 0;
                // dd($vistaCausaS["vista_1"]["fechaProgramacion"] !== "");
                if ($vistaCausaS) {
                    foreach ($vistaCausaS as $key => $value) {
                        // guardar las vistas de causas en la base de datos
                        $numVistaCausa++;
                        $tableVistaCausa = new VistaCausaSuprema();
                        $tableVistaCausa->n_vista = $numVistaCausa;

                        if ($value["fecha vista"] !== "") {
                            $fechaVista = Carbon::createFromFormat('d/m/Y H:i:s', $value["fecha vista"]);
                            $fechaFormateadaVista = $fechaVista->format('Y-m-d H:i:s');
                        }
                        $tableVistaCausa->fecha_vista = $fechaFormateadaVista ?? null;

                        if ($value["fechaProgramacion"] !== "") {
                            $fechaProgramacion = Carbon::createFromFormat('d/m/Y', $value["fechaProgramacion"]);
                            $fechaFormateadaProgramacion = $fechaProgramacion->format('Y-m-d');
                        }
                        $tableVistaCausa->fecha_programacion = $fechaFormateadaProgramacion ?? null;

                        $tableVistaCausa->sentido_resultado = $value["sentidoResultado"];
                        $tableVistaCausa->observacion = $value["observacion"];
                        $tableVistaCausa->tipo_vista = $value["tipodeVista"];
                        $tableVistaCausa->abog_virtual = $rptaAbo;
                        $tableVistaCausa->metadata = null;
                        $tableVistaCausa->code_company = Auth::user()->code_company;
                        $tableVistaCausa->code_user = Auth::user()->code_user;
                        $tableVistaCausa->update_date = null;
                        $tableVistaCausa->id_exp = $idSuprema;
                        $tableVistaCausa->save();
                    }
                }

                $seguimientoS = json_decode($seguimientoSuprema, true);
                $seguimientoSReversed = array_reverse($seguimientoS, true);

                $numSeg = 0;
                if ($seguimientoSReversed) {
                    foreach ($seguimientoSReversed as $keyS => $valueS) {
                        $tableSeguimientoS = new SeguimientoSuprema();
                        $tableSeguimientoS->n_seguimiento = $numSeg;
                        $fechaKey = Carbon::createFromFormat('d/m/Y', $keyS);
                        $fechaFormateadaSegui = $fechaKey->format('Y-m-d');
                        $tableSeguimientoS->fecha = $fechaFormateadaSegui;
                        $tableSeguimientoS->acto = $valueS["txtActo"];
                        $tableSeguimientoS->resolucion = $valueS["txtResolucion"];
                        $tableSeguimientoS->fojas = $valueS["numFojas"];
                        $tableSeguimientoS->sumilla = $valueS["txtSumillaSeg"];
                        $tableSeguimientoS->desc_usuario = $valueS["xDescUsuario"];
                        $tableSeguimientoS->presentante = $valueS["presentante"];
                        $tableSeguimientoS->abog_virtual = $rptaAbo;
                        $tableSeguimientoS->u_tipo = null;
                        $tableSeguimientoS->u_title = null;
                        $tableSeguimientoS->u_date = null;
                        $tableSeguimientoS->u_descripcion = null;
                        $tableSeguimientoS->metadata = null;
                        $tableSeguimientoS->code_company = Auth::user()->code_company;
                        $tableSeguimientoS->code_user = Auth::user()->code_user;
                        $tableSeguimientoS->update_date = null;
                        $tableSeguimientoS->id_exp = $idSuprema;
                        $tableSeguimientoS->save();
                        $numSeg++;
                    }
                }

                UserParte::insert([
                    'nombres' => $dataUser->name,
                    'apellidos' => $dataUser->lastname,
                    'email' => $dataUser->email,
                    'categoria' => null,
                    'rol' => $dataUser->type_user,
                    'id_exp' => $idSuprema,
                    'code_company' => $dataUser->code_company,
                    'code_user'    => $dataUser->code_user,
                    'entidad' => 'suprema',
                    'metadata' => 'si',
                ]);

                return redirect()->route('sistema_expedientes.suprema.viewAcciones', ["Exp" => $idSuprema])->with('success', '¡Proceso registrado correctamente!');
            } else if ($entidad == "Poder Judicial del Perú (SINOE)") {

                $totalExpediente = ExpedienteSinoe::where('code_company', Auth::user()->code_company)
                    ->whereNull('proceso_penal')
                    ->count();
                $limitExpediente = $dataSuscripcion->limit_sinoe;

                // Verificar si ya alcanso el maximo
                if ($totalExpediente >= $limitExpediente && $limitExpediente !== null) {
                    return redirect()->route('sistema_expedientes.expedientesRegistroClientes')->with('error', "Error al crear proceso (Maximo alcanzado).");
                }

                // Id del cliente
                $idClient = $datosExpediente["id-client"];
                $numExp = $datosExpediente["m-n-exp"];
                $oJuris  = $datosExpediente["m-o-juris"];
                $disJudi  = $datosExpediente["m-dis-judi"];
                $juez  = $datosExpediente["m-juez"];
                $ubi  = $datosExpediente["m-ubi"];
                $eProcesal  = $datosExpediente["m-e-procesal"];
                $sumilla  = $datosExpediente["m-sumilla"];
                $proceso  = $datosExpediente["m-proceso"];
                $especialidad  = $datosExpediente["m-especialidad"];
                $obs  = $datosExpediente["m-obs"];
                $estado  = $datosExpediente["m-estado"];
                $materia  = $datosExpediente["m-case"];
                $lawyerResponsible  = $datosExpediente["m-lawyer-responsible"];
                $dateInitial  = $datosExpediente["m-date-initial"];
                $dateConclusion  = $datosExpediente["m-date-conclusion"];
                $motivoConclusion  = $datosExpediente["m-motivo-conclusion"];
                $idEntidad  = $datosExpediente["id-entidad"];
                $datosSinoe = $datosExpediente["datosSinoe"];
                $credencialesSinoe = $datosExpediente["a-credenciales-sinoe"];

                $timestamp = strtotime($dateConclusion);
                if ($timestamp !== false) {
                    $dateConclusion = date('Y-m-d', $timestamp);
                } else {
                    $dateConclusion = null;
                }
                $partesSeparadas = [];

                if ($rptaAbo == "si") {
                    $partes = request()->input('parte');
                    $tipoPersonas = request()->input('tipoPersona');
                    $nombresRazonSocial = request()->input('nombresRazonSocial');
                    foreach ($partes as $index => $parte) {
                        if (isset($tipoPersonas[$index]) && isset($nombresRazonSocial[$index])) {
                            $partesSeparadas[] = [$parte, $tipoPersonas[$index], $nombresRazonSocial[$index]];
                        }
                    }
                }
                // DATA EXPEDIENTE
                $newData = [
                    'n_expediente' => $numExp,
                    'o_jurisdicional' => $oJuris,
                    'd_judicial' => $disJudi,
                    'juez' => $juez,
                    'ubicacion' => $ubi,
                    'e_procesal' => $eProcesal,
                    'sumilla' => $sumilla,
                    'proceso' => $proceso,
                    'especialidad' => $especialidad,
                    'observacion' => $obs,
                    'estado' => $estado,
                    'materia' => $materia,
                    'demanding' => null,
                    'defendant' => null,
                    'lawyer_responsible' => $lawyerResponsible,
                    'update_date' => null,
                    'state' => $state,
                    'date_state' => $infoDate,
                    'date_initial' => $dateInitial,
                    'date_conclusion' => $dateConclusion,
                    'motivo_conclusion' => $motivoConclusion,
                    'partes_procesales' => json_encode($partesSeparadas),
                    'abogado_virtual' => $rptaAbo,
                    'id_client' => $idClient,
                    'entidad' => $entidad,
                    'code_user' => $dataUser->code_user,
                    'code_company' => $dataUser->code_company,
                ];

                $dataIdSinoe = ExpedienteSinoe::insertGetId($newData);

                UserParte::insert([
                    'nombres' => $dataUser->name,
                    'apellidos' => $dataUser->lastname,
                    'email' => $dataUser->email,
                    'categoria' => null,
                    'rol' => $dataUser->type_user,
                    'id_exp' => $dataIdSinoe,
                    'code_company' => $dataUser->code_company,
                    'code_user'    => $dataUser->code_user,
                    'entidad' => 'sinoe',
                    'metadata' => 'si',
                ]);

                $jsonDatosSinoe = json_decode($datosSinoe, true);
                $jsonDatosSinoeReversed = array_reverse($jsonDatosSinoe["resultados"], true);
                foreach ($jsonDatosSinoeReversed as $key => $resData) {
                    $notificacionSinoe = new NotificationSinoe();

                    $fechaDateTime = DateTime::createFromFormat('d/m/Y H:i:s', $resData["Registro"]["Fecha"]);
                    $fechaFormateada = $fechaDateTime->format('Y-m-d H:i:s');

                    $notificacionSinoe->tipo = "Registro";
                    $notificacionSinoe->n_notificacion = $resData["Registro"]["N° Notificación"];
                    $notificacionSinoe->n_expediente = $resData["Registro"]["N° Expediente"];
                    $notificacionSinoe->sumilla = $resData["Registro"]["Sumilla"];
                    $notificacionSinoe->oj = $resData["Registro"]["O.J"];
                    $notificacionSinoe->fecha = $fechaFormateada;
                    $notificacionSinoe->id_exp = $dataIdSinoe;
                    $notificacionSinoe->uid_credenciales_sinoe = $credencialesSinoe;
                    $notificacionSinoe->update_date = null;
                    $notificacionSinoe->abog_virtual = $rptaAbo;
                    $notificacionSinoe->code_user = Auth::user()->code_user;
                    $notificacionSinoe->code_company = Auth::user()->code_company;
                    $notificacionSinoe->save();



                    if (isset($resData['Anexos']) && is_array($resData['Anexos'])) {
                        foreach ($resData['Anexos'] as $anexoKey => $anexoData) {
                            $anexoModel = new AnexoNotificationSinoe();
                            $anexoModel->tipo = $anexoData["Tipo"];
                            $anexoModel->identificacion = $anexoData["Identificación de anexo"];
                            $anexoModel->n_paginas = $anexoData["Nro. de Paginas"];
                            $anexoModel->documento = $anexoData["Documento"];
                            $anexoModel->id_exp = $dataIdSinoe;
                            $anexoModel->id_notification = $notificacionSinoe->id;
                            $anexoModel->abog_virtual = $rptaAbo;
                            $anexoModel->code_user = Auth::user()->code_user;
                            $anexoModel->code_company = Auth::user()->code_company;
                            $anexoModel->save();
                        }
                    }
                }

                $ultMovi = NotificationSinoe::where('id_exp', $dataIdSinoe)->where('abog_virtual', 'si')->orderBy('id', 'desc')->get()->first();

                $fechaConvertida = Carbon::createFromFormat('Y-m-d H:i:s', $ultMovi->fecha);
                $fechaFormateada = $fechaConvertida->format('d/m/Y H:i:s');

                $data = [
                    'numExpediente' => $numExp,
                    'fechaHoraNotifi' => $fechaFormateada,
                    'credential' => $credencialesSinoe
                ];

                TempDocumentPresentado::create([
                    'id_exp' => $dataIdSinoe,
                    'uid' => $credencialesSinoe,
                    'n_expediente' => $numExp,
                    'entidad' => "sinoe",
                    'estado' => "pendiente",
                    'metadata' => null,
                    'code_company' => Auth::user()->code_company,
                    'code_user' => Auth::user()->code_user
                ]);


                // $response = Http::post(config('app.url_rpa').'/sinoe-historial-data', $data);
                // // dd($response->json());
                // // Verifica si la solicitud fue exitosa antes de procesar la respuesta
                // if ($response->successful()) {
                //     // Obtiene los datos de la respuesta
                //     $data = $response->json(); // Convierte la respuesta a un array

                //     if ($data["status"] == 200 && $data["msg"] !== ""){
                //         $datos = $data["data"];
                //         if ($datos["numResults"] > 0){
                //             $result = $datos["resultados"];
                //             foreach ($result as $key => $valResult) {
                //                 $registros = $valResult["Registro"];
                //                 $archivos = $valResult["Archivos"];

                //                 $fechaConvertidaSinoe = Carbon::createFromFormat('d/m/Y H:i:s', $registros["Fecha de Presentación"]);
                //                 $fechaFormateadaSinoe = $fechaConvertidaSinoe->format('Y-m-d H:i:s');

                //                 $newDataHistorial = [
                //                     'n_expediente' => $registros["Cod. Expediente"],
                //                     'id_exp' => $dataIdSinoe,
                //                     'n_escrito' => $registros["Nro. Escrito"],
                //                     'distrito_judicial' => $registros["Distrito Judicial"],
                //                     'organo_juris' => $registros["Órgano Jurisdiccional"],
                //                     'tipo_doc' => $registros["Tipo de Documento"],
                //                     'fecha_presentacion' => $fechaFormateadaSinoe,
                //                     'sumilla' => $registros["Sumilla"],
                //                     'metadata' => 'si',
                //                     'code_company' => Auth::user()->code_company,
                //                     'code_user' => Auth::user()->code_user
                //                 ];

                //                 $insertedIdHistorial = HistorialDocumentosSinoe::insertGetId($newDataHistorial);

                //                 foreach ($archivos as $key => $valArchivos) {
                //                     $newDataDoc = [
                //                         'id_exp' => $dataIdSinoe,
                //                         'id_historial' => $insertedIdHistorial,
                //                         'descripcion' => $valArchivos["Descripción"],
                //                         'file_doc' =>$valArchivos["Documento"],
                //                         'file_cargo' => null,
                //                         'metadata' => 'si',
                //                         'code_company' => Auth::user()->code_company,
                //                         'code_user' => Auth::user()->code_user
                //                     ];
                //                     DocumentosPresentadosSinoe::create($newDataDoc);
                //                 }
                //             }
                //         }
                //     }

                // } else {
                //     // Si la solicitud falla, maneja el error aquí
                //     $statusCode = $response->status();
                //     // ... lógica para manejar el error
                // }

                return redirect()->route('sistema_expedientes.sinoe.viewSeguimiento', ["Exp" => $dataIdSinoe])->with('success', '¡Expediente registrado correctamente!');
            } else {
                return redirect()->back()->with('error', '¡Entidad no permitida!');
            }
        } else {

            if ($entidadM == "CEJ por Código de Expediente" || $entidadM == "CEJ por Número y Juzgado") {

                $totalExpediente = Expedientes::where('code_company', Auth::user()->code_company)->count();
                $limitExpediente = $dataSuscripcion->limit_judicial;

                // Verificar si ya alcanso el maximo
                if ($totalExpediente >= $limitExpediente && $limitExpediente !== null) {
                    return redirect()->route('sistema_expedientes.expedientesRegistroClientes')->with('error', "Error al crear proceso (Maximo alcanzado).");
                }

                // Id del cliente
                $idClient = $datosExpediente["id-client"];
                $numExp = $datosExpediente["m-n-exp"];
                $oJuris  = $datosExpediente["m-o-juris"];
                $disJudi  = $datosExpediente["m-dis-judi"];
                $juez  = $datosExpediente["m-juez"];
                $ubi  = $datosExpediente["m-ubi"];
                $eProcesal  = $datosExpediente["m-e-procesal"];
                $sumilla  = $datosExpediente["m-sumilla"];
                $proceso  = $datosExpediente["m-proceso"];
                $especialidad  = $datosExpediente["m-especialidad"];
                $obs  = $datosExpediente["m-obs"];
                $estado  = $datosExpediente["m-estado"];
                $materia  = $datosExpediente["m-case"];
                $lawyerResponsible  = $datosExpediente["m-lawyer-responsible"];
                $dateInitial  = $datosExpediente["m-date-initial"];
                $dateConclusion  = $datosExpediente["m-date-conclusion"];
                $motivoConclusion  = $datosExpediente["m-motivo-conclusion"];
                $idEntidad  = $datosExpediente["id-entidad"];
                $timestamp = strtotime($dateConclusion);
                if ($timestamp !== false) {
                    $dateConclusion = date('Y-m-d', $timestamp);
                } else {
                    $dateConclusion = null;
                }
                $partesSeparadas = [];

                if ($rptaAbo == "no") {
                    $partes = request()->input('parte');
                    $tipoPersonas = request()->input('tipoPersona');
                    $nombresRazonSocial = request()->input('nombresRazonSocial');
                    foreach ($partes as $index => $parte) {
                        if (isset($tipoPersonas[$index]) && isset($nombresRazonSocial[$index])) {
                            $partesSeparadas[] = [$parte, $tipoPersonas[$index], $nombresRazonSocial[$index]];
                        }
                    }
                }
                // DATA EXPEDIENTE
                $newData = [
                    'n_expediente' => $numExp,
                    'o_jurisdicional' => $oJuris,
                    'd_judicial' => $disJudi,
                    'juez' => $juez,
                    'ubicacion' => $ubi,
                    'e_procesal' => $eProcesal,
                    'sumilla' => $sumilla,
                    'proceso' => $proceso,
                    'especialidad' => $especialidad,
                    'observacion' => $obs,
                    'estado' => $estado,
                    'materia' => $materia,
                    'demanding' => null,
                    'defendant' => null,
                    'lawyer_responsible' => $lawyerResponsible,
                    'update_date' => null,
                    'state' => $state,
                    'date_state' => $infoDate,
                    'date_initial' => $dateInitial,
                    'date_conclusion' => $dateConclusion,
                    'motivo_conclusion' => $motivoConclusion,
                    'partes_procesales' => json_encode($partesSeparadas),
                    'abogado_virtual' => $rptaAbo,
                    'id_client' => $idClient,
                    'entidad' => $entidad,
                    'code_user' => $dataUser->code_user,
                    'code_company' => $dataUser->code_company,
                ];
                // Expedientes::insert($newData);
                $dataId = DB::table('expedientes')->insertGetId($newData);
                // $seguimientoExp = $datosExpediente["seguimientoExp"];
                // $seguimientoAll = json_decode($seguimientoExp, true);
                // $reversedData = array_reverse($seguimientoAll, true);
                // $numObs = 0;
                // $notiIds = [];
                // foreach ($reversedData as $key => $entry) {
                //     $obs = new FollowUp();
                //         // Asignar valores a las columnas respectivas
                //     $obs->n_seguimiento = $numObs++;
                //     $fechaIngreso = isset($entry['Fecha de Ingreso']) ? DateTime::createFromFormat('d/m/Y H:i', $entry['Fecha de Ingreso']) : null;
                //     $fechaResolucion = isset($entry['Fecha de Resolución']) ? DateTime::createFromFormat('d/m/Y', $entry['Fecha de Resolución']) : null;
                //     $proveido = isset($entry['Proveido']) ? DateTime::createFromFormat('d/m/Y', $entry['Proveido']) : null;
                //     $obs->fecha_ingreso = $fechaIngreso ? $fechaIngreso->format('Y-m-d H:i:s') : null;
                //     $obs->fecha_resolucion = $fechaResolucion ? $fechaResolucion->format('Y-m-d') : null;
                //     $obs->resolucion = isset($entry['Resolución']) ? $entry['Resolución'] : null;
                //     $obs->type_notificacion = isset($entry['Tipo de Notificación']) ? $entry['Tipo de Notificación'] : null;
                //     $obs->acto = isset($entry['Acto']) && !empty($entry['Acto']) ? $entry['Acto'] : null;
                //     $obs->folios = isset($entry['Folios']) && !empty($entry['Folios']) ? $entry['Folios'] : null;
                //     $obs->fojas = ($entry['Fojas'] !== "") ? $entry['Fojas'] : null;
                //     $obs->proveido = $proveido ? $proveido->format(('Y-m-d')) : null;
                //     $obs->obs_sumilla = isset($entry['Sumilla']) ? $entry['Sumilla'] : null;
                //     $obs->descripcion = isset($entry['Descripción de Usuario']) ? $entry['Descripción de Usuario'] : null;
                //     $obs->file = isset($entry['Descarga resolucion']) ? $entry['Descarga resolucion'] : null;
                //     $obs->id_exp = $dataId;
                //     $obs->abog_virtual = $rptaAbo;
                //     $obs->code_company = $dataUser->code_company;
                //     $obs->save();
                //             // Si hay notificaciones en el entry, también puedes iterar a través de ellas
                //     if (isset($entry['notifi']) && is_array($entry['notifi'])) {
                //         $notiIds = [];
                //         foreach ($entry['notifi'] as $notificacionKey => $notificacion) {
                //             $notificacionModel = new NotificacionSeguimiento();
                //             // Asignar valores a la notificación y guárdala
                //             $notificacionModel->name = $notificacionKey;
                //             $notificacionModel->destinatario = isset($notificacion['Destinatario']) ? $notificacion['Destinatario'] : null;
                //             $fechaEnvio = isset($notificacion['Fecha de envio']) ? DateTime::createFromFormat('d/m/Y H:i', $notificacion['Fecha de envio']) : null;
                //             $notificacionModel->fecha_envio = $fechaEnvio ? $fechaEnvio->format('Y-m-d H:i:s') : null;
                //             $notificacionModel->anexos = isset($notificacion['Anexo(s)']) ? $notificacion['Anexo(s)'] : null;
                //             $notificacionModel->forma_entrega = isset($notificacion['Forma de entrega']) ? $notificacion['Forma de entrega']: null;
                //             $notificacionModel->id_exp = $dataId;
                //             $notificacionModel->abog_virtual = $rptaAbo;
                //             $notificacionModel->save();
                //                 $notiIds[] = $notificacionModel->id;
                //         }
                //         // Asignar el ID de notificación a la propiedad $obs->noti después del bucle de notificaciones
                //         if ($notiIds != []) {
                //             $obs->noti = json_encode($notiIds);
                //             $obs->save(); // Actualizar el seguimiento con el ID de notificación
                //         }
                //     }
                // }
                // $currentDateTime = Carbon::now();
                // $mysqlDateTime = $currentDateTime->format('Y-m-d H:i:s');
                // Alert::insert([
                //     'date_time'=> $mysqlDateTime,
                //     'message' =>"Se registro un nuevo expediente  N° " . $numExp,
                //     'alert' => 'success',
                //     'type' => 'expediente',
                //     'id_exp' => $dataId,
                // ]);

                UserParte::insert([
                    'nombres' => $dataUser->name,
                    'apellidos' => $dataUser->lastname,
                    'email' => $dataUser->email,
                    'categoria' => null,
                    'rol' => $dataUser->type_user,
                    'id_exp' => $dataId,
                    'code_company' => $dataUser->code_company,
                    'code_user'    => $dataUser->code_user,
                    'entidad' => 'judicial',
                    'metadata' => 'si',
                ]);

                return redirect()->route('sistema_expedientes.viewSeguimiento', ["Exp" => $dataId])->with('success', '¡Expediente registrado correctamente!');
            } else if ($entidadM == "Indecopi - Búsqueda por número de Reclamo/Buen Oficio" || $entidadM == "Indecopi - Búsqueda por Reclamante/Ciudadano" || $entidadM == "Indecopi - Búsqueda por Reclamado/Proveedor") {

                $totalExpediente = Indecopi::where('code_company', Auth::user()->code_company)->count();
                $limitExpediente = $dataSuscripcion->limit_indecopi;

                // Verificar si ya alcanso el maximo
                if ($totalExpediente >= $limitExpediente && $limitExpediente !== null) {
                    return redirect()->route('sistema_expedientes.expedientesRegistroClientes')->with('error', "Error al crear proceso (Maximo alcanzado).");
                }

                $idClient = $datosExpediente["id-client"];
                $entidadExp = $datosExpediente["entidad-exp"];
                // $iTipo = $datosExpediente["a-indecopi-tipo"];
                $iTipo = $datosExpediente["m-indecopi-tipo"];
                // $iFechaDesde = $datosExpediente["a-indecopi-fecha-desde"];
                // $iFechaHasta = $datosExpediente["a-indecopi-fecha-hasta"];
                // $iTypeDoc = $datosExpediente["a-indecopi-type-doc"];
                // $iNombreRazon = $datosExpediente["a-indecopi-nombre-razon"];
                // $iApellidoP = $datosExpediente["a-indecopi-apellido-p"];
                // $iApellidoM = $datosExpediente["a-indecopi-apellido-m"];
                $iNTipo = $datosExpediente["i-n-tipo"];
                // $iTipo = $datosExpediente["i-tipo"];
                $iEntidad = $datosExpediente["i-entidad"];
                $iOficina = $datosExpediente["i-oficina"];
                $iResponsable = $datosExpediente["i-responsable"];
                $iViaPresentacion = $datosExpediente["i-via-presentacion"];
                $iFechaInicio = $datosExpediente["i-fecha-inicio"];
                $iEstado = $datosExpediente["i-estado"];
                $iFecha = $datosExpediente["i-fecha"];
                $iFormaConclusion = $datosExpediente["i-forma-conclusion"];
                $iPartesProcesales = $datosExpediente["partesProcesales"];
                $iSeguimientos = $datosExpediente["seguimientoExp"];
                // $state = $datosExpediente["m-state"];
                // $infoState = $datosExpediente["m-info-date"];

                // Crear un array para almacenar los datos de "data1" y "data2"
                $data1Values = [];
                $data2Values = [];

                // if ($rptaAbo == 'si'){

                //     // Decodificar la cadena JSON en un array asociativo
                //     $dataArray = json_decode($iPartesProcesales, true);


                //     // Iterar sobre las secciones "data1" y "data2"
                //     foreach (['data1', 'data2'] as $section) {
                //         if (isset($dataArray[$section])) {
                //             $sectionArray = json_decode($dataArray[$section], true);
                //             foreach ($sectionArray as $subkey => $subarray) {
                //             $values = array_values($subarray);
                //             // Agregar los valores al array correspondiente (data1Values o data2Values)
                //             if ($section === 'data1') {
                //                 $data1Values[] = $values;
                //             } elseif ($section === 'data2') {
                //                 $data2Values[] = $values;
                //             }
                //             }
                //         }
                //     }



                // }
                if ($rptaAbo == 'no') {
                    // Ingesó los datos del expediente manualmente
                    // $iTipo = $datosExpediente["m-indecopi-tipo"];

                    // Definir el número de registros en los campos (pueden ser diferentes)
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
                }

                $newDataIndecopi = [
                    "tipo" => $iTipo,
                    "numero" => $iNTipo,
                    "oficina" => $iOficina,
                    "responsable" => $iResponsable,
                    "via_presentacion" => $iViaPresentacion,
                    "fecha_inicio" => $iFechaInicio,
                    "estado" => $iEstado,
                    "fecha" => $iFecha,
                    "forma_conclusion" => $iFormaConclusion,
                    "partes_procesales1" => json_encode($data1Values),
                    "partes_procesales2" => json_encode($data2Values),
                    "acciones_realizadas" => Null,
                    "state" => $state,
                    "date_state" => $infoDate,
                    "i_entidad" => $iEntidad,
                    "entidad" => $entidadExp,
                    "abogado_virtual" => $rptaAbo,
                    "id_client" => $idClient,
                    "code_user" => $dataUser->code_user,
                    "code_company" => $dataUser->code_company,
                    "metadata"     => Null,
                ];

                $dataIdIndecopi = DB::table('indecopis')->insertGetId($newDataIndecopi);

                // Decodificar la cadena JSON en un array asociativo
                // $dataArray2 = json_decode($iSeguimientos, true);
                // $dataArray2Reverse = array_reverse($dataArray2, true);

                // Contador para el número de acciones
                // $contadorAcciones = 0;

                // Iterar sobre las claves del array (que representan las acciones "R1", "R2", "R3", etc.)
                // foreach ($dataArray2Reverse as $clave => $accion) {
                //     $accionesIndecopi = new AccionesIndecopi();

                //     $accionesIndecopi->n_accion = $contadorAcciones++;
                //     $accionesIndecopi->fecha = Carbon::createFromFormat('d/m/Y', $accion['Fecha'])->format('Y-m-d') ?? Null;
                //     $accionesIndecopi->accion_realizada = $accion['Acción realizada'];
                //     $accionesIndecopi->anotaciones = $accion['Anotaciones'];
                //     $accionesIndecopi->abog_virtual = $rptaAbo;
                //     $accionesIndecopi->metadata = Null;
                //     $accionesIndecopi->code_user = $dataUser->code_user;
                //     $accionesIndecopi->code_company = $dataUser->code_company;
                //     $accionesIndecopi->update_date = Null;
                //     $accionesIndecopi->id_indecopi = $dataIdIndecopi;
                //     $accionesIndecopi->save();
                // }

                UserParte::insert([
                    'nombres' => $dataUser->name,
                    'apellidos' => $dataUser->lastname,
                    'email' => $dataUser->email,
                    'categoria' => null,
                    'rol' => $dataUser->type_user,
                    'id_exp' => $dataIdIndecopi,
                    'code_company' => $dataUser->code_company,
                    'code_user'    => $dataUser->code_user,
                    'entidad' => 'indecopi',
                    'metadata' => 'si',
                ]);

                return redirect()->route('sistema_expedientes.indecopi.viewAcciones', ["Exp" => $dataIdIndecopi])->with('success', '¡Proceso registrado correctamente!');
            } else if ($entidadM == "Consulta de Expedientes Judiciales Supremo (CEJ Supremo)") {

                $totalExpediente = CorteSuprema::where('code_company', Auth::user()->code_company)->count();
                $limitExpediente = $dataSuscripcion->limit_suprema;

                // Verificar si ya alcanso el maximo
                if ($totalExpediente >= $limitExpediente && $limitExpediente !== null) {
                    return redirect()->route('sistema_expedientes.expedientesRegistroClientes')->with('error', "Error al crear proceso (Maximo alcanzado).");
                }

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
                $urlSuprema = $datosExpediente["urlSuprema"];
                $idClient = $datosExpediente["id-client"];
                $entidadExp = $datosExpediente["entidad-exp"];

                $partesSeparadas = [];

                $partesP = request()->input('parte');
                $datosP = request()->input('datos');
                $caracteristicaP = request()->input('caracteristica');
                foreach ($partesP as $index => $parte) {
                    $caractP = $caracteristicaP[$index] == null ? "" : $caracteristicaP[$index];
                    $partesSeparadas[] = [$parte, $datosP[$index], $caractP];
                }

                $newDataSuprema = [
                    'n_expediente' => $nExpSuprema,
                    'instancia' => $instanciaSuprema,
                    'recurso_sala' => $recursoSalaSuprema,
                    'fecha_ingreso' => $fechaIngresoSuprema,
                    'organo_procedencia' => $organoProcedenciaSuprema,
                    'relator' => $relatorSuprema,
                    'distrito_judicial' => $distritoJudicialSuprema,
                    'numero_procedencia' => $nProcedenciaSuprema,
                    'secretario' => $secretarioSuprema,
                    'delito' => $delitoSuprema,
                    'ubicacion' => $ubicacionSuprema,
                    'estado' => $estadoSuprema,
                    'update_date' => null,
                    'url_suprema' => $urlSuprema,
                    'state' => $state,
                    'date_state' => $infoDate,
                    'partes_procesales' => json_encode($partesSeparadas),
                    'vista_causas' => null,
                    'abogado_virtual' => $rptaAbo,
                    'entidad' => $entidadExp,
                    'code_user' => Auth::user()->code_user,
                    'code_company' => Auth::user()->code_company,
                    'metadata' => null,
                    'id_client' => $idClient,
                ];

                $idSuprema = CorteSuprema::insertGetId($newDataSuprema);

                UserParte::insert([
                    'nombres' => $dataUser->name,
                    'apellidos' => $dataUser->lastname,
                    'email' => $dataUser->email,
                    'categoria' => null,
                    'rol' => $dataUser->type_user,
                    'id_exp' => $idSuprema,
                    'code_company' => $dataUser->code_company,
                    'code_user'    => $dataUser->code_user,
                    'entidad' => 'suprema',
                    'metadata' => 'si',
                ]);

                return redirect()->route('sistema_expedientes.suprema.viewAcciones', ["Exp" => $idSuprema])->with('success', '¡Proceso registrado correctamente!');
            } else if ($entidadM == "Poder Judicial del Perú (SINOE)") {

                $totalExpediente = ExpedienteSinoe::where('code_company', Auth::user()->code_company)
                    ->whereNull('proceso_penal')
                    ->count();
                $limitExpediente = $dataSuscripcion->limit_sinoe;

                // Verificar si ya alcanso el maximo
                if ($totalExpediente >= $limitExpediente && $limitExpediente !== null) {
                    return redirect()->route('sistema_expedientes.expedientesRegistroClientes')->with('error', "Error al crear proceso (Maximo alcanzado).");
                }

                // Id del cliente
                $idClient = $datosExpediente["id-client"];
                $numExp = $datosExpediente["m-n-exp"];
                $oJuris  = $datosExpediente["m-o-juris"];
                $disJudi  = $datosExpediente["m-dis-judi"];
                $juez  = $datosExpediente["m-juez"];
                $ubi  = $datosExpediente["m-ubi"];
                $eProcesal  = $datosExpediente["m-e-procesal"];
                $sumilla  = $datosExpediente["m-sumilla"];
                $proceso  = $datosExpediente["m-proceso"];
                $especialidad  = $datosExpediente["m-especialidad"];
                $obs  = $datosExpediente["m-obs"];
                $estado  = $datosExpediente["m-estado"];
                $materia  = $datosExpediente["m-case"];
                $lawyerResponsible  = $datosExpediente["m-lawyer-responsible"];
                $dateInitial  = $datosExpediente["m-date-initial"];
                $dateConclusion  = $datosExpediente["m-date-conclusion"];
                $motivoConclusion  = $datosExpediente["m-motivo-conclusion"];
                $idEntidad  = $datosExpediente["id-entidad"];
                $timestamp = strtotime($dateConclusion);
                if ($timestamp !== false) {
                    $dateConclusion = date('Y-m-d', $timestamp);
                } else {
                    $dateConclusion = null;
                }
                $partesSeparadas = [];

                if ($rptaAbo == "no") {
                    $partes = request()->input('parte');
                    $tipoPersonas = request()->input('tipoPersona');
                    $nombresRazonSocial = request()->input('nombresRazonSocial');
                    foreach ($partes as $index => $parte) {
                        if (isset($tipoPersonas[$index]) && isset($nombresRazonSocial[$index])) {
                            $partesSeparadas[] = [$parte, $tipoPersonas[$index], $nombresRazonSocial[$index]];
                        }
                    }
                }
                // DATA EXPEDIENTE
                $newData = [
                    'n_expediente' => $numExp,
                    'o_jurisdicional' => $oJuris,
                    'd_judicial' => $disJudi,
                    'juez' => $juez,
                    'ubicacion' => $ubi,
                    'e_procesal' => $eProcesal,
                    'sumilla' => $sumilla,
                    'proceso' => $proceso,
                    'especialidad' => $especialidad,
                    'observacion' => $obs,
                    'estado' => $estado,
                    'materia' => $materia,
                    'demanding' => null,
                    'defendant' => null,
                    'lawyer_responsible' => $lawyerResponsible,
                    'update_date' => null,
                    'state' => $state,
                    'date_state' => $infoDate,
                    'date_initial' => $dateInitial,
                    'date_conclusion' => $dateConclusion,
                    'motivo_conclusion' => $motivoConclusion,
                    'partes_procesales' => json_encode($partesSeparadas),
                    'abogado_virtual' => $rptaAbo,
                    'id_client' => $idClient,
                    'entidad' => $entidad,
                    'code_user' => $dataUser->code_user,
                    'code_company' => $dataUser->code_company,
                ];

                $dataId = ExpedienteSinoe::insertGetId($newData);

                UserParte::insert([
                    'nombres' => $dataUser->name,
                    'apellidos' => $dataUser->lastname,
                    'email' => $dataUser->email,
                    'categoria' => null,
                    'rol' => $dataUser->type_user,
                    'id_exp' => $dataId,
                    'code_company' => $dataUser->code_company,
                    'code_user'    => $dataUser->code_user,
                    'entidad' => 'sinoe',
                    'metadata' => 'si',
                ]);

                return redirect()->route('sistema_expedientes.sinoe.viewSeguimiento', ["Exp" => $dataId])->with('success', '¡Expediente registrado correctamente!');
            } else if ($entidadM == "Proceso Penal") {

                $totalExpediente = ExpedienteSinoe::where('code_company', Auth::user()->code_company)
                    ->where('proceso_penal', 'si')
                    ->count();
                // $limitExpediente = $dataSuscripcion->limit_sinoe;

                // // Verificar si ya alcanso el maximo
                // if ($totalExpediente >= $limitExpediente && $limitExpediente !== null) {
                //     return redirect()->route('sistema_expedientes.expedientesRegistroClientes')->with('error', "Error al crear proceso (Maximo alcanzado).");
                // }

                // Id del cliente
                $idClient = $datosExpediente["id-client"];
                $numExp = $datosExpediente["m-n-exp"];
                $oJuris  = $datosExpediente["m-o-juris"];
                $disJudi  = $datosExpediente["m-dis-judi"];
                $juez  = $datosExpediente["m-juez"];
                $ubi  = $datosExpediente["m-ubi"];
                $eProcesal  = $datosExpediente["m-e-procesal"];
                $sumilla  = $datosExpediente["m-sumilla"];
                $proceso  = $datosExpediente["m-proceso"];
                $especialidad  = $datosExpediente["m-especialidad"];
                $obs  = $datosExpediente["m-obs"];
                $estado  = $datosExpediente["m-estado"];
                $materia  = $datosExpediente["m-case"];
                $lawyerResponsible  = $datosExpediente["m-lawyer-responsible"];
                $dateInitial  = $datosExpediente["m-date-initial"];
                $dateConclusion  = $datosExpediente["m-date-conclusion"];
                $motivoConclusion  = $datosExpediente["m-motivo-conclusion"];
                $idEntidad  = $datosExpediente["id-entidad"];
                $timestamp = strtotime($dateConclusion);
                if ($timestamp !== false) {
                    $dateConclusion = date('Y-m-d', $timestamp);
                } else {
                    $dateConclusion = null;
                }
                $partesSeparadas = [];

                if ($rptaAbo == "no") {
                    $partes = request()->input('parte');
                    $tipoPersonas = request()->input('tipoPersona');
                    $nombresRazonSocial = request()->input('nombresRazonSocial');
                    foreach ($partes as $index => $parte) {
                        if (isset($tipoPersonas[$index]) && isset($nombresRazonSocial[$index])) {
                            $partesSeparadas[] = [$parte, $tipoPersonas[$index], $nombresRazonSocial[$index]];
                        }
                    }
                }
                // DATA EXPEDIENTE
                $newData = [
                    'n_expediente' => $numExp,
                    'o_jurisdicional' => $oJuris,
                    'd_judicial' => $disJudi,
                    'juez' => $juez,
                    'ubicacion' => $ubi,
                    'e_procesal' => $eProcesal,
                    'sumilla' => $sumilla,
                    'proceso' => $proceso,
                    'especialidad' => $especialidad,
                    'observacion' => $obs,
                    'estado' => $estado,
                    'materia' => $materia,
                    'demanding' => null,
                    'defendant' => null,
                    'lawyer_responsible' => $lawyerResponsible,
                    'update_date' => null,
                    'state' => $state,
                    'date_state' => $infoDate,
                    'date_initial' => $dateInitial,
                    'date_conclusion' => $dateConclusion,
                    'motivo_conclusion' => $motivoConclusion,
                    'partes_procesales' => json_encode($partesSeparadas),
                    'abogado_virtual' => $rptaAbo,
                    'id_client' => $idClient,
                    'entidad' => $entidad,
                    'code_user' => $dataUser->code_user,
                    'code_company' => $dataUser->code_company,
                    'proceso_penal' => 'si',

                ];

                $dataId = ExpedienteSinoe::insertGetId($newData);

                UserParte::insert([
                    'nombres' => $dataUser->name,
                    'apellidos' => $dataUser->lastname,
                    'email' => $dataUser->email,
                    'categoria' => null,
                    'rol' => $dataUser->type_user,
                    'id_exp' => $dataId,
                    'code_company' => $dataUser->code_company,
                    'code_user'    => $dataUser->code_user,
                    'entidad' => 'penal',
                    'metadata' => 'si',
                ]);

                return redirect()->route('sistema_expedientes.penal.viewSeguimiento', ["Exp" => $dataId])->with('success', '¡Expediente registrado correctamente!');
            } else {
                return redirect()->back()->with('error', '¡Entidad no permitida!');
            }
        }
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
        $uExpediente = Expedientes::find($id);
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


        // FollowUp::where('id_exp' , '=', $id)
        // ->where('n_seguimiento', '=', 0)
        // ->update([
        //     'obs_sumilla' => $case,
        //     'update_date' => now(),
        // ]);
        $currentDateTime = Carbon::now();
        $mysqlDateTime = $currentDateTime->format('Y-m-d H:i:s');
        // Alert::insert([
        //     'date_time'=> $mysqlDateTime,
        //     'message' =>"Se actualizó el expediente  N° ". $codeExp,
        //     'alert' => 'info',
        //     'type' => $entidad,
        //     'id_exp' => $id,
        //     'num_obs' => 0,
        // ]);
        return redirect()->route('sistema_expedientes.expedientesRegistroExpedientes')->with('success', '¡Expediente actualizado correctamente!');
    }

    // GET DATOS EXPEDIENTE
    public function datosExpediente(Request $request)
    {
        $id = $_POST['id'];
        $dataExpediente = Expedientes::join('clientes', 'expedientes.id_client', '=', 'clientes.id')
            ->select(
                'expedientes.id',
                'expedientes.n_expediente',
                'expedientes.o_jurisdicional',
                'expedientes.d_judicial',
                'expedientes.juez',
                'expedientes.ubicacion',
                'expedientes.e_procesal',
                'expedientes.sumilla',
                'expedientes.proceso',
                'expedientes.especialidad',
                'expedientes.observacion',
                'expedientes.estado',
                'expedientes.materia',
                'expedientes.demanding',
                'expedientes.defendant',
                // 'expedientes.info_proceso',
                'expedientes.lawyer_responsible',
                'expedientes.update_date',
                'expedientes.state',
                'expedientes.date_state',
                'expedientes.partes_procesales',
                'expedientes.entidad',
                // 'expedientes.info_date',
                // 'expedientes.initial_date',
                'expedientes.date_initial',
                'expedientes.date_conclusion',
                'expedientes.motivo_conclusion',
                'clientes.name',
                'clientes.last_name',
                'clientes.name_company',
                'clientes.type_contact',
                'clientes.ruc',
                'clientes.email',
                'clientes.phone',
            )
            ->where('expedientes.id', '=', $id)
            ->get();

        return response($dataExpediente);
    }

    // DELETE
    public function deleteExpediente()
    {
        $id = $_POST['id'];
        $exp = Expedientes::where('id', '=', $id)->get()->first();
        $dataUser = User::where('id', '=', auth()->id())->get()->first();
        // $currentDateTime = Carbon::now();
        // $mysqlDateTime = $currentDateTime->format('Y-m-d H:i:s');
        $expN = $exp->n_expediente;
        NotificacionSeguimiento::where('id_exp', '=', $id)->delete();
        FollowUp::where('id_exp', '=', $id)->delete();
        Expedientes::destroy($id);
        UserParte::where('id_exp', '=', $id)
            ->where('entidad', '=', 'judicial')
            ->where('code_company', '=', $dataUser->code_company)
            ->delete();
        // Alert::insert([
        //     'date_time'=> $mysqlDateTime,
        //     'message' =>"Se eliminó el expediente  N° ". $expN,
        //     'alert' => 'danger',
        //     'type' => 'expediente',
        //     'id_exp' => $id,
        // ]);

        TaskExpediente::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();
        WorkFlowTaskExpediente::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();
        CommentMovement::where('id_exp', $id)->delete();
        CommentTaskJudicial::where('id_exp', $id)->delete();
        CommentTaskFlujoJudicial::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();
        EconomicExpenses::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();
        FlujoAsociadoExpediente::where('id_exp', $id)->where('code_company', Auth::user()->code_company)->delete();

        EventSuggestion::where('code_company', Auth::user()->code_company)
            ->where('entidad', 'judicial')
            ->where('metadata', $id)
            ->delete();

        // $directoryToDelete = '/public/docs/' . $expN;

        // if (Storage::exists($directoryToDelete)) {
        //     Storage::deleteDirectory($directoryToDelete);
        // }

        return response()->json("Eliminado");
    }

    // public function generarReporte()
    // {
    //     return view('dashboard.reporte');
    // }

    /* *************************************************
     *
     *          SEGUIMIENTO DE EXPEDIENTE
     *
     **************************************************/



    public function viewSeguimiento(Request $request)
    {

        $id = request()->input('Exp');

        if ($id) {

            $dataUser = User::where('id', Auth()->id())->get()->first();

            $data = Expedientes::join('clientes', 'expedientes.id_client', '=', 'clientes.id')
                ->select(
                    'expedientes.id',
                    'expedientes.n_expediente',
                    'expedientes.materia',
                    'expedientes.proceso',
                    'expedientes.lawyer_responsible',
                    'expedientes.estado',
                    'expedientes.sumilla',
                    'expedientes.date_initial',
                    'expedientes.update_date',
                    'expedientes.o_jurisdicional',
                    'expedientes.d_judicial',
                    'expedientes.juez',
                    'expedientes.observacion',
                    'expedientes.especialidad',
                    'expedientes.e_procesal',
                    'expedientes.date_conclusion',
                    'expedientes.ubicacion',
                    'expedientes.motivo_conclusion',
                    'clientes.name',
                    'clientes.last_name',
                    'clientes.name_company',
                    'clientes.type_contact',
                    'clientes.ruc',
                    'clientes.email',
                    'clientes.phone',
                    'follow_ups.fecha_ingreso',
                    'follow_ups.fecha_resolucion'
                )
                ->leftJoin('follow_ups', function ($join) {
                    $join->on('expedientes.id', '=', 'follow_ups.id_exp')
                        ->where('follow_ups.id', '=', function ($query) {
                            $query->select(DB::raw('MAX(id)'))
                                ->from('follow_ups')
                                ->whereColumn('id_exp', 'expedientes.id');
                        });
                })
                ->orderBy('expedientes.id', 'desc')
                ->where('expedientes.id', $id)
                ->where('expedientes.code_company', $dataUser->code_company)
                ->get();

            //withQueryString() => mantener el query
            $movements = FollowUp::where('id_exp', $id)
                ->where('code_company', $dataUser->code_company)
                ->orderBy('id', 'desc')
                ->paginate(5)
                ->withQueryString();

            $notify = NotificacionSeguimiento::where('id_exp', $id)->get();

            $comments = CommentMovement::where('id_exp', $id)->where('code_company', $dataUser->code_company)->orderBy('date', 'asc')->get();

            // $groupStages = DB::table('work_flow_task_expedientes')
            //     ->select('id_workflow', 'id_workflow_stage', 'id_exp', 'nombre_etapa', 'nombre_flujo')
            //     ->where('id_exp', $id)
            //     ->groupBy('id_workflow', 'id_workflow_stage', 'id_exp', 'nombre_etapa', 'nombre_flujo')
            //     ->get();
            $groupStages = WorkFlowTaskExpediente::select('w1.*')
                ->from('work_flow_task_expedientes AS w1')
                ->join(DB::raw('(SELECT MAX(id) AS max_id, id_workflow_stage
                FROM work_flow_task_expedientes
                WHERE id_exp = ' . $id . '
                GROUP BY id_workflow_stage) AS max_ids'), function ($join) {
                    $join->on('w1.id', '=', 'max_ids.max_id')
                        ->on('w1.id_workflow_stage', '=', 'max_ids.id_workflow_stage');
                })
                ->where('w1.id_exp', $id)
                ->get();

            $stageCountEnProgreso = WorkFlowTaskExpediente::select('w1.*')
                ->from('work_flow_task_expedientes AS w1')
                ->join(DB::raw('(SELECT MAX(id) AS max_id, id_workflow_task
                FROM work_flow_task_expedientes
                WHERE id_exp = ' . $id . '
                AND estado = "En progreso"
                GROUP BY id_workflow_task) AS max_ids'), function ($join) {
                    $join->on('w1.id', '=', 'max_ids.max_id')
                        ->on('w1.id_workflow_task', '=', 'max_ids.id_workflow_task');
                })
                ->where('w1.id_exp', $id)
                ->get();


            // $stageCountEnProgreso = (object) $stageCountEnProgresoArray;

            $estadoFlujoCount = FlujoAsociadoExpediente::where('id_exp', $id)->where('table_pertenece', 'flujo')->count();
            $workFlowTaskExpediente = WorkFlowTaskExpediente::where('id_exp', $id)->get();

            $countAll = WorkFlowTaskExpediente::where('id_exp', $id)->count();
            $countCheck = WorkFlowTaskExpediente::where('id_exp', $id)->where('metadata', 'finalizado')->count();
            $countAllTask = TaskExpediente::where('id_exp', $id)->count();
            $countAllTaskCheck = TaskExpediente::where('id_exp', $id)->where('metadata', 'finalizado')->count();

            // TOTAL
            $sumAll = $countAll + $countAllTask;
            // TOTAL AVANZADO
            $sumAllCheck = $countCheck + $countAllTaskCheck;
            $TaskFinalizado = $countAllTaskCheck;
            $TaskFlujoFinalizado = $countCheck;

            $suggestion = SuggestionChatJudicial::where('code_company', Auth::user()->code_company)
                ->where('id_exp', $id)
                ->where('entidad', 'judicial')
                ->orderBy('id', 'asc')
                ->get();
        }


        return view('dashboard.sistema_expedientes.movimientosExpediente', compact(
            'id',
            'data',
            'movements',
            'notify',
            'comments',
            'workFlowTaskExpediente',
            'groupStages',
            'estadoFlujoCount',
            'sumAll',
            'sumAllCheck',
            'stageCountEnProgreso',
            'suggestion',
            'TaskFlujoFinalizado',
            'TaskFinalizado'
        ));
    }

    public function guardarVideo(Request $request)
    {
        // Obtén el Blob del video enviado desde el cliente
        $videoBlob = $request->file('video');
        $codeExp = $request->input('code-exp');

        // Genera un nombre único para el archivo WebM
        $nombreArchivo = Str::random(20) . '.webm';

        // Guarda el Blob en el almacenamiento de Laravel
        Storage::disk('public')->put('videos/' . $codeExp . '/' . $nombreArchivo, file_get_contents($videoBlob->getRealPath()));

        // Ruta del archivo de entrada y salida para FFMpeg
        $rutaEntrada = storage_path('app/public/videos/' . $codeExp . '/' . $nombreArchivo);
        $rutaSalida = storage_path('app/public/videos/' . $codeExp . '/' . Str::random(20) . '.webm');

        // Configurar las rutas a FFmpeg y FFProbe
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $ffmpegPath = storage_path('app/ffmpeg-4.4.1-win-64/ffmpeg.exe');
            $ffprobePath = storage_path('app/ffprobe-4.4.1-win-64/ffprobe.exe');
        } else {
            $ffmpegPath = '/usr/bin/ffmpeg'; // Ruta en sistemas Unix-like
            $ffprobePath = '/usr/bin/ffprobe'; // Ruta en sistemas Unix-like
        }

        // Utilizando PHP-FFMpeg para convertir el video
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => $ffmpegPath,
            'ffprobe.binaries' => $ffprobePath,
            'timeout' => 5400, // 1 hora y 30 minutos en segundos (1h * 60min * 60seg + 30seg) Tiempo de espera
        ]);
        // Convertir y guardar el video como WebM
        $video = $ffmpeg->open($rutaEntrada);
        $formatoWebM = new WebM();

        // Guardar el video como WebM
        $video->save($formatoWebM, $rutaSalida);


        // Obtiene la URL relativa a través del almacenamiento de Laravel
        $urlRelativa = '/storage/videos/' . $codeExp . '/' . $nombreArchivo;
        return response()->json(['url' => $urlRelativa]);
    }

    public function addFollowUp(Request $request)
    {
        // dd(request()->input("url-video") ,$request);

        $tipoSeguimiento = request()->input("type-segui");
        $tituloSeguimiento = request()->input("title-sigui");
        $fechaSeguimiento = request()->input("date-segui");
        $descripcionSeguimiento = request()->input("descrip-segui");
        $numeroExpediente = request()->input("code-exp");
        $idExpediente = request()->input("id-exp");
        $archivoAdjunto = request()->file("a-file");
        $urlVideo = request()->input("url-video");

        $datosExpediente = Expedientes::where('id', $idExpediente)->first();


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

        $ultimoRegistro = FollowUp::where('id_exp', $idExpediente)
            ->orderBy('n_seguimiento', 'desc')
            ->first();

        $datosSeguimiento = collect([
            "u_tipo" => $tipoSeguimiento,
            "u_title" => $tituloSeguimiento,
            "u_date" => $fechaSeguimiento,
            "u_descripcion" => $descripcionSeguimiento,
            "abog_virtual" => "no",
            "n_seguimiento" => $ultimoRegistro->n_seguimiento + 1,
            "id_exp" => $idExpediente,
            "code_company" => $datosExpediente->code_company,
            "code_user" => $datosExpediente->code_user,
            "metadata" => $url ?? null,
            "video" => $urlVideo ?? null
        ]);

        FollowUp::insert($datosSeguimiento->toArray());

        return redirect()->back()->with('success', 'Movimiento agregado correctamente');
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

        $exp = Expedientes::where('id', '=', $idExp)->get()->first();

        $codeCompany = auth()->user()->code_company;
        $codeUser = auth()->user()->code_user;

        $dataOld = FollowUp::where('id', $idM)->where('code_company', $codeCompany)->first();

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


        FollowUp::where('id_exp', '=', $idExp)
            ->where('id', '=', $idM)
            ->where('code_company', $codeCompany)
            ->update([
                'u_title' => $title,
                'u_date' => $date,
                'u_descripcion' => $descrip,
                'code_user' => $codeUser,
                'update_date' => now(),
                'metadata' => $url ?? $dataOld->metadata, //* Colocar el documento anterior
            ]);

        $value = '¡Movimiento del expediente ' . $exp->n_expediente . ' se actualizó correctamente!';

        return redirect()->back()->with('success', $value);
    }

    // GET DATOS EXPEDIENTE OBSERVACION
    public function datosExpedienteObs(Request $request)
    {
        $id = $_POST['id'];
        $dataExpediente = DB::table('follow_ups')
            ->join('expedientes', 'follow_ups.id_exp', '=', 'expedientes.id')
            ->join('clientes', 'expedientes.id_client', '=', 'clientes.id')
            ->select(
                'expedientes.id',
                'expedientes.n_expediente',
                'follow_ups.n_seguimiento',
                'follow_ups.obs_sumilla',
                'follow_ups.acto',
                'follow_ups.fecha_resolucion',
                'follow_ups.resolucion',
                'follow_ups.fojas',
                'follow_ups.type_notificacion',
                'follow_ups.proveido',
                'follow_ups.file',
                'clientes.name',
                'clientes.type_contact',
                'clientes.last_name',
                'clientes.name_company',
                'clientes.dni',
                'clientes.ruc',
            )
            ->where('follow_ups.id_exp', $id)
            ->get();

        return response($dataExpediente);
    }

    public function deleteFollowUp()
    {
        $id = request()->input('idM');
        $codeCompany = auth()->user()->code_company;
        $dataFollowUp = FollowUp::where('id', $id)->where('code_company', $codeCompany)->first();
        if ($dataFollowUp && $dataFollowUp->metadata !== null) {
            $borrar_url = str_replace('storage', 'public', $dataFollowUp->metadata);
            Storage::delete($borrar_url);
        }
        if ($dataFollowUp && $dataFollowUp->video !== null) {
            $borrar_url = str_replace('storage', 'public', $dataFollowUp->video);
            Storage::delete($borrar_url);
        }
        FollowUp::where('id', $id)
            ->where('code_company', $codeCompany)
            ->delete();
        return response()->json("Eliminado");
    }

    public function datosMovimiento()
    {
        $id = request()->input('idM');
        $codeCompany = auth()->user()->code_company;
        $data = FollowUp::where('id', $id)
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

        $movements = FollowUp::where(function ($query) use ($texto, $id, $codeCompany) {
            $query->where('id_exp', '=', $id)
                ->where('code_company', $codeCompany)
                ->where(function ($innerQuery) use ($texto) {
                    $innerQuery->where('u_descripcion', 'like', '%' . $texto . '%')
                        ->orWhere('u_title', 'like', '%' . $texto . '%')
                        ->orWhere('resolucion', 'like', '%' . $texto . '%')
                        ->orWhere('type_notificacion', 'like', '%' . $texto . '%')
                        ->orWhere('acto', 'like', '%' . $texto . '%')
                        ->orWhere('folios', 'like', '%' . $texto . '%')
                        ->orWhere('fojas', 'like', '%' . $texto . '%')
                        ->orWhere('proveido', 'like', '%' . $texto . '%')
                        ->orWhere('obs_sumilla', 'like', '%' . $texto . '%')
                        ->orWhere('descripcion', 'like', '%' . $texto . '%');
                });
        })
            ->orderBy('n_seguimiento', 'desc')
            ->get();


        $notify = NotificacionSeguimiento::where(function ($query) use ($texto, $id) {
            $query->where('id_exp', '=', $id)
                ->where(function ($innerQuery) use ($texto) {
                    $innerQuery->where('name', 'like', '%' . $texto . '%')
                        ->orWhere('destinatario', 'like', '%' . $texto . '%')
                        ->orWhere('anexos', 'like', '%' . $texto . '%')
                        ->orWhere('forma_entrega', 'like', '%' . $texto . '%');
                });
        })
            ->orderBy('id', 'desc')
            ->get();

        $comments = CommentMovement::where('id_exp', $id)->orderBy('date', 'asc')->get();


        return view('dashboard.sistema_expedientes.searchSeguimiento', compact('movements', 'notify', 'texto', 'comments'));
    }


    /*
    * **********************************
    *
    *       ENTIDAD DE EXPEDIENTE
    *
    ************************************* */

    public function getExpEntidad(Request $request)
    {
        // $data = Entidad::orderBy('id')->get();
        $data = Entidad::where('id', '<>', 7)
            ->where('id', '<>', 9)
            ->orderBy('id')
            ->get();
        return response()->json($data);
    }

    // ? no automatizado
    public function getExpEntidad2(Request $request)
    {
        // Filtra los resultados donde el id no sea igual a 2
        $data = Entidad::where('id', '<>', 2)
            ->where('id', '<>', 4)
            ->where('id', '<>', 5)
            ->where('id', '<>', 6)
            ->orderBy('id')
            ->get();
        return response()->json($data);
    }

    /*
    * ************************************
    *
    *       FILTRO DE EXPEDIENTE
    *
    ************************************* */

    public function getExpFiltro()
    {
        $data = FiltroExp::orderBy('distrito_judicial')->get();
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

        $idMovi = request()->input("idMovi");
        $idExp = request()->input("idExp");
        $idNoti = request()->input("idNoti");
        $idUser = Auth()->id();
        $comment = request()->input("comment");
        $date = date("Y-m-d H:i:s");
        $type = request()->input("type"); //principal o notificación

        $dataUser = User::where('id', $idUser)->get()->first();
        $existExp = Expedientes::where('code_company', '=', $dataUser->code_company)->get()->first();
        $existMovemment = FollowUp::where('code_company', '=', $dataUser->code_company)->get()->first();
        if ($dataUser && $existExp && $existMovemment) {
            $newData = [
                'comment' => $comment,
                'code_user' => $dataUser->code_user,
                'code_company' => $dataUser->code_company,
                'id_exp' => $idExp,
                'id_user' => $idUser,
                'id_follow_up' => $idMovi,
                'date' => $date,
                'type' => $type,
                'id_notify' => $idNoti == "" ? Null : $idNoti,
                'metadata' => Null,
            ];

            $insertedId = DB::table('comment_movements')->insertGetId($newData);
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
        $dataComment = CommentMovement::where('id', $id)->get()->first();
        if ($dataUser->code_company == $dataComment->code_company) {
            CommentMovement::where('id', $id)->delete();
            return response()->json('CommentDelete');
        }
        return response()->json('ErrorDelete');
    }
}
