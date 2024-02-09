<?php

use App\Http\Controllers\AlertasController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CalendarTemisController;
use App\Http\Controllers\CreditController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\ExpedientesController;
use App\Http\Controllers\EventSuggestionController;
use App\Http\Controllers\ExpedienteSinoeController;
use App\Http\Controllers\IndecopiController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SupremaController;
use App\Http\Controllers\UsuarioControllers;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/auth/register', [RegisterController::class, 'register']);
Route::post('/auth/login', [LoginController::class, 'login']);

// Route::get('forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
// Route::post('forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
// Route::get('reset-password/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
// Route::post('reset-password', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');




Route::middleware(['auth:sanctum'])->group(function () {
    //
    Route::post('/auth/logout', [LoginController::class, 'logout']);
    //
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index']);
    //
    // Notificaciones reporte
    Route::get('/notificacion-reportes', [ReporteController::class, 'notificacionReporte']);
    Route::put('/update-estado-history-movements/{id}', [ReporteController::class, 'updateEstadoHistoryMovements']);
    // Calendario
    Route::get('/calendar', [CalendarTemisController::class, 'view']);
    Route::get('/calendar-client/{id}', [CalendarTemisController::class, 'getCalendarClient']);
    Route::delete('/delete-calendar-event/{id}', [CalendarTemisController::class,  'deleteEventCalendar']);
    Route::post('/add-event-suggestion', [EventSuggestionController::class, 'addEvent']);

    //USUARIOS
    Route::get('/listausuario', [UsuarioControllers::class, 'listausuario']);
    Route::put('/update-password', [UsuarioControllers::class, 'updatepassword']);
    Route::delete('/delete-user', [UsuarioControllers::class, 'deleteuser']);
    // Route::post('adduser', [UsuarioControllers::class, 'adduser'])->name('usuarios.adduser');
    // Route::post('updateuser', [UsuarioControllers::class, 'updateuser'])->name('usuarios.update');

    //! SISTEMA EXPEDIENTES
    Route::get('/clientes', [App\Http\Controllers\dashboard\ClientesController::class, 'mostrarClientes']);
    //         Route::get('expedientes-reportes', [ExpReportesController::class, 'mostrarReportes'])->name('sistema_expedientes.expedientesReportes');

    //         // ? CLIENTE
    //         Route::post('addCliente', [App\Http\Controllers\dashboard\ClientesController::class, 'addCliente'])->name('sistema_expedientes.addCliente');
    //         Route::post('updateCliente', [App\Http\Controllers\dashboard\ClientesController::class, 'updateCliente'])->name('sistema_expedientes.updateCliente');
    //         Route::post('deleteCliente', [App\Http\Controllers\dashboard\ClientesController::class, 'deleteCliente'])->name('cliente.delete');
    //         Route::post('datos-cliente', [App\Http\Controllers\dashboard\ClientesController::class, 'datosCliente']);
    //         Route::post('datos-cliente2', [App\Http\Controllers\dashboard\ClientesController::class, 'datosCliente2']);


    // ? Expedientes
    Route::middleware(['verificarAccesoJudicial'])->group(function () {
        //             // CEJ judicial
        Route::get('/procesos-poder-judicial/{idClient}', [ExpedientesController::class, 'mostrarExpedientesCliente']);
        //             Route::post('registrar-expediente', [App\Http\Controllers\dashboard\ExpedientesController::class, 'addExpediente'])->name('sistema_expedientes.addExpediente');
        //             Route::post('update-expediente', [App\Http\Controllers\dashboard\ExpedientesController::class, 'updateExpediente'])->name('sistema_expedientes.updateExpediente');
        //             Route::post('deleteExpediente', [App\Http\Controllers\dashboard\ExpedientesController::class, 'deleteExpediente'])->name('expediente.delete');
        //             Route::post('get-data-expediente', [App\Http\Controllers\dashboard\ExpedientesController::class, 'datosExpediente']);

        //             // ? Movimientos
        //             Route::get('seguimientos', [ExpedientesController::class, 'viewSeguimiento'])->name('sistema_expedientes.viewSeguimiento');
        //             Route::post('add-seguimiento', [ExpedientesController::class, 'addFollowUp']);
        //             Route::post('/guardar-video', [App\Http\Controllers\dashboard\ExpedientesController::class, 'guardarVideo']);
        //             Route::post('update-seguimiento', [App\Http\Controllers\dashboard\ExpedientesController::class, 'updateFollowUp']);
        //             Route::post('delete-seguimiento', [App\Http\Controllers\dashboard\ExpedientesController::class, 'deleteFollowUp']);
        //             Route::post('get-data-movi', [App\Http\Controllers\dashboard\ExpedientesController::class, 'datosMovimiento']);
        //             Route::get('/seguimientos/search-seguimiento', [App\Http\Controllers\dashboard\ExpedientesController::class, 'searchSeguimiento'])->name('sistema_expedientes.searchSeguimiento');

        //             // TAREAS DEL PODER JUDICIAL
        //             Route::post('add-task-judicial', [TaskExpedienteController::class, 'addTaskJudicial']);
        //             Route::post('update-task-judicial', [TaskExpedienteController::class, 'updateTaskJudicial']);
        //             Route::post('delete-task-judicial', [TaskExpedienteController::class, 'deleteTaskJudicial']);
        //             Route::post('get-all-task-judicial', [TaskExpedienteController::class, 'getAllTaskJudicial']);

        //             Route::post('get-comment-task-judicial', [TaskExpedienteController::class, 'getComment']);
        //             Route::post('get-comment-task-flujo-judicial', [TaskExpedienteController::class, 'getCommentFlujo']);
        //             Route::get('/task/search-task-judicial', [TaskExpedienteController::class, 'searchTask'])->name('sistema_expedientes.searchTaskJudicial');
        //             Route::post('update-estado-comment-task-judicial', [TaskExpedienteController::class, 'updateStatusComment']);
        //             Route::post('update-estado-comment-task-flujo-judicial', [TaskExpedienteController::class, 'updateStatusCommentFlujo']);
        //             Route::post('save-comment-task-judicial', [TaskExpedienteController::class, 'saveComment']);
        //             Route::post('save-comment-task-flujo-judicial', [TaskExpedienteController::class, 'saveCommentFlujo']);
        //             Route::post('delete-comment-task-judicial', [TaskExpedienteController::class, 'deleteComment']);
        //             Route::post('delete-comment-task-flujo-judicial', [TaskExpedienteController::class, 'deleteCommentFlujo']);

        //             Route::post('save-comment', [App\Http\Controllers\dashboard\ExpedientesController::class, 'saveComment']);
        //             Route::post('delete-comment', [App\Http\Controllers\dashboard\ExpedientesController::class, 'deleteComment']);

        //             Route::post('add-economic', [EconomicExpensesController::class, 'addEconomic']);
        //             Route::post('get-economic', [EconomicExpensesController::class, 'getAllEconomic']);
        //             Route::post('get-money-economic', [EconomicExpensesController::class, 'getAllMoneyEconomic']);
        //             Route::post('edit-economic', [EconomicExpensesController::class, 'editEconomic']);
        //             Route::post('delete-economic', [EconomicExpensesController::class, 'deleteEconomic']);

        //             Route::post('upload-economic', [EconomicExpensesController::class, 'uploadAttachedFiles']);
        //             Route::post('remove-upload-economic', [EconomicExpensesController::class, 'removeuploadAttachedFiles']);

        //             Route::post('get-work-flows-in-expediente', [WorkFlowsController::class, 'getWorkFlowsExpedientes']);
        //             Route::post('add-work-flow-in-exp', [WorkFlowsController::class, 'addWorkFlowTaskExpediente']);
        //             Route::delete('destroy-work-flow-in-exp/{company}/{id}', [WorkFlowsController::class, 'destroyWorkFlowTaskExpediente']);

        //             // Chat con la IA
        //             Route::post('add-suggestion-chat', [SuggestionChatController::class, 'addHistoryChat']);
    });

    Route::middleware(['verificarAccesoSuprema'])->group(function () {
        // SUPREMA -
        Route::get('/procesos-corte-suprema/{idClient}', [SupremaController::class, 'mostrarExpedientesCliente']);
        //             Route::post('get-data-suprema', [SupremaController::class, 'datosExpediente']);
        //             Route::post('update-suprema', [SupremaController::class, 'updateExpediente'])->name('sistema_expedientes.suprema.updateExpediente');
        //             Route::post('deleteSuprema', [SupremaController::class, 'deleteExpediente'])->name('suprema.delete');

        //             Route::get('seguimientos-corte-suprema', [SupremaController::class, 'viewAcciones'])->name('sistema_expedientes.suprema.viewAcciones');
        //             Route::post('add-accion-suprema', [SupremaController::class, 'addFollowUp']);
        //             Route::post('update-accion-suprema', [SupremaController::class, 'updateFollowUp']);
        //             Route::post('get-data-accion-suprema', [SupremaController::class, 'datosAccion']);
        //             Route::post('delete-accion-suprema', [SupremaController::class, 'deleteAccion']);
        //             Route::get('/seguimientos-corte-suprema/search-seguimiento', [SupremaController::class, 'searchAccion'])->name('sistema_expedientes.suprema.searchSeguimiento');

        //             Route::post('save-comment-suprema', [SupremaController::class, 'saveComment']);
        //             Route::post('delete-comment-suprema', [SupremaController::class, 'deleteComment']);

        //             Route::post('add-economic-suprema', [EconomicExpensesSupremaController::class, 'addEconomic']);
        //             Route::post('get-economic-suprema', [EconomicExpensesSupremaController::class, 'getAllEconomic']);
        //             Route::post('get-money-economic-suprema', [EconomicExpensesSupremaController::class, 'getAllMoneyEconomic']);
        //             Route::post('edit-economic-suprema', [EconomicExpensesSupremaController::class, 'editEconomic']);
        //             Route::post('delete-economic-suprema', [EconomicExpensesSupremaController::class, 'deleteEconomic']);

        //             Route::post('upload-economic-suprema', [EconomicExpensesSupremaController::class, 'uploadAttachedFiles']);
        //             Route::post('remove-upload-economic-suprema', [EconomicExpensesSupremaController::class, 'removeuploadAttachedFiles']);

        //             Route::post('add-work-flow-in-exp-suprema', [WorkFlowsController::class, 'addWorkFlowTaskSuprema']);
        //             Route::delete('destroy-work-flow-in-suprema/{company}/{id}', [WorkFlowsController::class, 'destroyWorkFlowTaskSuprema']);
        //             Route::post('get-work-flows-in-suprema', [WorkFlowsController::class, 'getWorkFlowsSuprema']);


        //             // TAREAS DE CORTE SUPREMO
        //             Route::post('add-task-suprema', [TaskExpedienteController::class, 'addTaskSuprema']);
        //             Route::post('update-task-suprema', [TaskExpedienteController::class, 'updateTaskSuprema']);
        //             Route::post('delete-task-suprema', [TaskExpedienteController::class, 'deleteTaskSuprema']);
        //             Route::post('get-all-task-suprema', [TaskExpedienteController::class, 'getAllTaskSuprema']);

        //             Route::post('get-comment-task-suprema', [TaskExpedienteController::class, 'getCommentSuprema']);
        //             Route::post('get-comment-task-flujo-suprema', [TaskExpedienteController::class, 'getCommentFlujoSuprema']);
        //             Route::get('/task/search-task-suprema', [TaskExpedienteController::class, 'searchTaskSuprema'])->name('sistema_expedientes.suprema.searchTaskSuprema');
        //             Route::post('update-estado-comment-task-suprema', [TaskExpedienteController::class, 'updateStatusCommentSuprema']);
        //             Route::post('update-estado-comment-task-flujo-suprema', [TaskExpedienteController::class, 'updateStatusCommentFlujoSuprema']);
        //             Route::post('save-comment-task-suprema', [TaskExpedienteController::class, 'saveCommentSuprema']);
        //             Route::post('save-comment-task-flujo-suprema', [TaskExpedienteController::class, 'saveCommentFlujoSuprema']);
        //             Route::post('delete-comment-task-suprema', [TaskExpedienteController::class, 'deleteCommentSuprema']);
        //             Route::post('delete-comment-task-flujo-suprema', [TaskExpedienteController::class, 'deleteCommentFlujoSuprema']);
    });

    Route::middleware(['verificarAccesoIndecopi'])->group(function () {
        // INDECOPI -
        Route::get('procesos-indecopi/{idClient}', [IndecopiController::class, 'mostrarExpedientesCliente']);
        //             Route::post('get-data-indecopi', [IndecopiController::class, 'datosExpediente']);
        //             Route::post('update-indecopi', [IndecopiController::class, 'updateExpediente'])->name('sistema_expedientes.indecopi.updateExpediente');
        //             Route::post('deleteIndecopi', [IndecopiController::class, 'deleteExpediente'])->name('indecopi.delete');

        //             Route::get('acciones-realizadas', [IndecopiController::class, 'viewAcciones'])->name('sistema_expedientes.indecopi.viewAcciones');
        //             Route::post('add-accion-indecopi', [IndecopiController::class, 'addFollowUp']);
        //             Route::post('update-accion-indecopi', [IndecopiController::class, 'updateFollowUp']);
        //             Route::post('get-data-accion-indecopi', [IndecopiController::class, 'datosAccion']);
        //             Route::post('delete-accion-indecopi', [IndecopiController::class, 'deleteAccion']);
        //             Route::get('/acciones-realizadas/search-accion', [IndecopiController::class, 'searchAccion'])->name('sistema_expedientes.indecopi.searchSeguimiento');

        //             Route::post('save-comment-indecopi', [IndecopiController::class, 'saveComment']);
        //             Route::post('delete-comment-indecopi', [IndecopiController::class, 'deleteComment']);

        //             Route::post('add-economic-indecopi', [EconomicExpensesIndecopiController::class, 'addEconomic']);
        //             Route::post('get-economic-indecopi', [EconomicExpensesIndecopiController::class, 'getAllEconomic']);
        //             Route::post('get-money-economic-indecopi', [EconomicExpensesIndecopiController::class, 'getAllMoneyEconomic']);
        //             Route::post('edit-economic-indecopi', [EconomicExpensesIndecopiController::class, 'editEconomic']);
        //             Route::post('delete-economic-indecopi', [EconomicExpensesIndecopiController::class, 'deleteEconomic']);

        //             Route::post('upload-economic-indecopi', [EconomicExpensesIndecopiController::class, 'uploadAttachedFiles']);
        //             Route::post('remove-upload-economic-indecopi', [EconomicExpensesIndecopiController::class, 'removeuploadAttachedFiles']);

        //             Route::post('get-work-flows-in-indecopi', [WorkFlowsController::class, 'getWorkFlowsIndecopi']);
        //             Route::post('add-work-flow-in-exp-indecopi', [WorkFlowsController::class, 'addWorkFlowTaskIndecopi']);
        //             Route::delete('destroy-work-flow-in-indecopi/{company}/{id}', [WorkFlowsController::class, 'destroyWorkFlowTaskIndecopi']);

        //             // TAREAS DE INDECOPI
        //             Route::post('add-task-indecopi', [TaskIndecopiController::class, 'addTaskJudicial']);
        //             Route::post('update-task-indecopi', [TaskIndecopiController::class, 'updateTaskJudicial']);
        //             Route::post('delete-task-indecopi', [TaskIndecopiController::class, 'deleteTaskJudicial']);
        //             Route::post('get-all-task-indecopi', [TaskIndecopiController::class, 'getAllTaskJudicial']);

        //             Route::post('get-comment-task-indecopi', [TaskIndecopiController::class, 'getComment']);
        //             Route::get('/task/search-task-indecopi', [TaskIndecopiController::class, 'searchTask'])->name('sistema_expedientes.searchTaskIndecopi');
        //             Route::post('update-estado-comment-task-indecopi', [TaskIndecopiController::class, 'updateStatusComment']);
        //             Route::post('save-comment-task-indecopi', [TaskIndecopiController::class, 'saveComment']);
        //             Route::post('delete-comment-task-indecopi', [TaskIndecopiController::class, 'deleteComment']);

        //             Route::post('get-comment-task-flujo-indecopi', [TaskExpedienteController::class, 'getCommentFlujoIndecopi']);
        //             Route::post('update-estado-comment-task-flujo-indecopi', [TaskExpedienteController::class, 'updateStatusCommentFlujoIndecopi']);
        //             Route::post('save-comment-task-flujo-indecopi', [TaskExpedienteController::class, 'saveCommentFlujoIndecopi']);
        //             Route::post('delete-comment-task-flujo-indecopi', [TaskExpedienteController::class, 'deleteCommentFlujoIndecopi']);
    });


    Route::middleware(['verificarAccesoSinoe'])->group(function () {
        // SINOE .
        Route::get('procesos-sinoe/{idClient}', [ExpedienteSinoeController::class, 'mostrarExpedientesCliente']);
        //             Route::post('update-expediente-sinoe', [ExpedienteSinoeController::class, 'updateExpediente'])->name('sistema_expedientes.sinoe.updateExpediente');
        //             Route::post('deleteExpedienteSinoe', [ExpedienteSinoeController::class, 'deleteExpediente'])->name('expediente.sinoe.delete');
        //             Route::post('get-data-expediente-sinoe', [ExpedienteSinoeController::class, 'datosExpediente']);

        //             // ? DOCUMENTOS PRESENTADOS SINOE
        //             Route::post('add-documentos-presentados-sinoe', [DocumentosPresentadosSinoeController::class, 'addDocument']);
        //             Route::post('update-documentos-presentados-sinoe', [DocumentosPresentadosSinoeController::class, 'updateDocument']);
        //             Route::post('delete-documentos-presentados-sinoe', [DocumentosPresentadosSinoeController::class, 'deleteDocument']);
        //             Route::post('get-all-documentos-presentados-sinoe', [DocumentosPresentadosSinoeController::class, 'getAllDocument']);

        //             // ? Movimientos
        //             Route::get('seguimientos-sinoe', [ExpedienteSinoeController::class, 'viewSeguimiento'])->name('sistema_expedientes.sinoe.viewSeguimiento');
        //             Route::post('add-seguimiento-sinoe', [ExpedienteSinoeController::class, 'addFollowUp']);
        //             Route::post('update-seguimiento-sinoe', [ExpedienteSinoeController::class, 'updateFollowUp']);
        //             Route::post('delete-seguimiento-sinoe', [ExpedienteSinoeController::class, 'deleteFollowUp']);
        //             Route::post('get-data-movi-sinoe', [ExpedienteSinoeController::class, 'datosMovimiento']);
        //             Route::get('/seguimientos-sinoe/search-seguimiento', [ExpedienteSinoeController::class, 'searchSeguimiento'])->name('sistema_expedientes.sinoe.searchSeguimiento');

        //             // TAREAS DEL PODER SINOE
        //             Route::post('add-task-sinoe', [TaskSinoeController::class, 'addTaskSinoe']);
        //             Route::post('update-task-sinoe', [TaskSinoeController::class, 'updateTaskSinoe']);
        //             Route::post('delete-task-sinoe', [TaskSinoeController::class, 'deleteTaskSinoe']);
        //             Route::post('get-all-task-sinoe', [TaskSinoeController::class, 'getAllTaskSinoe']);

        //             Route::post('get-comment-task-sinoe', [TaskSinoeController::class, 'getComment']);
        //             Route::post('get-comment-task-flujo-sinoe', [TaskSinoeController::class, 'getCommentFlujoSinoe']);
        //             Route::get('/task/search-task-sinoe', [TaskSinoeController::class, 'searchTask'])->name('sistema_expedientes.sinoe.searchTaskJudicial');
        //             Route::post('update-estado-comment-task-sinoe', [TaskSinoeController::class, 'updateStatusComment']);
        //             Route::post('update-estado-comment-task-flujo-sinoe', [TaskSinoeController::class, 'updateStatusCommentFlujoSinoe']);
        //             Route::post('save-comment-task-sinoe', [TaskSinoeController::class, 'saveComment']);
        //             Route::post('save-comment-task-flujo-sinoe', [TaskSinoeController::class, 'saveCommentFlujoSinoe']);
        //             Route::post('delete-comment-task-sinoe', [TaskSinoeController::class, 'deleteComment']);
        //             Route::post('delete-comment-task-flujo-sinoe', [TaskSinoeController::class, 'deleteCommentFlujoSinoe']);


        //             Route::post('save-comment-sinoe', [ExpedienteSinoeController::class, 'saveComment']);
        //             Route::post('delete-comment-sinoe', [ExpedienteSinoeController::class, 'deleteComment']);

        //             Route::post('add-economic-sinoe', [EconomicExpensesSinoeController::class, 'addEconomic']);
        //             Route::post('get-economic-sinoe', [EconomicExpensesSinoeController::class, 'getAllEconomic']);
        //             Route::post('get-money-economic-sinoe', [EconomicExpensesSinoeController::class, 'getAllMoneyEconomic']);
        //             Route::post('edit-economic-sinoe', [EconomicExpensesSinoeController::class, 'editEconomic']);
        //             Route::post('delete-economic-sinoe', [EconomicExpensesSinoeController::class, 'deleteEconomic']);

        //             Route::post('upload-economic-sinoe', [EconomicExpensesSinoeController::class, 'uploadAttachedFiles']);
        //             Route::post('remove-upload-economic-sinoe', [EconomicExpensesSinoeController::class, 'removeuploadAttachedFiles']);

        //             Route::post('add-work-flow-in-exp-sinoe', [WorkFlowsController::class, 'addWorkFlowTaskSinoe']);
        //             Route::delete('destroy-work-flow-in-sinoe/{company}/{id}', [WorkFlowsController::class, 'destroyWorkFlowTaskSinoe']);
        //             Route::post('get-work-flows-in-sinoe', [WorkFlowsController::class, 'getWorkFlowsSinoe']);

        //             Route::post('delete-storage-sinoe-temp', [ExpedienteSinoeController::class, 'deleteStorageSinoeTemp']);

        //             // CREDENCIALES
        //             Route::get('credenciales-sinoe', [CredencialesSinoeController::class, 'credencialesSinoe'])->name('usuarios.credencialesSinoe');
        //             Route::post('add-credenciales', [CredencialesSinoeController::class, 'addCredenciales']);
        //             Route::post('update-credenciales', [CredencialesSinoeController::class, 'updateCredenciales']);
        //             Route::post('delete-credencial', [CredencialesSinoeController::class, 'deleteCredenciales']);
        //             Route::post('get-data-credencial', [CredencialesSinoeController::class, 'getDataCredencial']);
    });
});


