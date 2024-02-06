<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EconomicExpensesIndecopi;
use App\Models\Indecopi;
use App\Models\User;
use Illuminate\Http\Request;

class EconomicExpensesIndecopiController extends Controller
{
    /*
     * ************************************************
     *
     *          DATOS ECONOMICOS DEL EXPEDIENTE
     *
     * ************************************************ */

    public function addEconomic(Request $request)
    {
        // dd($request);

        $idExp = request()->input("idExp"); // => "44"
        $type = request()->input("typeEconomic"); // => "Gastos"
        $title = request()->input("titleEconomic"); // => "Gasto general"
        $datetime = request()->input("dateEconomic"); // => "2023-09-05T15:20"
        $descrip = request()->input("descripEconomic"); // => "se pago esta cantidad"
        $moneda = request()->input("monedaEconomic"); // => "Sol"
        $monto = request()->input("montoEconomic"); // => "120"
        $pagado = request()->input("pagadoEconomic"); // => "Si"

        // Verificar si el número ya tiene dos decimales
        if (!preg_match('/\.\d{2}$/', $monto)) {
            $monto = number_format($monto, 2, '.', '');
        }
        $dataUser = User::where('id', Auth()->id())->get()->first();
        $exp =  Indecopi::where('id', $idExp)->get()->first();
        $formattedDatetime = date("Y-m-d H:i:s", strtotime($datetime));

        if ($dataUser->code_user = $exp->code_user) {
            $newData = [
                'type' => $type,
                'date_time' => $formattedDatetime,
                'moneda' => $moneda,
                'mount' => $monto,
                'titulo' => $title,
                'descripcion' => $descrip,
                'status' => $pagado,
                'attached_files' => '[]',
                'metadata' => Null,
                'code_user' => $dataUser->code_user,
                'code_company' => $dataUser->code_company,
                'id_indecopi' => $idExp,
                'entidad' => $exp->entidad,
            ];

            EconomicExpensesIndecopi::insert($newData);

            return response()->json($newData);
        }

        return response()->json([]);
    }

    public function editEconomic(Request $request)
    {
        // dd($request);

        $idExp = request()->input("idExp"); // => "44"
        $id = request()->input("id"); // => "44"
        $title = request()->input("titleEconomic"); // => "Gasto general"
        $datetime = request()->input("dateEconomic"); // => "2023-09-05T15:20"
        $descrip = request()->input("descripEconomic"); // => "se pago esta cantidad"
        $moneda = request()->input("monedaEconomic"); // => "Sol"
        $monto = request()->input("montoEconomic"); // => "120"
        $pagado = request()->input("pagadoEconomic"); // => "Si"

        // Verificar si el número ya tiene dos decimales
        if (!preg_match('/\.\d{2}$/', $monto)) {
            $monto = number_format($monto, 2, '.', '');
        }
        $dataUser = User::where('id', Auth()->id())->get()->first();
        $exp =  Indecopi::where('id', $idExp)->get()->first();
        $formattedDatetime = date("Y-m-d H:i:s", strtotime($datetime));

        if ($dataUser->code_user = $exp->code_user) {
            $newData = [
                'date_time' => $formattedDatetime,
                'moneda' => $moneda,
                'mount' => $monto,
                'titulo' => $title,
                'descripcion' => $descrip,
                'status' => $pagado,
                'attached_files' => Null,
                'metadata' => Null,
                'code_user' => $dataUser->code_user,
                'code_company' => $dataUser->code_company,
                'id_indecopi' => $idExp,
                'entidad' => $exp->entidad,
            ];

            EconomicExpensesIndecopi::where('id', $id)->where('code_company', $dataUser->code_company)->update($newData);

            return response()->json($newData);
        }

        return response()->json([]);
    }

