<?php

namespace App\Http\Controllers;

use App\Models\AccountGoogleCalendar;
use App\Models\User;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Illuminate\Http\Request;
use Google\Service\Calendar\Calendar as Google_Service_Calendar_Calendar;
use Google\Service\Calendar as Google_Service_Calendar;

class GoogleCalendarController extends Controller
{
    // protected $client;

    // public function __construct()
    // {
    //     $clientSecretPath = storage_path('app/google-calendar/client_secret.json');
    //     $this->client = new GoogleClient();
    //     $this->client->setAuthConfig($clientSecretPath);
    //     $this->client->setAccessType('offline');
    //     $this->client->setApprovalPrompt('force');
    //     $this->client->addScope([
    //         Calendar::CALENDAR,
    //         Calendar::CALENDAR_EVENTS
    //     ]);
    //     $this->client->setRedirectUri(url('/google/calendar/callback'));
    // }

    // public function redirectToGoogle()
    // {
    //     $idUser = request()->input("user");
    //     session(['user' => $idUser]);
    //     $authUrl = $this->client->createAuthUrl();
    //     // dd($authUrl);
    //     return redirect()->away($authUrl);
    // }

    // public function handleGoogleCallback(Request $request)
    // {
    //     if ($request->has('code')) {
    //         $token = $this->client->fetchAccessTokenWithAuthCode($request->code);
    //         // Guardar el token en la sesión
    //         session(['google_access_token' => $token]);

    //         return redirect('/google/calendar/create');
    //     }
    // }

    // public function createCalendar()
    // {
    //     // Recuperar el token de la sesión
    //     $token = session('google_access_token');
    //     // dd($token);
    //     $idUser = session('user');

    //     $dataUser = User::where('id', $idUser)->first();

    //     if ($dataUser) {

    //         // Configurar el cliente con el token de acceso de la sesión
    //         $this->client->setAccessToken($token);

    //         $service = new \Google\Service\Calendar($this->client);

    //         $dataAccountCalendar = AccountGoogleCalendar::where('id_user', $idUser)->first();

    //         $calendarList = $service->calendarList->listCalendarList();
    //         if ($dataAccountCalendar) {
    //             $calendarExists = false;
    //             // Verificar si el calendario existe
    //             foreach ($calendarList->getItems() as $calendar) {
    //                 if ($calendar->getId() === $dataAccountCalendar->id_calendar) {
    //                     $calendarExists = true;
    //                     break;
    //                 }
    //             }

    //             // Si el calendario no existe, crearlo
    //             if (!$calendarExists) {
    //                 $newCalendar = new Google_Service_Calendar_Calendar();
    //                 $newCalendar->setSummary('Temis');
    //                 // Otros detalles del calendario si es necesario
    //                 $createdCalendar = $service->calendars->insert($newCalendar);
    //                 $calendarId = $createdCalendar->getId();
    //             } else {
    //                 $calendarId = $dataAccountCalendar->id_calendar;
    //             }
    //         }

    //         // Definir el correo del usuario al que le otorgarás acceso al calendario
    //         $userEmail = config('app.iamcalendar'); // Reemplaza con el correo del usuario

    //         // Crear el objeto de permisos para agregar al calendario
    //         $rule = new \Google\Service\Calendar\AclRule([
    //             'scope' => [
    //                 'type' => 'user',
    //                 'value' => $userEmail,
    //             ],
    //             'role' => 'owner', // Puedes especificar el nivel de permisos: owner, writer, reader, etc.
    //         ]);

    //         // Agregar permisos al calendario para el usuario específico
    //         $service->acl->insert($calendarId, $rule);

    //         // Creando al usuario o actualizarlo - datos de google calendar
    //         if ($dataAccountCalendar) {
    //             AccountGoogleCalendar::where('id', $dataAccountCalendar->id)->update([
    //                 'access_token' => $token["access_token"],
    //                 'expires_in' => $token["expires_in"],
    //                 'refresh_token' => $token["refresh_token"] ?? $dataAccountCalendar->refresh_token,
    //                 'scope' => $token["scope"],
    //                 'token_type' => $token["token_type"],
    //                 'created' => $token["created"],
    //                 'id_calendar' => $calendarId,
    //                 'iam_email' => $userEmail,
    //                 'code_user' => $dataUser->code_user,
    //                 'code_company' => $dataUser->code_company,
    //                 'metadata' => $token,
    //             ]);
    //         } else {
    //             AccountGoogleCalendar::create([
    //                 'access_token' => $token["access_token"],
    //                 'expires_in' => $token["expires_in"],
    //                 'refresh_token' => $token["refresh_token"],
    //                 'scope' => $token["scope"],
    //                 'token_type' => $token["token_type"],
    //                 'created' => $token["created"],
    //                 'id_calendar' => $calendarId,
    //                 'iam_email' => $userEmail,
    //                 'id_user' => $idUser,
    //                 'code_user' => $dataUser->code_user,
    //                 'code_company' => $dataUser->code_company,
    //                 'metadata' => $token,
    //             ]);
    //         }
    //         // dd($this->client, $service);

    //         return view('googleCalendar.finish');
    //     } else {
    //         return view('googleCalendar.error');
    //     }
    // }
}