// Route::middleware([EnsureSubdomainExist::class])->group(function () {


//     Route::group(['middleware' => 'auth:sanctum'], function () {









//         // * *******************************************
//         // * PROCESO PENAL
//         // PENAL .
//         Route::get('procesos-penal', [ExpedientePenalController::class, 'mostrarExpedientes'])->name('sistema_expedientes.penal.expedientesRegistroExpedientes');
//         Route::post('update-expediente-penal', [ExpedientePenalController::class, 'updateExpediente'])->name('sistema_expedientes.penal.updateExpediente');
//         Route::post('deleteExpedientePenal', [ExpedientePenalController::class, 'deleteExpediente'])->name('expediente.penal.delete');
//         Route::post('get-data-expediente-penal', [ExpedientePenalController::class, 'datosExpediente']);

//         // ? DOCUMENTOS PRESENTADOS PENAL
//         Route::post('add-documentos-presentados-penal', [DocumentosPresentadosSinoeController::class, 'addDocument']);
//         Route::post('update-documentos-presentados-penal', [DocumentosPresentadosSinoeController::class, 'updateDocument']);
//         Route::post('delete-documentos-presentados-penal', [DocumentosPresentadosSinoeController::class, 'deleteDocument']);
//         Route::post('get-all-documentos-presentados-penal', [DocumentosPresentadosSinoeController::class, 'getAllDocument']);

