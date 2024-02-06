<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\AlertCliente;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Credenciales;
use App\Models\Logs;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClientesController extends Controller
{

    /**
     * Obtener lista de clientes
     *
     * @OA\Get(
     *     path="/api/clientes",
     *     tags={"Clientes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de clientes",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Clientes"),
     *             @OA\Property(property="clientes", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="type_contact", type="string", example="Empresa"),
     *                     @OA\Property(property="name", type="string", example=null),
     *                     @OA\Property(property="last_name", type="string", example=null),
     *                     @OA\Property(property="dni", type="string", example=null),
     *                     @OA\Property(property="birthdate", type="string", example=null),
     *                     @OA\Property(property="company", type="string", example=null),
     *                     @OA\Property(property="name_company", type="string", example="demo"),
     *                     @OA\Property(property="ruc", type="string", example="1234568790875746"),
     *                     @OA\Property(property="email", type="string", example="{'email':'alnuawd@sagssdg.adgs','type_email':'Trabajo','email2':null,'type_email2':'Trabajo'}"),
     *                     @OA\Property(property="phone", type="string", example="{'phone':'2354325','type_phone':'Trabajo','phone2':null,'type_phone2':'Trabajo'}"),
     *                     @OA\Property(property="address", type="string", example="{'country':'Perú','departamento':'20','provincia':'152','distrito':'1534','street':'qwetryuio'}"),
     *                     @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                     @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                     @OA\Property(property="created_at", type="string", example=null),
     *                     @OA\Property(property="updated_at", type="string", example=null)
     *                 )
     *             )
     *         )
     *     )
     * )
     */


    public function mostrarClientes()
    {
        $clientes = Cliente::where("code_company", Auth::user()->code_company)->get();
        // // CREDENCIALES CREADAS POR EL USUARIO
        // $credencialUsuario = Credenciales::where('code_user', Auth::user()->code_user)->get();
        // // CREDENCIALES COMPARTIDAS CON EL USUARIO
        // $credencialCompartida = Credenciales::where('compartida', 0)->where('code_company', Auth::user()->code_company)->whereRaw('JSON_CONTAINS(metadata, ?)', ['"' . Auth::user()->id . '"'])
        //     ->get();
        // // CREDENCIALES COMPARTIDAS CON TODOS LOS USUARIOS
        // $credencialCompartidaAll = Credenciales::where('compartida', 1)->where('code_company', Auth::user()->code_company)->get();

        return response()->json(
            [
                'status' => true,
                'message' => "Clientes",
                'clientes' => $clientes,
                // 'credencialUsuario' => $credencialUsuario,
                // 'credencialCompartida' => $credencialCompartida,
                // 'credencialCompartidaAll' => $credencialCompartidaAll,
            ]
        );
    }

    // DATOS CLIENTE
    public function datosCliente()
    {
        $id = $_POST['id'];
        $datosCliente = Cliente::where('id', '=', $id)->get()->first();
        return response()->json($datosCliente);
    }
    public function datosCliente2()
    {
        $id = $_POST['id'];
        $datosCliente = Cliente::where('id', '=', $id)->get()->first();
        return response()->json($datosCliente);
    }

    // DELETE
    public function deleteCliente()
    {
        $id = $_POST['id'];
        $datosCli = Cliente::where('id', '=', $id)->get()->first();
        if ($datosCli->type_contact == 'Persona') {
            $nameInMessage = $datosCli->name . ', ' . $datosCli->last_name;
        }
        if ($datosCli->type_contact == 'Empresa') {
            $nameInMessage = $datosCli->name_company;
        }
        Cliente::destroy($id);
        $currentDateTime = Carbon::now();
        $mysqlDateTime = $currentDateTime->format('Y-m-d H:i:s');
        // Alert::insert([
        //     'date_time'=> $mysqlDateTime,
        //     'message' =>"Se eliminó el cliente de tipo ". strtoupper($datosCli->type_contact) ." con nombre ". strtoupper($nameInMessage),
        //     'alert' => 'danger',
        //     'type' => 'cliente',
        //     'id_cli' => $id,
        // ]);
        return response()->json("Eliminado");
    }

    // REGISTRAR
    public function  addCliente(Request $request)
    {
        // dd($request);

        $datosCliente = request()->all();
        $dataUser = User::where('id', Auth::id())->get()->first();
        $typeClient = $datosCliente["type-client"];
        if ($typeClient == 'Persona') {
            $name = $datosCliente["name"];
            $lastName = $datosCliente["last-name"];
            $dni = $datosCliente["dni"];
            $birthdate = $datosCliente["birthdate"];
            $company = $datosCliente["company"];
        } else {
            $nameCompany = $datosCliente["name-company"];
            $ruc = $datosCliente["ruc"];
        }
        $email = $datosCliente["email"];
        $typeEmail = $datosCliente["type-email"];
        $email2 = $datosCliente["email2"];
        $typeEmail2 = $datosCliente["type-email2"];
        $phone = $datosCliente["phone"];
        $typePhone = $datosCliente["type-phone"];
        $phone2 = $datosCliente["phone2"];
        $typePhone2 = $datosCliente["type-phone2"];
        $country = $datosCliente["country"];
        $departamento = $datosCliente["selectDepartamento"];
        $provincia = $datosCliente["selectProvincia"];
        $distrito = $datosCliente["selectDistrito"];
        $street = $datosCliente["street"];

        // Cliente
        $jsonEmail = ['email' => $email, 'type_email' => $typeEmail, 'email2' => $email2, 'type_email2' => $typeEmail2];
        $jsonPhone = ['phone' => $phone, 'type_phone' => $typePhone, 'phone2' => $phone2, 'type_phone2' => $typePhone2];
        $jsonAddress = [
            'country' => $country,
            'departamento' => $departamento,
            'provincia' => $provincia,
            'distrito' => $distrito,
            'street' => $street,
        ];
        $nameInMessage = '';

        if ($typeClient == 'Persona') {
            $nameInMessage = $name . ', ' . $lastName;
            $newData = [
                'type_contact' => $typeClient,
                'name' => $name,
                'last_name' => $lastName,
                'dni' => $dni,
                'birthdate' => $birthdate,
                'company' =>  $company,
                'code_user' =>  $dataUser->code_user,
                'code_company' =>  $dataUser->code_company,
                'email' => json_encode($jsonEmail),
                'phone' => json_encode($jsonPhone),
                'address' => json_encode($jsonAddress),
            ];
        } else {
            $nameInMessage = $nameCompany;
            $newData = [
                'type_contact' => $typeClient,
                'name_company' =>  $nameCompany,
                'ruc' => $ruc,
                'code_user' =>  $dataUser->code_user,
                'code_company' =>  $dataUser->code_company,
                'email' => json_encode($jsonEmail),
                'phone' => json_encode($jsonPhone),
                'address' => json_encode($jsonAddress),
            ];
        }
        // Cliente::insert($newData);
        $dataId = DB::table('clientes')->insertGetId($newData);
        $currentDateTime = Carbon::now();
        $mysqlDateTime = $currentDateTime->format('Y-m-d H:i:s');

        // ALERTA DE CLIENTE POR CORREO SI ESTA ACTIVO
        // "alert-activo" => "1"
        // "alert-fecha-limite" => "2023-10-30"
        // "alert-titulo" => "alerta de envio por correo"
        // "alert-descripcion" => "esta es una descripción de una alerta por correo"
        $alertActivo = request()->input("alert-activo", "0");
        $alertFecha = request()->input("alert-fecha-limite");
        $alertTitulo = request()->input("alert-titulo");
        $alertDescripcion = request()->input("alert-descripcion");
        if ($alertActivo == "1") {
            AlertCliente::insert([
                'id_client' => $dataId,
                'fecha_limite' => $alertFecha,
                'titulo' => $alertTitulo,
                'descripcion' => $alertDescripcion,
                'code_company' => Auth::user()->code_company,
                'code_user' => Auth::user()->code_user,
                'metadata' => null,
            ]);
        }
        return redirect()->route('sistema_expedientes.expedientesRegistroClientes')->with('success', '¡Cliente registrado correctamente!');
    }

    // ACTUALIZAR
    public function  updateCliente(Request $request)
    {
        $datosCliente = request()->all();
        $id = $datosCliente["id"];
        $typeClient = $datosCliente["u-type-client"];

        $email = $datosCliente["u-email"];
        $typeEmail = $datosCliente["u-type-email"];
        $email2 = $datosCliente["u-email2"];
        $typeEmail2 = $datosCliente["u-type-email2"];
        $phone = $datosCliente["u-phone"];
        $typePhone = $datosCliente["u-type-phone"];
        $phone2 = $datosCliente["u-phone2"];
        $typePhone2 = $datosCliente["u-type-phone2"];
        $country = $datosCliente["u-country"];
        $departamento = $datosCliente["u-selectDepartamento"];
        $provincia = $datosCliente["u-selectProvincia"];
        $distrito = $datosCliente["u-selectDistrito"];
        $street = $datosCliente["u-street"];

        $jsonEmail = ['email' => $email, 'type_email' => $typeEmail, 'email2' => $email2, 'type_email2' => $typeEmail2];
        $jsonPhone = ['phone' => $phone, 'type_phone' => $typePhone, 'phone2' => $phone2, 'type_phone2' => $typePhone2];
        $jsonAddress = [
            'country' => $country,
            'departamento' => $departamento,
            'provincia' => $provincia,
            'distrito' => $distrito,
            'street' => $street,
        ];

        $nameInMessage = '';
        $uCliente = Cliente::find($id);
        $uCliente->type_contact = $typeClient;
        if ($typeClient == 'Persona') {
            $nameInMessage = $datosCliente["name"] . ', ' . $datosCliente["last-name"];
            $uCliente->name = $datosCliente["name"];
            $uCliente->last_name = $datosCliente["last-name"];
            $uCliente->dni = $datosCliente["dni"];
            $uCliente->birthdate = $datosCliente["birthdate"];
            $uCliente->company = $datosCliente["company"];
            $uCliente->name_company = null;
            $uCliente->ruc = null;
        } else {
            $nameInMessage = $datosCliente["name-company"];
            $uCliente->name = null;
            $uCliente->last_name = null;
            $uCliente->dni = null;
            $uCliente->birthdate = null;
            $uCliente->company = null;
            $uCliente->name_company = $datosCliente["name-company"];
            $uCliente->ruc = $datosCliente["ruc"];
        }
        $uCliente->email = $jsonEmail;
        $uCliente->phone = $jsonPhone;
        $uCliente->address = $jsonAddress;
        $uCliente->save();

        $currentDateTime = Carbon::now();
        $mysqlDateTime = $currentDateTime->format('Y-m-d H:i:s');
        // Alert::insert([
        //     'date_time'=> $mysqlDateTime,
        //     'message' =>"Se actualizó el cliente de tipo ". strtoupper($typeClient) ." con nombre ". strtoupper($nameInMessage),
        //     'alert' => 'info',
        //     'type' => 'cliente',
        //     'id_cli' => $id,
        // ]);

        // notify()->success('Se actualizó nuevo cliente', 'Clientes');
        return redirect()->route('sistema_expedientes.expedientesRegistroClientes')->with('success', '¡Cliente actualizado correctamente!');
    }
}
