<?php

namespace App\Http\Controllers\Auth;

use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use App\Models\Logs;
use App\Models\Suscripcion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

trait AuthenticatesUsers
{
    use RedirectsUsers, ThrottlesLogins;

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm(Request $request)
    {
        // $host = request()->getHttpHost();
        // $subdominio = strtok($host, '.');

        // return view('auth.login')->with([
        //     'subdominio' => $subdominio
        // ]);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    /**
     * Iniciar sesión de usuario
     *
     * @OA\Post (
     *     path="/api/auth/login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Credenciales de inicio de sesión",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="oc6781343@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="contraseña")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inicio de sesión exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Oscar"),
     *                 @OA\Property(property="lastname", type="string", example="Chavesta"),
     *                 @OA\Property(property="email", type="string", format="email", example="oc6781343@gmail.com"),
     *                 @OA\Property(property="rol", type="string", example="1"),
     *                 @OA\Property(property="type_user", type="string", example="Abogado"),
     *                 @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                 @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                 @OA\Property(property="api_token", type="string", example=null),
     *                 @OA\Property(property="active", type="integer", example=1),
     *                 @OA\Property(property="estado", type="string", example="fwL0KebxdwvyXygef7h17YUbZs2rGpSahTbVWUP6"),
     *                 @OA\Property(property="last_password_change", type="string", format="date-time", example=null),
     *                 @OA\Property(property="phone", type="string", example=null),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example=null),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-25T16:47:28.000000Z")
     *             ),
     *             @OA\Property(property="token", type="string", example="86|VnV1s7PIRHordrMZKlc4JAHjkqrBHzMfIJCdaLpG"),
     *             @OA\Property(property="dataCompany", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="desarrollo"),
     *                 @OA\Property(property="ruc", type="string", example=null),
     *                 @OA\Property(property="email", type="string", format="email", example=null),
     *                 @OA\Property(property="phone", type="string", example=null),
     *                 @OA\Property(property="logo", type="string", example=null),
     *                 @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                 @OA\Property(property="id_suscripcion", type="string", example="7"),
     *                 @OA\Property(property="code_user", type="string", example=null),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-16T01:51:12.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-27T23:46:36.000000Z")
     *             ),
     *             @OA\Property(property="dataSuscripcion", type="object",
     *                 @OA\Property(property="id", type="integer", example=7),
     *                 @OA\Property(property="price", type="integer", example=0),
     *                 @OA\Property(property="type_suscripcion", type="string", example="Perzonalizado"),
     *                 @OA\Property(property="dias_suscripcion", type="integer", example=365),
     *                 @OA\Property(property="accept_terms_and_conditions", type="string", example="yes"),
     *                 @OA\Property(property="current_period_start", type="string", format="date", example="2023-11-27"),
     *                 @OA\Property(property="current_period_end", type="string", format="date", example="2024-11-26"),
     *                 @OA\Property(property="cancel_at_period_end", type="string", format="date", example=null),
     *                 @OA\Property(property="cancel_at", type="string", format="date", example=null),
     *                 @OA\Property(property="ended_at", type="string", format="date", example=null),
     *                 @OA\Property(property="limit_credit", type="integer", example=200),
     *                 @OA\Property(property="limit_users", type="integer", example=null),
     *                 @OA\Property(property="limit_workflows", type="integer", example=null),
     *                 @OA\Property(property="access_judicial", type="string", example="yes"),
     *                 @OA\Property(property="access_indecopi", type="string", example="yes"),
     *                 @OA\Property(property="access_suprema", type="string", example="yes"),
     *                 @OA\Property(property="access_sinoe", type="string", example="yes"),
     *                 @OA\Property(property="access_legaltech", type="string", example="yes"),
     *                 @OA\Property(property="limit_judicial", type="integer", example=null),
     *                 @OA\Property(property="limit_indecopi", type="integer", example=null),
     *                 @OA\Property(property="limit_suprema", type="integer", example=null),
     *                 @OA\Property(property="limit_sinoe", type="integer", example=null),
     *                 @OA\Property(property="limit_credencial_sinoe", type="integer", example=null),
     *                 @OA\Property(property="metadata", type="string", example=null),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example=null),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-11T22:14:56.000000Z")
     *             )
     *         )
     *     )
     * )
     *
     */

    public function login(Request $request)
    {
        $this->validateLogin($request);
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (
            method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)
        ) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }
        if ($this->attemptLogin($request)) {
            // token
            $user = Auth::user();
            $token = $user->createToken($this->myTokenApp)->plainTextToken;
            // Verificación de su cuenta esta activa su suscripcion
            // Datos de la Company
            $dataCompany = Company::where("code_company", $user->code_company)->first();
            $dataSuscripcion = Suscripcion::where('id', $dataCompany->id_suscripcion)->first();
            // Verificando si la cuenta esta en demo

            if ($dataSuscripcion) {
                if ($dataSuscripcion->id == 6 || $dataSuscripcion->type_suscripcion == "Suspensión") {
                    Auth::logout();
                    return redirect()->route('suspended.account');
                }
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

                    if ($diasFaltantes < 0) {
                        // Si su suscripcion llego a su tiempo cambiar su estado de suscripcion a Suspensión
                        if ($dataCompany->id_suscripcion == 1) { //Su cuenta era demo
                            // Cambiamos el id de suscripcion de demo a Suspensión
                            Company::where('id', $dataCompany->id)
                                ->update([
                                    'id_suscripcion' => 6
                                ]);
                        }
                        if ($dataSuscripcion->id > 6) { //su cuenta es personalizado
                            // en caso de tener una suscripcion cambiar el tipo a Suspensión para no perder su tipo de plan
                            Company::where('id', $dataCompany->id)
                                ->update([
                                    'type_suscripcion' => 'Suspensión',
                                    'ended_at' => Carbon::now()->toDateTimeString()
                                ]);
                        }

                        Auth::logout();
                        return redirect()->route('suspended.account');
                    }
                }
            }

            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }
            return $this->sendLoginResponse($request, $user, $token, $dataCompany, $dataSuscripcion);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request),
            $request->boolean('remember')
        );
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendLoginResponse(Request $request, $user, $token, $dataCompany, $dataSuscripcion)
    {
        // $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            return $response;
        }
        return response()->json([
            "status" => true,
            "user" => $user,
            "token" => $token,
            "dataCompany" => $dataCompany,
            "dataSuscripcion" => $dataSuscripcion
        ], 200);
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // $userId = Auth::user()->id;
        // Auth::logoutOtherDevices($request->password);
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */

    /**
     * Cerrar sesión del usuario
     *
     * @OA\Post (
     *     path="/api/auth/logout",
     *     tags={"Authentication"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Cierre de sesión exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sesión cerrada exitosamente"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=7),
     *                 @OA\Property(property="name", type="string", example="example"),
     *                 @OA\Property(property="lastname", type="string", example="demo"),
     *                 @OA\Property(property="email", type="string", format="email", example="example@demo.com"),
     *                 @OA\Property(property="rol", type="string", example="1"),
     *                 @OA\Property(property="type_user", type="string", example="Abogado"),
     *                 @OA\Property(property="code_user", type="string", example="Temis-7"),
     *                 @OA\Property(property="code_company", type="string", example="example"),
     *                 @OA\Property(property="api_token", type="string", example=null),
     *                 @OA\Property(property="active", type="integer", example=1),
     *                 @OA\Property(property="estado", type="string", example=null),
     *                 @OA\Property(property="last_password_change", type="string", format="date-time", example=null),
     *                 @OA\Property(property="phone", type="string", example="987654123"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-25T21:49:39.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-25T21:49:40.000000Z")
     *             )
     *         )
     *     )
     * )
     *
     */

    public function logout(Request $request)
    {
        // Revocar todos los tokens asociados al usuario
        $user = auth()->user();
        // auth()->user()->tokens()->delete();
        $user->currentAccessToken()->delete();

        return response()->json([
            "status" => true,
            "message" => "Sesión cerrada exitosamente",
            "user" => $user,
        ], 200);
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        //
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