//         // ? Movimientos
//         Route::get('seguimientos-penal', [ExpedientePenalController::class, 'viewSeguimiento'])->name('sistema_expedientes.penal.viewSeguimiento');
//         Route::post('add-seguimiento-penal', [ExpedientePenalController::class, 'addFollowUp']);
//         Route::post('update-seguimiento-penal', [ExpedientePenalController::class, 'updateFollowUp']);
//         Route::post('delete-seguimiento-penal', [ExpedientePenalController::class, 'deleteFollowUp']);
//         Route::post('get-data-movi-penal', [ExpedientePenalController::class, 'datosMovimiento']);
//         Route::get('/seguimientos-penal/search-seguimiento', [ExpedientePenalController::class, 'searchSeguimiento'])->name('sistema_expedientes.penal.searchSeguimiento');

//         // TAREAS DEL PODER PENAL
//         Route::post('add-task-penal', [TaskPenalController::class, 'addTaskSinoe']);
//         Route::post('update-task-penal', [TaskPenalController::class, 'updateTaskSinoe']);
//         Route::post('delete-task-penal', [TaskPenalController::class, 'deleteTaskSinoe']);
//         Route::post('get-all-task-penal', [TaskPenalController::class, 'getAllTaskSinoe']);

//         Route::post('get-comment-task-penal', [TaskPenalController::class, 'getComment']);
//         Route::post('get-comment-task-flujo-penal', [TaskPenalController::class, 'getCommentFlujoSinoe']);
//         Route::get('/task/search-task-penal', [TaskPenalController::class, 'searchTask'])->name('sistema_expedientes.penal.searchTaskJudicial');
//         Route::post('update-estado-comment-task-penal', [TaskPenalController::class, 'updateStatusComment']);
//         Route::post('update-estado-comment-task-flujo-penal', [TaskPenalController::class, 'updateStatusCommentFlujoSinoe']);
//         Route::post('save-comment-task-penal', [TaskPenalController::class, 'saveComment']);
//         Route::post('save-comment-task-flujo-penal', [TaskPenalController::class, 'saveCommentFlujoSinoe']);
//         Route::post('delete-comment-task-penal', [TaskPenalController::class, 'deleteComment']);
//         Route::post('delete-comment-task-flujo-penal', [TaskPenalController::class, 'deleteCommentFlujoSinoe']);


