<?php

use App\Http\Controllers\Auth\LogoutOtherDevicesController;
use App\Http\Controllers\CalendarTemisController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\dashboard\ExpedientesController;
use App\Http\Controllers\CredencialesSinoeController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\dashboard\RegistroClientesController;
use App\Http\Controllers\DocumentosPresentadosSinoeController;
use App\Http\Controllers\EconomicExpensesController;
use App\Http\Controllers\EconomicExpensesIndecopiController;
use App\Http\Controllers\EconomicExpensesPenalController;
use App\Http\Controllers\EconomicExpensesSinoeController;
use App\Http\Controllers\EconomicExpensesSupremaController;
use App\Http\Controllers\EventSuggestionController;
use App\Http\Controllers\ExpedientePenalController;
use App\Http\Controllers\ExpedienteSinoeController;
use App\Http\Controllers\ExpReportesController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\IndecopiController;
use App\Http\Controllers\LegalBotController;
use App\Http\Controllers\NotificacionToUserExpController;
use App\Http\Controllers\PromptsController;
use App\Http\Controllers\PromptsGeneratorController;
use App\Http\Controllers\PromptsLibraryController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SuggestionChatController;
use App\Http\Controllers\SupremaController;
use App\Http\Controllers\TaskExpedienteController;
use App\Http\Controllers\TaskIndecopiController;
use App\Http\Controllers\TaskPenalController;
use App\Http\Controllers\TaskSinoeController;
use App\Http\Controllers\UsuarioControllers;
use App\Http\Controllers\WorkFlowsController;
use App\Http\Middleware\EnsureSubdomainExist;
use App\Models\ExpedienteSinoe;
use Illuminate\Support\Facades\Artisan;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/




// Route::get('/', function () {
//     // return view('bienvenida');
//     return redirect()->route('home');
//     // return view('auth.login');
// });


Route::get('storage-link', function () {
    Artisan::call('storage:link');
});

// ? Google calendar
// Route::get('/google/calendar', [GoogleCalendarController::class, 'redirectToGoogle'])->name('google.calendar');
// Route::get('/google/calendar/callback', [GoogleCalendarController::class, 'handleGoogleCallback']);
// Route::get('/google/calendar/callback/events', [TaskExpedienteController::class, 'handleGoogleCallbackEvents']);
// Route::get('/google/calendar/create', [GoogleCalendarController::class, 'createCalendar']);

// Route::get('/domain-notfound', [App\Http\Controllers\CompanyController::class, 'domainNotFound'])->name('domain-notfound');

// Route::get('suspended-account', [\App\Http\Controllers\SustpendedAccountController::class, 'viewSuspendedAccount'])->name('suspended.account');
// Route::get('no-access', [\App\Http\Controllers\SustpendedAccountController::class, 'viewNotAccess'])->name('notaccess.page');

// Route::middleware([EnsureSubdomainExist::class])->group(function () {

//     // Auth::routes();
//     Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
//     Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
//     Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
//     Route::get('forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
//     Route::post('forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
//     Route::get('reset-password/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
//     Route::post('reset-password', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

//     // Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
//     // Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);

//     // Route::get('/complete-registration', [App\Http\Controllers\Auth\RegisterController::class, 'completeRegistration'])->name('complete.registration');

//     Route::get('/password/expired', function () {
//         return view('auth.passwords.expired');
//     })->name('password.expired');



//     // 2fa middleware
//     // Route::middleware(['2fa'])->group(function () {

//     Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
//     // Route::post('/2fa', function () {
//     //     return redirect(route('home'));
//     // })->name('2fa');
//     // Route::get('/2fa', function () {
//     //     return redirect(route('home'));
//     // });

//     Route::group(['middleware' => 'auth:sanctum'], function () {


//         Route::middleware(["auth", "admin"])->group(function () {
//             Route::get('listausuario', [UsuarioControllers::class, 'listausuario'])->name('usuarios.index');
//         });

//         // Notificaciones reporte
//         Route::post('notificacion-reportes', [ReporteController::class, 'notificacionReporte']);
//         Route::post('update-estado-history-movements', [ReporteController::class, 'updateEstadoHistoryMovements']);

