<?php

namespace App\Http\Controllers;

use App\Models\DocumentosPresentadosSinoe;
use App\Models\ExpedienteSinoe;
use App\Models\HistorialDocumentosSinoe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentosPresentadosSinoeController extends Controller
{
    //
    public function addDocument(Request $request)
    {
        // dd($request);

        $nEscrito = request()->input("documento-n-escrito", "");
        $disJudicial = request()->input("documento-dis-judi", "");
        $orgJurisdiccional = request()->input("documento-org-judi", "");
        $tipoDoc = request()->input("documento-tipo-doc", "");
        $fechaPresentacion = request()->input("documento-fecha-p", "");
        $sumilla = request()->input("documento-sumilla", "");
        $fileDoc = request()->file("documento-doc");
        $fileCargo = request()->file("documento-cargo");
        $idExp = request()->input("id-exp-penal");

        if ($idExp && $fileDoc) {

            $datosExpediente = ExpedienteSinoe::where('code_company', Auth::user()->code_company)
                ->where("id", $idExp)
                ->first();


            $newDataHistorial = [
                'n_expediente' => $datosExpediente->n_expediente,
                'id_exp' => $datosExpediente->id,
                'n_escrito' => $nEscrito,
                'distrito_judicial' => $disJudicial,
                'organo_juris' => $orgJurisdiccional,
                'tipo_doc' => $tipoDoc,
                'fecha_presentacion' => $fechaPresentacion,
                'sumilla' => $sumilla,
                'metadata' => null,
                'code_company' => Auth::user()->code_company,
                'code_user' => Auth::user()->code_user
            ];

            $insertedId = HistorialDocumentosSinoe::insertGetId($newDataHistorial);


            if ($fileDoc) {
                $extension = $fileDoc->getClientOriginalExtension();
                $nombreArchivo = $fileDoc->getClientOriginalName();
                $nombreArchivoSinExtension = basename($nombreArchivo, ".{$extension}");

                // Verifica si la extensión del archivo está permitida
                if ($extension == 'pdf' || $extension == 'docx' || $extension == 'doc') {
                    // Sube el archivo al almacenamiento
                    if (file_exists(public_path('storage/docs/sinoe/' . Auth::user()->code_company . '/' . $datosExpediente->n_expediente . '/documento-presentado/' . $nombreArchivo))) {
                        // El archivo existe
                        $nombreArchivo = $nombreArchivoSinExtension . ' - copy.' . $extension;
                        $rutaArchivo = $fileDoc->storeAs('public/docs/sinoe/' . Auth::user()->code_company . '/'  . $datosExpediente->n_expediente . '/documento-presentado', $nombreArchivo);
                        $urlDoc = Storage::url($rutaArchivo);
                    } else {
                        $rutaArchivo = $fileDoc->storeAs('public/docs/sinoe/' . Auth::user()->code_company . '/'  . $datosExpediente->n_expediente . '/documento-presentado', $nombreArchivo);
                        $urlDoc = Storage::url($rutaArchivo);
                    }
                    $descripcionDoc = "Documento";

                    $newDataDoc = [
                        'id_exp' => $idExp,
                        'id_historial' => $insertedId,
                        'descripcion' => $descripcionDoc,
                        'file_doc' => $urlDoc,
                        'file_cargo' => null,
                        'metadata' => null,
                        'code_company' => Auth::user()->code_company,
                        'code_user' => Auth::user()->code_user
                    ];
                    DocumentosPresentadosSinoe::create($newDataDoc);
                }
            }

            if ($fileCargo) {
                $extension2 = $fileCargo->getClientOriginalExtension();
                $nombreArchivo2 = $fileCargo->getClientOriginalName();
                $nombreArchivoSinExtension2 = basename($nombreArchivo2, ".{$extension2}");

                // Verifica si la extensión del archivo está permitida
                if ($extension2 == 'pdf' || $extension2 == 'docx' || $extension2 == 'doc') {
                    // Sube el archivo al almacenamiento
                    if (file_exists(public_path('storage/docs/sinoe/' . Auth::user()->code_company . '/'  . $datosExpediente->n_expediente . '/documento-presentado/' . $nombreArchivo2))) {
                        // El archivo existe
                        $nombreArchivo2 = $nombreArchivoSinExtension2 . ' - copy.' . $extension2;
                        $rutaArchivo2 = $fileCargo->storeAs('public/docs/sinoe/' . Auth::user()->code_company . '/'  . $datosExpediente->n_expediente . '/documento-presentado', $nombreArchivo2);
                        $urlDocCargo = Storage::url($rutaArchivo2);
                    } else {
                        $rutaArchivo2 = $fileCargo->storeAs('public/docs/sinoe/' . Auth::user()->code_company . '/'  . $datosExpediente->n_expediente . '/documento-presentado', $nombreArchivo2);
                        $urlDocCargo = Storage::url($rutaArchivo2);
                    }
                    $descripcionCargo = "Cargo";

                    $newDataDoc = [
                        'id_exp' => $idExp,
                        'id_historial' => $insertedId,
                        'descripcion' => $descripcionCargo,
                        'file_doc' => $urlDocCargo,
                        'file_cargo' => null,
                        'metadata' => null,
                        'code_company' => Auth::user()->code_company,
                        'code_user' => Auth::user()->code_user
                    ];
                    DocumentosPresentadosSinoe::create($newDataDoc);
                }
            }

            return redirect()->back()->with('success', '¡Registrado!');;
        }
        return redirect()->back()->with('error', 'Error al registrar');;
    }

    public function updateDocument(Request $request)
    {
        // dd($request);

        $id = request()->input("id-historial-edit", "");
        $nEscrito = request()->input("documento-n-escrito-edit", "");
        $disJudicial = request()->input("documento-dis-judi-edit", "");
        $orgJurisdiccional = request()->input("documento-org-judi-edit", "");
        $tipoDoc = request()->input("documento-tipo-doc-edit", "");
        $fechaPresentacion = request()->input("documento-fecha-p-edit", "");
        $sumilla = request()->input("documento-sumilla-edit", "");
        $idfileDoc = request()->input("info-id-doc-documento", "");
        $idfileCargo = request()->input("info-id-doc-cargo", "");

        $fileDoc = request()->file("documento-doc-edit");
        $fileCargo = request()->file("documento-cargo-edit");

        if ($id) {

            $dataOldHistory =  HistorialDocumentosSinoe::where('code_company', Auth::user()->code_company)
                ->where('id', $id)
                ->first();

            $datosExpediente = ExpedienteSinoe::where('code_company', Auth::user()->code_company)
                ->where("id", $dataOldHistory->id_exp)
                ->first();


            $upDataHistorial = [
                'n_escrito' => $nEscrito,
                'distrito_judicial' => $disJudicial,
                'organo_juris' => $orgJurisdiccional,
                'tipo_doc' => $tipoDoc,
                'fecha_presentacion' => $fechaPresentacion,
                'sumilla' => $sumilla,
                'metadata' => null,
                'code_company' => Auth::user()->code_company,
                'code_user' => Auth::user()->code_user
            ];

            HistorialDocumentosSinoe::where('id', $id)->update($upDataHistorial);


            if ($fileDoc) {
                $extension = $fileDoc->getClientOriginalExtension();
                $nombreArchivo = $fileDoc->getClientOriginalName();
                $nombreArchivoSinExtension = basename($nombreArchivo, ".{$extension}");

                $oldDataFileDoc = DocumentosPresentadosSinoe::where('code_company', Auth::user()->code_company)
                    ->where("id", $idfileDoc)
                    ->first();

                // Verifica si la extensión del archivo está permitida
                if ($extension == 'pdf' || $extension == 'docx' || $extension == 'doc') {

                    if ($oldDataFileDoc) {
                        if ($oldDataFileDoc->file_doc !== null) {
                            $rutaSinStorage = str_replace('/storage', 'public/', $oldDataFileDoc->file_doc);
                            Storage::delete($rutaSinStorage);
                        }
                    }

                    // Sube el archivo al almacenamiento
                    if (file_exists(public_path('storage/docs/sinoe/' . Auth::user()->code_company . '/' . $datosExpediente->n_expediente . '/documento-presentado/' . $nombreArchivo))) {
                        // El archivo existe
                        $nombreArchivo = $nombreArchivoSinExtension . ' - copy.' . $extension;
                        $rutaArchivo = $fileDoc->storeAs('public/docs/sinoe/' . Auth::user()->code_company . '/'  . $datosExpediente->n_expediente . '/documento-presentado', $nombreArchivo);
                        $urlDoc = Storage::url($rutaArchivo);
                    } else {
                        $rutaArchivo = $fileDoc->storeAs('public/docs/sinoe/' . Auth::user()->code_company . '/'  . $datosExpediente->n_expediente . '/documento-presentado', $nombreArchivo);
                        $urlDoc = Storage::url($rutaArchivo);
                    }

                    $upDataDoc = [
                        'file_doc' => $urlDoc,
                        'code_company' => Auth::user()->code_company,
                        'code_user' => Auth::user()->code_user
                    ];

                    DocumentosPresentadosSinoe::where('code_company', Auth::user()->code_company)
                        ->where('id', $idfileDoc)
                        ->update($upDataDoc);
                }
            }

            if ($fileCargo) {
                $extension2 = $fileCargo->getClientOriginalExtension();
                $nombreArchivo2 = $fileCargo->getClientOriginalName();
                $nombreArchivoSinExtension2 = basename($nombreArchivo2, ".{$extension2}");

                $oldDataFileCargo = DocumentosPresentadosSinoe::where('code_company', Auth::user()->code_company)
                    ->where("id", $idfileCargo)
                    ->first();

                // Verifica si la extensión del archivo está permitida
                if ($extension2 == 'pdf' || $extension2 == 'docx' || $extension2 == 'doc') {

                    // Sube el archivo al almacenamiento
                    if (file_exists(public_path('storage/docs/sinoe/' . Auth::user()->code_company . '/'  . $datosExpediente->n_expediente . '/documento-presentado/' . $nombreArchivo2))) {
                        // El archivo existe
                        $nombreArchivo2 = $nombreArchivoSinExtension2 . ' - copy.' . $extension2;
                        $rutaArchivo2 = $fileCargo->storeAs('public/docs/sinoe/' . Auth::user()->code_company . '/'  . $datosExpediente->n_expediente . '/documento-presentado', $nombreArchivo2);
                        $urlDocCargo = Storage::url($rutaArchivo2);
                    } else {
                        $rutaArchivo2 = $fileCargo->storeAs('public/docs/sinoe/' . Auth::user()->code_company . '/'  . $datosExpediente->n_expediente . '/documento-presentado', $nombreArchivo2);
                        $urlDocCargo = Storage::url($rutaArchivo2);
                    }

                    $upDataDoc = [
                        'file_cargo' => $urlDocCargo,
                        'code_company' => Auth::user()->code_company,
                        'code_user' => Auth::user()->code_user
                    ];

                    if ($oldDataFileCargo) {
                        if ($oldDataFileCargo->file_cargo !== null) {
                            $rutaSinStorage2 = str_replace('/storage', '', $oldDataFileCargo->file_cargo);
                            Storage::delete($rutaSinStorage2);
                        }
                        DocumentosPresentadosSinoe::where('code_company', Auth::user()->code_company)
                            ->where('id', $idfileCargo)
                            ->update($upDataDoc);
                    } else {
                        $descripcionCargo = "Cargo";

                        $newDataDoc = [
                            'id_exp' => $datosExpediente->id,
                            'id_historial' => $id,
                            'descripcion' => $descripcionCargo,
                            'file_doc' => $urlDocCargo,
                            'file_cargo' => null,
                            'metadata' => null,
                            'code_company' => Auth::user()->code_company,
                            'code_user' => Auth::user()->code_user
                        ];
                        DocumentosPresentadosSinoe::create($newDataDoc);
                    }
                }
            }

            return redirect()->back()->with('success', '¡Actualizado!');
        }
        return redirect()->back()->with('error', 'Error al actualizar');;
    }

    public function deleteDocument()
    {
        $id = request()->input("id");

        if ($id) {
            HistorialDocumentosSinoe::where("code_company", Auth::user()->code_company)
                ->where("id", $id)
                ->delete();
            $dataOldDocument = DocumentosPresentadosSinoe::where("code_company", Auth::user()->code_company)
                ->where("id_historial", $id)
                ->get();

            foreach ($dataOldDocument as $key => $value) {
                if ($value->file_doc !== null) {
                    $rutaSinStorage = str_replace('/storage', 'public', $value->file_doc);
                    Storage::delete($rutaSinStorage);
                }
            }
            DocumentosPresentadosSinoe::where("code_company", Auth::user()->code_company)
                ->where("id_historial", $id)
                ->delete();

            return response()->json("Eliminado");
        }

        return response()->json("error");
    }

    public function getAllDocument()
    {

        $idExp = request()->input("idExp");
        if ($idExp) {
            $historialData = HistorialDocumentosSinoe::where("code_company", Auth::user()->code_company)
                ->where("id_exp", $idExp)
                ->get();
            $documentosData = DocumentosPresentadosSinoe::where("code_company", Auth::user()->code_company)
                ->where("id_exp", $idExp)
                ->get();

            return response()->json(["historial" => $historialData, "documentos" => $documentosData]);
        }
        return response()->json(null);
    }
}