//         Route::post('save-comment-penal', [ExpedientePenalController::class, 'saveComment']);
//         Route::post('delete-comment-penal', [ExpedientePenalController::class, 'deleteComment']);

//         Route::post('add-economic-penal', [EconomicExpensesPenalController::class, 'addEconomic']);
//         Route::post('get-economic-penal', [EconomicExpensesPenalController::class, 'getAllEconomic']);
//         Route::post('get-money-economic-penal', [EconomicExpensesPenalController::class, 'getAllMoneyEconomic']);
//         Route::post('edit-economic-penal', [EconomicExpensesPenalController::class, 'editEconomic']);
//         Route::post('delete-economic-penal', [EconomicExpensesPenalController::class, 'deleteEconomic']);

//         Route::post('upload-economic-penal', [EconomicExpensesPenalController::class, 'uploadAttachedFiles']);
//         Route::post('remove-upload-economic-penal', [EconomicExpensesPenalController::class, 'removeuploadAttachedFiles']);

//         Route::post('add-work-flow-in-exp-penal', [WorkFlowsController::class, 'addWorkFlowTaskPenal']);
//         Route::delete('destroy-work-flow-in-penal/{company}/{id}', [WorkFlowsController::class, 'destroyWorkFlowTaskPenal']);
//         Route::post('get-work-flows-in-penal', [WorkFlowsController::class, 'getWorkFlowsPenal']);