//         // Calendario
//         Route::get('calendar', [CalendarTemisController::class, 'view'])->name('sistema_expedientes.calendar.index');
//         Route::post('get-data-calendar', [CalendarTemisController::class,  'getDataCalendar']);
//         Route::post('delete-calendar-event', [CalendarTemisController::class,  'deleteEventCalendar']);

//         Route::post('add-event-suggestion', [EventSuggestionController::class, 'addEvent']);


//         //USUARIOS
//         // Route::get('listausuario', [UsuarioControllers::class, 'listausuario'])->name('usuarios.index');
//         Route::get('updatecontrasena', [UsuarioControllers::class, 'updatecontrasena'])->name('usuarios.password');
//         Route::post('updatepassword', [UsuarioControllers::class, 'updatepassword'])->name('usuarios.updatepassword');
//         Route::get('/complete-registration', [UsuarioControllers::class, 'completeRegistration'])->name('usuario.registration');


//         Route::post('deleteuser', [UsuarioControllers::class, 'deleteuser'])->name('usuarios.delete');
//         Route::post('adduser', [UsuarioControllers::class, 'adduser'])->name('usuarios.adduser');
//         Route::post('updateuser', [UsuarioControllers::class, 'updateuser'])->name('usuarios.update');

//         //! SISTEMA EXPEDIENTES
//         // Route::get('expedientes-Home', [ExpedientesController::class, 'mostrarExpedientes'])->name('sistema_expedientes.expedientesHome');
//         // Route::get('alertas', [AlertasController::class, 'mostrarAlertas'])->name('sistema_expedientes.expedientesAlertas');
//         Route::get('clientes', [App\Http\Controllers\dashboard\RegistroClientesController::class, 'mostrarClientes'])->name('sistema_expedientes.expedientesRegistroClientes');
//         Route::get('expedientes-reportes', [ExpReportesController::class, 'mostrarReportes'])->name('sistema_expedientes.expedientesReportes');

//         // ? CLIENTE
//         Route::post('addCliente', [App\Http\Controllers\dashboard\RegistroClientesController::class, 'addCliente'])->name('sistema_expedientes.addCliente');
//         Route::post('updateCliente', [App\Http\Controllers\dashboard\RegistroClientesController::class, 'updateCliente'])->name('sistema_expedientes.updateCliente');
//         Route::post('deleteCliente', [App\Http\Controllers\dashboard\RegistroClientesController::class, 'deleteCliente'])->name('cliente.delete');
//         Route::post('datos-cliente', [App\Http\Controllers\dashboard\RegistroClientesController::class, 'datosCliente']);
//         Route::post('datos-cliente2', [App\Http\Controllers\dashboard\RegistroClientesController::class, 'datosCliente2']);

//         Route::middleware(['verificarAccesoJudicial'])->group(function () {
//             // CEJ judicial
//             Route::get('procesos-poder-judicial', [App\Http\Controllers\dashboard\ExpedientesController::class, 'mostrarExpedientes'])->name('sistema_expedientes.expedientesRegistroExpedientes');
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
//         });

//         Route::middleware(['verificarAccesoSuprema'])->group(function () {
//             // SUPREMA -
//             Route::get('procesos-corte-suprema', [SupremaController::class, 'mostrarExpedientes'])->name('sistema_expedientes.suprema.expedientesRegistroExpedientes');
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
//         });


//         Route::middleware(['verificarAccesoIndecopi'])->group(function () {
//             // INDECOPI -
//             Route::get('procesos-indecopi', [IndecopiController::class, 'mostrarExpedientes'])->name('sistema_expedientes.indecopi.expedientesRegistroExpedientes');
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
//         });


//         Route::middleware(['verificarAccesoSinoe'])->group(function () {
//             // SINOE .
//             Route::get('procesos-sinoe', [ExpedienteSinoeController::class, 'mostrarExpedientes'])->name('sistema_expedientes.sinoe.expedientesRegistroExpedientes');
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
//         });

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

// Busqueda de expediente
// Route::get('/busqueda-expediente', function () {
//     return view('expSearch.expSearch');
// });