    public function deleteEconomic()
    {
        $id = request()->input('id');
        $codeCompany = auth()->user()->code_company;

        EconomicExpensesIndecopi::where('id', $id)
            ->where('code_company', $codeCompany)
            ->delete();
        return response()->json("Eliminado");
    }

    public function getAllEconomic()
    {
        $idExp = request()->input('idExp');
        $dataUser = User::where('id', Auth()->id())->first();

        $economic = EconomicExpensesIndecopi::join('indecopis', 'economic_expenses_indecopis.id_indecopi', '=', 'indecopis.id')
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
            ->orderBy('economic_expenses_indecopis.id', 'desc')
            ->where('economic_expenses_indecopis.code_company', $dataUser->code_company)
            ->where('economic_expenses_indecopis.id_indecopi', $idExp)
            ->get();
        return response()->json($economic);
    }

    public function getAllMoneyEconomic()
    {
        $idExp = request()->input('idExp');
        $dataUser = User::where('id', Auth()->id())->first();

        $economic = EconomicExpensesIndecopi::join('indecopis', 'economic_expenses_indecopis.id_indecopi', '=', 'indecopis.id')
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
            ->where('economic_expenses_indecopis.id_indecopi', $idExp)
            ->get();

        // Calcular las sumas
        // ? gastos
        $sumaGastosMountSolSi = $economic->where('moneda', 'Sol')->where('status', 'Si')->where('type', 'Gastos')->sum('mount');
        $sumaGastosMountDolarSi = $economic->where('moneda', 'Dólar')->where('status', 'Si')->where('type', 'Gastos')->sum('mount');
        $sumaGastosMountSolNo = $economic->where('moneda', 'Sol')->where('status', 'No')->where('type', 'Gastos')->sum('mount');
        $sumaGastosMountDolarNo = $economic->where('moneda', 'Dólar')->where('status', 'No')->where('type', 'Gastos')->sum('mount');
        // ? Ingresos
        $sumaRecaudacionesMountSolSi = $economic->where('moneda', 'Sol')->where('status', 'Si')->where('type', 'Recaudaciones')->sum('mount');
        $sumaRecaudacionesMountDolarSi = $economic->where('moneda', 'Dólar')->where('status', 'Si')->where('type', 'Recaudaciones')->sum('mount');
        $sumaRecaudacionesMountSolNo = $economic->where('moneda', 'Sol')->where('status', 'No')->where('type', 'Recaudaciones')->sum('mount');
        $sumaRecaudacionesMountDolarNo = $economic->where('moneda', 'Dólar')->where('status', 'No')->where('type', 'Recaudaciones')->sum('mount');
        // ? Comisiones
        $sumaComisionesMountSolSi = $economic->where('moneda', 'Sol')->where('status', 'Si')->where('type', 'Comisiones')->sum('mount');
        $sumaComisionesMountDolarSi = $economic->where('moneda', 'Dólar')->where('status', 'Si')->where('type', 'Comisiones')->sum('mount');
        $sumaComisionesMountSolNo = $economic->where('moneda', 'Sol')->where('status', 'No')->where('type', 'Comisiones')->sum('mount');
        $sumaComisionesMountDolarNo = $economic->where('moneda', 'Dólar')->where('status', 'No')->where('type', 'Comisiones')->sum('mount');

        $sumaMountTotalSol = $economic->where('moneda', 'Sol')->sum('mount');
        $sumaMountTotalDolar = $economic->where('moneda', 'Dólar')->sum('mount');

        // Crear una estructura de datos que contenga los datos originales y las sumas
        $resultados = [
            'sumaGastosMountSolSi' => $sumaGastosMountSolSi,
            'sumaGastosMountDolarSi' => $sumaGastosMountDolarSi,
            'sumaGastosMountSolNo' => $sumaGastosMountSolNo,
            'sumaGastosMountDolarNo' => $sumaGastosMountDolarNo,

            'sumaRecaudacionesMountSolSi' => $sumaRecaudacionesMountSolSi,
            'sumaRecaudacionesMountDolarSi' => $sumaRecaudacionesMountDolarSi,
            'sumaRecaudacionesMountSolNo' => $sumaRecaudacionesMountSolNo,
            'sumaRecaudacionesMountDolarNo' => $sumaRecaudacionesMountDolarNo,

            'sumaComisionesMountSolSi' => $sumaComisionesMountSolSi,
            'sumaComisionesMountDolarSi' => $sumaComisionesMountDolarSi,
            'sumaComisionesMountSolNo' => $sumaComisionesMountSolNo,
            'sumaComisionesMountDolarNo' => $sumaComisionesMountDolarNo,

            'sumaMountTotalSol' => $sumaMountTotalSol,
            'sumaMountTotalDolar' => $sumaMountTotalDolar,
        ];

        return response()->json($resultados);
    }