//         // * *******************************************

//         Route::middleware(['verificarAccesoLegalTech'])->group(function () {

//             // ? LEGALBOT
//             Route::get('legalbot/index', [LegalBotController::class, 'viewIndex'])->name('legalBot.index');
//             Route::get('legalbot/analisis-expediente', [LegalBotController::class, 'viewAnalisisExp'])->name('legalBot.analisis');
//             Route::get('legalbot/conocimiento', [LegalBotController::class, 'viewConocimiento'])->name('legalBot.conocimiento');
//             Route::get('legalbot/escrito_final', [LegalBotController::class, 'viewEscritoFinal'])->name('legalBot.escrito_final');
//             Route::get('legalbot/asistencia-legal-ia', [PromptsController::class, 'show'])->name('prompts');
//             Route::resource('legalbot/prompts', PromptsLibraryController::class)->names('prompt_libraries');
//             Route::resource('legalbot/generador', PromptsGeneratorController::class)->names('prompt_generator');

//             Route::post('update-credit-consumption-promts', [CreditController::class, 'updateCreditConsumptionPromts']);
//             // Route::post('update-credit-consumption-promts-generation', [CreditController::class, 'updateCreditConsumptionPromtsGeneration']);
//             Route::post('update-credit-consumption-writting', [CreditController::class, 'updateCreditConsumptionWritting']);
//             Route::post('update-credit-consumption-training-knowledge', [CreditController::class, 'updateCreditConsumptionTrainingKnowledge']);

