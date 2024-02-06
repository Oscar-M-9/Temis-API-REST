<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Models\Companies;
use App\Models\Company;
use App\Models\Credit;
use App\Models\Credits;
use App\Models\Suscripcion;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    // protected $redirectTo = '/success';

    private $myTokenApp;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
        $this->myTokenApp = "legaltech";
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'code_company' => ['required', 'alpha_dash', 'max:100'],
            'phone' => ['required', 'numeric']
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        Company::create([
            'name' => $data['code_company'],
            'code_company' => $data['code_company'],
            'id_suscripcion' => 1
        ]);

        $idCreatedUser = User::create([
            'name' => $data['name'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'rol' => 1,
            'type_user' => 'Abogado',
            'google2fa_secret' => '', //$data['google2fa_secret'],
            'active' => true,
            'estado' => null,
            'last_password_change' => null,
            'code_company' => $data['code_company'],
            'phone' => $data['phone']
        ])->id;

        $createdUser = User::find($idCreatedUser);
        $createdUser->code_user = 'Temis-' . $idCreatedUser;
        $createdUser->save();

        $mytime = Carbon::now();
        $date_end = $mytime->copy()->addDays(30);

        $creditos = Credit::create([
            'used_suggestions' => 0,
            'used_writtings' => 0,
            'used_training_knowledge' => 0,
            'used_prompts' => 0,
            'total' => 0,
            'code_company' => $data['code_company'],
            'current_period_start' => $mytime,
            'current_period_end' => $date_end,
            'used_bot' => 0,
            'used_chatpdf' => 0
        ]);

        return $createdUser;
    }

    /**
     * Registrar un nuevo usuario
     *
     * @OA\Post (
     *     path="/api/auth/register",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos para el registro de un nuevo usuario",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="example"),
     *             @OA\Property(property="lastname", type="string", example="demo"),
     *             @OA\Property(property="email", type="string", format="email", example="example@demo.com"),
     *             @OA\Property(property="password", type="string", format="password", example="contraseña"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="contraseña_confirmacion"),
     *             @OA\Property(property="rol", type="string", example="1"),
     *             @OA\Property(property="type_user", type="string", example="Abogado"),
     *             @OA\Property(property="phone", type="string", example="987654123"),
     *             @OA\Property(property="code_company", type="string", example="example")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
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
     *             ),
     *             @OA\Property(property="token", type="string", example="88|nHBu8lhsPqctao87Ta0I7frTBpd4j9GzJYQgwVpj"),
     *             @OA\Property(property="dataCompany", type="object",
     *                 @OA\Property(property="id", type="integer", example=7),
     *                 @OA\Property(property="name", type="string", example="example"),
     *                 @OA\Property(property="ruc", type="string", example=null),
     *                 @OA\Property(property="email", type="string", format="email", example=null),
     *                 @OA\Property(property="phone", type="string", example=null),
     *                 @OA\Property(property="logo", type="string", example=null),
     *                 @OA\Property(property="code_company", type="string", example="example"),
     *                 @OA\Property(property="id_suscripcion", type="string", example="1"),
     *                 @OA\Property(property="code_user", type="string", example=null),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-25T21:49:39.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-25T21:49:39.000000Z")
     *             ),
     *             @OA\Property(property="dataSuscripcion", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="price", type="integer", example=0),
     *                 @OA\Property(property="type_suscripcion", type="string", example="Demo"),
     *                 @OA\Property(property="dias_suscripcion", type="integer", example=15),
     *                 @OA\Property(property="accept_terms_and_conditions", type="string", example="yes"),
     *                 @OA\Property(property="current_period_start", type="string", format="date", example=null),
     *                 @OA\Property(property="current_period_end", type="string", format="date", example=null),
     *                 @OA\Property(property="cancel_at_period_end", type="string", format="date", example=null),
     *                 @OA\Property(property="cancel_at", type="string", format="date", example=null),
     *                 @OA\Property(property="ended_at", type="string", format="date", example=null),
     *                 @OA\Property(property="limit_credit", type="integer", example=25),
     *                 @OA\Property(property="limit_users", type="integer", example=3),
     *                 @OA\Property(property="limit_workflows", type="integer", example=25),
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
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example=null)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="La dirección de correo electrónico ya está asociada a una cuenta",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="La dirección de correo electrónico ya está asociada a una cuenta. Inicie sesión para acceder a su cuenta"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array",
     *                     @OA\Items(type="string", example="The email has already been taken.")
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     */

    public function register(Request $request)
    {
        $validator = $this->validator($request->all());
        // Unprocessable Entity
        if ($validator->fails() && $validator->errors()->has('email')) {
            return response()->json([
                "status" => false,
                'message' => 'La dirección de correo electrónico ya está asociada a una cuenta. Inicie sesión para acceder a su cuenta',
                'errors' => $validator->errors(),
            ], 422);
        }
        $company = Company::where('code_company', '=', $request->input('code_company'))->get();

        if (count($company) > 0) {
            return response()->json([
                "status" => false,
                "message" => "Ya existe una compañía registrada con ese nombre",
                "errors" => [
                    "company" => [
                        "Ya existe una compañía registrada con ese nombre"
                    ]
                ]
            ], 422);
        }

        event(new Registered($user = $this->create($request->all())));

        $dataCompany = Company::where("code_company", $user->code_company)->first();
        $dataSuscripcion = Suscripcion::where('id', $dataCompany->id_suscripcion)->first();

        $newLogin = $this->guard()->login($user);
        return response()->json([
            "status" => true,
            "user" => $user,
            "token" => $user->createToken($this->myTokenApp)->plainTextToken,
            "dataCompany" => $dataCompany,
            "dataSuscripcion" => $dataSuscripcion
        ], 200);

        // if ($response = $this->registered($request, $user)) {
        //     redirect($this->redirectTo(TRUE));
        // }

    }
}
