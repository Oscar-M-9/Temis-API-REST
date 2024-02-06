<?php

namespace App\Http\Controllers;

use App\Mail\RegisterUser;
use App\Models\Ceos;
use App\Models\Company;
use App\Models\Formulario;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Logs;
use App\Models\Suscripcion;
use App\Models\TypeUser;
use App\Models\UserParte;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class UsuarioControllers extends Controller
{
    // protected $compactFlyer;

    // public function __construct()
    // {
    //     $this->compactFlyer = 'Flyer';
    // }

    /**
     * Obtener lista de usuarios
     *
     * @OA\Get (
     *     path="/api/listausuario",
     *     tags={"Usuario"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuarios",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="users", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Oscar"),
     *                     @OA\Property(property="lastname", type="string", example="Chavesta"),
     *                     @OA\Property(property="email", type="string", format="email", example="oc6781343@gmail.com"),
     *                     @OA\Property(property="email_verified_at", type="string", format="date-time", example=null),
     *                     @OA\Property(property="rol", type="string", example="1"),
     *                     @OA\Property(property="type_user", type="string", example="Abogado"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="api_token", type="string", example=null),
     *                     @OA\Property(property="active", type="integer", example=1),
     *                     @OA\Property(property="estado", type="string", example="fwL0KebxdwvyXygef7h17YUbZs2rGpSahTbVWUP6"),
     *                     @OA\Property(property="last_password_change", type="string", format="date-time", example=null),
     *                     @OA\Property(property="phone", type="string", example=null),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example=null),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-25T16:47:28.000000Z")
     *                 )
     *             ),
     *             @OA\Property(property="typeUser", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="type_user", type="string", example="Abogado"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example=null),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example=null)
     *                 ),
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=3),
     *                     @OA\Property(property="type_user", type="string", example="Asistente Legal"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example=null),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example=null)
     *                 ),
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=4),
     *                     @OA\Property(property="type_user", type="string", example="Secretario(a) Legal"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example=null),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example=null)
     *                 ),
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="type_user", type="string", example="Administrador Financiero"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example=null),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example=null)
     *                 )
     *             ),
     *             @OA\Property(property="totalUsers", type="integer", example=1),
     *             @OA\Property(property="limitUsers", type="integer", example=null)
     *         )
     *     )
     * )
     *
     */


    public function listausuario()
    {
        $Flyer = User::where('code_company', Auth::user()->code_company)->get();
        $typeUser = TypeUser::where('type_user', '<>', 'Super admin')->get();

        $dataCompany = Company::where('code_company', Auth::user()->code_company)->first();
        $dataSuscripcion = Suscripcion::where('id', $dataCompany->id_suscripcion)->first();
        $totalUsers = User::where('code_company', Auth::user()->code_company)->count();
        $limitUsers = $dataSuscripcion->limit_users;
        return response()->json([
            'status' => true,
            'users' => $Flyer,
            'typeUser' => $typeUser,
            'totalUsers' => $totalUsers,
            'limitUsers' => $limitUsers
        ], 200);
    }

    /**
     * Actualizar contraseña de usuario logueado
     *
     * @OA\Put(
     *     path="/api/update-password",
     *     tags={"Usuario"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", example="The id field is required"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contraseña actualizada con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contraseña actualizada con éxito"),
     *             @OA\Property(property="user", type="object",
     *                  @OA\Property(property="id", type="integer", example=7),
     *                  @OA\Property(property="name", type="string", example="example"),
     *                  @OA\Property(property="lastname", type="string", example="demo"),
     *                  @OA\Property(property="email", type="string", format="email", example="example@demo.com"),
     *                  @OA\Property(property="rol", type="string", example="1"),
     *                  @OA\Property(property="type_user", type="string", example="Abogado"),
     *                  @OA\Property(property="code_user", type="string", example="Temis-7"),
     *                  @OA\Property(property="code_company", type="string", example="example"),
     *                  @OA\Property(property="api_token", type="string", example=null),
     *                  @OA\Property(property="active", type="integer", example=1),
     *                  @OA\Property(property="estado", type="string", example=null),
     *                  @OA\Property(property="last_password_change", type="string", format="date-time", example=null),
     *                  @OA\Property(property="phone", type="string", example="987654123"),
     *                  @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-25T21:49:39.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-25T21:49:40.000000Z")
     *             ),
     *         )
     *     ),
     *    @OA\Response(
     *         response=422,
     *         description="La contraseña actual es incorrecta",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="La contraseña actual es incorrecta"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="actual", type="array",
     *                     @OA\Items(type="string", example="La contraseña actual es incorrecta")
     *                 ),
     *                 @OA\Property(property="nueva", type="array",
     *                     @OA\Items(type="string", example="La nueva contraseña coincide con la actual")
     *                 ),
     *             )
     *         )
     *     )
     * )
     */

    public function updatepassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nueva' => [
                'required',
                'string',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&.;:|()=¿¡+{}\/\[\]_,<>*-])[A-Za-z\d$@$!%*?&.;:|()=¿¡+{}\/\[\]_,<>*-]{8,}$/',
            ],
            'actual' => [
                'required',
                'string',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&.;:|()=¿¡+{}\/\[\]_,<>*-])[A-Za-z\d$@$!%*?&.;:|()=¿¡+{}\/\[\]_,<>*-]{8,}$/',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                'message' => 'Se produjo un error al actualizar la contraseña',
                'errors' => $validator->errors(),
            ], 422);
        }

        $id = Auth::user()->id;
        $nueva = request()->input('nueva');
        $actual = request()->input('actual');

        $valores = DB::table('users')->where('id', $id)->first();
        if (!Hash::check($actual, $valores->password)) {
            return response()->json([
                "status" => false,
                'message' => 'La contraseña actual es incorrecta',
                "errors" => [
                    "actual" => [
                        "La contraseña actual es incorrecta"
                    ]
                ]
            ], 422);
        }
        if ($actual != $nueva) { //son iguales, haz el cambio
            $update = DB::table('users')->where('id', $id)->update([
                'password' => Hash::make($nueva),
                'last_password_change' => now()
            ]);
            $user = User::find($id);
            return response()->json([
                "status" => true,
                'message' => 'Contraseña actualizada con éxito',
                "user" => $user
            ], 200);
        } else {
            // no cambies
            return response()->json([
                "status" => false,
                'message' => 'La nueva contraseña coincide con la actual',
                "errors" => [
                    "nueva" => [
                        "La nueva contraseña coincide con la actual"
                    ]
                ]
            ], 422);
        }
    }

    /**
     * Eliminar un usuario registrado en mi compañia
     *
     * @OA\Delete(
     *     path="/api/delete-user",
     *     tags={"Usuario"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", example="The id field is required"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario eliminado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="El usuario con el nombre 'name' ha sido eliminado exitosamente"),
     *             @OA\Property(property="user", type="object",
     *                  @OA\Property(property="id", type="integer", example=7),
     *                  @OA\Property(property="name", type="string", example="example"),
     *                  @OA\Property(property="lastname", type="string", example="demo"),
     *                  @OA\Property(property="email", type="string", format="email", example="example@demo.com"),
     *                  @OA\Property(property="rol", type="string", example="1"),
     *                  @OA\Property(property="type_user", type="string", example="Abogado"),
     *                  @OA\Property(property="code_user", type="string", example="Temis-7"),
     *                  @OA\Property(property="code_company", type="string", example="example"),
     *                  @OA\Property(property="api_token", type="string", example=null),
     *                  @OA\Property(property="active", type="integer", example=1),
     *                  @OA\Property(property="estado", type="string", example=null),
     *                  @OA\Property(property="last_password_change", type="string", format="date-time", example=null),
     *                  @OA\Property(property="phone", type="string", example="987654123"),
     *                  @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-25T21:49:39.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-25T21:49:40.000000Z")
     *             ),
     *         )
     *     ),
     *    @OA\Response(
     *         response=422,
     *         description="Se requiere el ID del usuario para proceder con la eliminación",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Se requiere el ID del usuario para proceder con la eliminación"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="id", type="array",
     *                     @OA\Items(type="string", example="The id field is required.")
     *                 ),
     *             )
     *         )
     *     ),
     *    @OA\Response(
     *         response=404,
     *         description="No se encontró al usuario",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se encontró al usuario"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="id", type="array",
     *                     @OA\Items(type="string", example="No se encontró un usuario con ese ID")
     *                 ),
     *             )
     *         )
     *     )
     * )
     */

    public function deleteuser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => [
                'required',
                'number',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                'message' => 'Se requiere el ID del usuario para proceder con la eliminación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $id = request()->input('id');
        $oldData = User::where('id', $id)->first();
        if ($oldData) {
            UserParte::where('code_user', $oldData->code_user)->where('code_company', Auth::user()->code_company)->delete();
            User::where('id', $id)->where('code_company', Auth::user()->code_company)->delete();

            return response()->json([
                "status" => true,
                'message' => 'El usuario con el nombre ' . $oldData->name . ' ha sido eliminado exitosamente',
                'user' => $oldData,
            ], 200);
        }
        return response()->json([
            "status" => false,
            'message' => 'No se encontró al usuario',
            "errors" => [
                "id" => [
                    "No se encontró un usuario con ese ID"
                ]
            ]
        ], 404);
    }
    protected function rules()
    {
        return [
            'name' => 'required|string|letterswithspace|max:255',
            'lastname' => 'required|string|letterswithspace|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'nueva' => 'required|string|min:8|complexPassword|confirmed',
        ];
    }
    protected function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'name.letterswithspace' => 'Ingrese solo letras y espacios',
            'name.max' => 'El nombre no puede ser mayor a 255 caracteres',
            'lastname.required' => 'El apellido es obligatorio',
            'lastname.letterswithspace' => 'Ingrese solo letras y espacios',
            'lastname.max' => 'El apellido no puede ser mayor a 255 caracteres',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'Ingrese un correo electrónico válido',
            'email.max' => 'El correo electrónico no puede ser mayor a 255 caracteres',
            'email.unique' => 'Este correo electrónico ya ha sido registrado',
            'nueva.required' => 'La contraseña es obligatoria',
            'nueva.min' => 'La contraseña debe tener al menos 8 caracteres',
            'nueva.complexPassword' => 'La contraseña debe contener al menos una letra mayúscula, una letra minúscula, un número y un símbolo',
        ];
    }


    public function adduser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|letterswithspace',
            'lastname' => 'required|string|letterswithspace',
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique('users'),
                'regex:/^[\w\d.-]+@[a-zA-Z]{3,}\.[a-zA-Z]{2,}$/',
            ],
            'phone' => 'required|string|number'
        ]);
        $errorMessages = [];

        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('email')) {
                $errorMessages[] = 'La dirección de correo electrónico ya está en uso.';
            }
            if ($errors->has('name')) {
                $errorMessages[] = 'El nombre proporcionados son incorrectos.';
            }
            if ($errors->has('lastname')) {
                $errorMessages[] = 'El apellido proporcionados son incorrectos.';
            }
            if ($errors->has('phone')) {
                $errorMessages[] = 'El teléfono proporcionado es incorrecto.';
            }
            return redirect()->route('usuarios.index')->with('error', implode(', ', $errorMessages));
        }

        $dataCompany = Company::where('code_company', Auth::user()->code_company)->first();
        $dataSuscripcion = Suscripcion::where('id', $dataCompany->id_suscripcion)->first();
        $totalUsers = User::where('code_company', Auth::user()->code_company)->count();
        $limitUsers = $dataSuscripcion->limit_users;

        // Verificar si ya alcanso el maximo
        if ($totalUsers >= $limitUsers && $limitUsers !== null) {
            return redirect()->route('usuarios.index')->with('error', "Error al crear usuario (Maximo alcanzado).");
        }

        $data = request()->all(); //arreglo
        $password = str_replace(" ", "", $data['name']) . '@' . date('Y');
        $google2fa = app('pragmarx.google2fa');
        $registration_data = $request->all();
        $registration_data["google2fa_secret"] = $google2fa->generateSecretKey();
        $request->session()->flash('registration_data', $registration_data);
        $QR_Image = $google2fa->getQRCodeInline(
            config('app.name'),
            $registration_data['email'],
            $registration_data['google2fa_secret']
        );
        $urlP = "https://" . Auth::user()->code_company . ".temisperu.com/home";
        $mailData = [
            'email' => $data['email'],
            'password' => $password,
            'loginWeb' => $urlP,
            'web' => config('app.url'),
            'ceoPage' => config('app.name'),
        ];
        $svgCode = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $QR_Image);
        $newData = [
            'name' => $data['name'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'phone' => $data['phone'],
            'rol' => 2,
            'google2fa_secret' => $registration_data["google2fa_secret"],
            'active' => true,
            'estado' => null,
            'type_user' => $data['type_user'],
            'last_password_change' => null,
        ];
        try {
            $user = User::create($newData);
            $userAuthData = User::where('id', '=', auth()->id())->get()->first();
            $codeUser = 'Temis-' . $user->id;
            $user_update = User::find($user->id);
            $user_update->code_user = $codeUser;
            $user_update->code_company = $userAuthData->code_company;
            $user_update->update();
            Mail::to($data['email'])->send(new RegisterUser($mailData, $svgCode));
            return redirect()->route('usuarios.index')->with('success', 'El usario ' . $data['name'] . ' se a registrado exitosamente');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return redirect()->route('usuarios.index')->with('error', "La dirección de correo electrónico ya está en uso.");
            } else {
                return redirect()->route('usuarios.index')->with('error', "Error al crear usuario.");
            }
        }
    }

    public function updateuser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|letterswithspace',
            'lastname' => 'required|string|letterswithspace',
            'email' => [
                'required',
                'string',
                'email',
                'regex:/^[\w\d.-]+@[a-zA-Z]+\.[a-zA-Z]{2,}$/',
            ],
            'phone' => 'required|string|number'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('email')) {
                $errorMessages[] = 'La dirección de correo electrónico ya está en uso.';
            }
            if ($errors->has('name')) {
                $errorMessages[] = 'El nombre proporcionados son incorrectos.';
            }
            if ($errors->has('lastname')) {
                $errorMessages[] = 'El apellido proporcionados son incorrectos.';
            }
            if ($errors->has('phone')) {
                $errorMessages[] = 'El teléfono proporcionado es incorrecto.';
            }
            return redirect()->route('usuarios.index')->with('error', implode(', ', $errorMessages));
        }
        $id = $request->input('id');
        $flyer_update = User::find($id);
        $flyer_update->name = $request->input('name', '');
        $flyer_update->lastname = $request->input('lastname', '');
        $flyer_update->email = $request->input('email', '');
        $flyer_update->phone = $request->input('phone', '');
        $flyer_update->active = $request->input('active', '');
        $flyer_update->rol = $request->input('admin', '');
        $flyer_update->type_user = $request->input('type_user', '');
        if ($flyer_update->active == 1) {
            $estado = true;
        } else {
            $estado = false;
        }
        if ($flyer_update->rol == '') {
            $flyer_update->rol = auth()->user()->rol;
        }
        if ($flyer_update->active == '') {
            $flyer_update->active = auth()->user()->active;
        }
        $count_rol = DB::table('users')->where('rol', '1')->count();
        if ($count_rol == 1 && $flyer_update == '2') {
            return redirect()->route('usuarios.index')->with('info', 'Debe quedar almenos un administrador');
        }

        try {
            $oldData = User::findOrFail($request->input('id'))->getOriginal();
            if ($flyer_update->code_company == Auth::user()->code_company) {
                $flyer_update->update();
            }
            $dataUser = User::where('id', $id)->first();
            UserParte::where('code_user', $dataUser->code_user)->where('code_company', Auth::user()->code_company)->update([
                'nombres' => $request->input('name', ''),
                'apellidos' => $request->input('lastname', ''),
                'email' => $request->input('email', ''),
                'rol' => $request->input('type_user', ''),
            ]);
            return redirect()->route('usuarios.index')->with('success', 'Usuario ' . $flyer_update->name . ' se a actualizado exitosamente');
        } catch (\Throwable $th) {
            return redirect()->route('usuarios.index')->with('error', "Error al actualizar Usuario");
        }
    }

    /* ******************************
    *                               *
    *       TIPO DE USUARIO         *
    *                               *
    ******************************* */
}