//             // LEGAL TECH
//             Route::get('legal-tech', [\App\Http\Controllers\LegalTechController::class, 'view'])->name('legalTech.index');
//             Route::get('legal-tech-courses', [\App\Http\Controllers\LegalTechController::class, 'viewCourses'])->name('legalTech.courses');
//             Route::get('legal-tech-jurisprudencia', [\App\Http\Controllers\LegalTechController::class, 'viewJurisprudencia'])->name('legalTech.jurisprudencia');
//         });

//         // ? notifications to users in proceso
//         Route::post('get-user-notify', [NotificacionToUserExpController::class, 'getUserNotify']);
//         Route::post('notify-add-proceso', [NotificacionToUserExpController::class, 'addUserNotify']);
//         Route::post('notify-delete-proceso', [NotificacionToUserExpController::class, 'deleteUserNotify']);

//         // * FLUJOS DE TRABAJO
//         Route::get('workflows', [WorkFlowsController::class, 'index'])->name('sistema_expedientes.workflows.index');

//         Route::post('add-work-flows', [WorkFlowsController::class, 'addWorkFlows']);
//         Route::post('update-work-flows', [WorkFlowsController::class, 'updateWorkFlows']);
//         Route::post('delete-work-flows', [WorkFlowsController::class, 'deleteWorkFlows']);
//         Route::post('select-work-flows', [WorkFlowsController::class, 'selectWorkFlows']);