    /*
     * ************************************************
     *
     *          ATTACHED FILES
     *
     * ************************************************ */

    public function uploadAttachedFiles(Request $request)
    {
        $file_name = time() . '.' . request()->sample_image->getClientOriginalExtension();
        request()->sample_image->move(public_path('images'), $file_name);
        $image_path = '/images/' . $file_name; // Ruta relativa

        $idExp = request()->idExp;
        $idEconomic = request()->id;
        $dataUser = User::where('id', Auth()->id())->first();

        // Obtén el registro económico que deseas actualizar
        $economicExpense = EconomicExpensesIndecopi::where('id', $idEconomic)
            ->where('id_indecopi', $idExp)
            ->where('code_company', $dataUser->code_company)
            ->first();

        // Obtiene los archivos existentes como un array (si ya hay archivos)
        $attachedFiles = json_decode($economicExpense->attached_files, true) ?? [];

        // Agrega la nueva URL al array
        $attachedFiles[] = $image_path;

        // Convierte el array a JSON para guardarlo en la base de datos
        $attachedFilesJson = json_encode($attachedFiles);

        // Actualiza la columna 'attached_files' con el nuevo JSON
        $economicExpense->update(['attached_files' => $attachedFilesJson]);

        return response()->json(['image_path' => $image_path]);
    }


    public function removeuploadAttachedFiles(Request $request)
    {
        $fileToDelete = $request->input('fileToDelete'); // Nombre del archivo a eliminar
        $idExp = $request->input('idExp');
        $idEconomic = $request->input('id');
        $dataUser = User::where('id', Auth()->id())->first();

        // Obtén el registro económico que deseas actualizar
        $economicExpense = EconomicExpensesIndecopi::where('id', $idEconomic)
            ->where('id_indecopi', $idExp)
            ->where('code_company', $dataUser->code_company)
            ->first();

        if ($economicExpense) {
            // Obtiene los archivos existentes como un array (si ya hay archivos)
            $attachedFiles = json_decode($economicExpense->attached_files, true) ?? [];

            // Encuentra y elimina el archivo del array
            $fileIndex = array_search('/images/' . $fileToDelete, $attachedFiles);
            if ($fileIndex !== false) {
                // Elimina el archivo del directorio de imágenes
                if (file_exists(public_path('images/' . $fileToDelete))) {
                    unlink(public_path('images/' . $fileToDelete));
                }

                unset($attachedFiles[$fileIndex]);

                // Convierte el array resultante a JSON
                $attachedFilesJson = json_encode(array_values($attachedFiles));

                // Actualiza la columna 'attached_files' con el nuevo JSON
                $economicExpense->update(['attached_files' => $attachedFilesJson]);

                $remainingItemCount = count($attachedFiles);

                return response()->json([
                    'message' => 'Archivo eliminado con éxito',
                    'remainingItemCount' => $remainingItemCount
                ]);
            } else {
                return response()->json(['message' => 'El archivo no existe en el array']);
            }
        } else {
            return response()->json(['message' => 'No se encontró el registro económico']);
        }
    }
}