//         Route::post('get-work-flows-all', [WorkFlowsController::class, 'getWorkFlowsAll']);
//         Route::post('get-stage-form-work-flows', [WorkFlowsController::class, 'getStagesFromWorkFlow']);


//         Route::get('/workflows/{uid}/{idStage?}', [WorkFlowsController::class, 'selectWorkFlows'])->name('workflows.uid');

//         Route::post('add-work-flows-stage', [WorkFlowsController::class, 'addWorkFlowsStage']);
//         Route::post('update-work-flows-stage', [WorkFlowsController::class, 'updateWorkFlowsStage']);
//         Route::post('delete-work-flows-stage', [WorkFlowsController::class, 'deleteWorkFlowsStage']);
//         // Route::post('select-work-flows-stage', [WorkFlowsController::class, 'selectWorkFlowsStage']);

//         Route::post('add-work-flows-task', [WorkFlowsController::class, 'addWorkFlowsTask']);
//         Route::post('update-work-flows-task', [WorkFlowsController::class, 'updateWorkFlowsTask']);
//         Route::post('delete-work-flows-task', [WorkFlowsController::class, 'deleteWorkFlowsTask']);

//         Route::post('add-work-flows-transition', [WorkFlowsController::class, 'addWorkFlowsTransition']);
//         Route::post('update-work-flows-transition', [WorkFlowsController::class, 'updateWorkFlowsTransition']);
//         Route::post('delete-work-flows-transition', [WorkFlowsController::class, 'deleteWorkFlowsTransition']);

//         // UBIGEO
//         Route::post('get-states', [\App\Http\Controllers\UbigeoController::class, 'getStates']);
//         Route::post('get-cities', [\App\Http\Controllers\UbigeoController::class, 'getCities']);
//         Route::post('get-districts', [\App\Http\Controllers\UbigeoController::class, 'getDistricts']);
//         // Route::get('/autocomplete-search-query', [ExpedientesController::class, 'query']);
//     });

//     // });
// });














Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//BUSQUEDA DE EXPEDIENTE
// Route::get('AlertForEmail', [AlertasController::class, 'verificarRegistros']);

// ALL entidad expedientes
Route::get('/entidadExp', [ExpedientesController::class, 'getExpEntidad']);
Route::get('/entidadExp2', [ExpedientesController::class, 'getExpEntidad2']);

// ALL filtro expedientes
Route::get('/filtroExp', [ExpedientesController::class, 'getExpFiltro']);

// API - recopila expedientes del poder judicial en la tabla temporal para su verificacion de actualización
Route::get('/get-temp-exp-judicial', [AlertasController::class, 'getExpJudicial']);
Route::get('/get-temp-exp-indecopi', [AlertasController::class, 'getExpIndecopi']);
Route::get('/get-temp-exp-suprema', [AlertasController::class, 'getExpSuprema']);
Route::get('/get-temp-exp-sinoe', [AlertasController::class, 'getExpSinoe']);
Route::get('/get-temp-exp-sinoe-historial', [AlertasController::class, 'getExpSinoeDocumentos']);

// Creditos - uso del bot
Route::get('get-total-credit', [CreditController::class, 'getTotalCredit']);

// Calendario
// Route::get('get-data-calendar', [CalendarTemisController::class,  'getDataCalendar']);
