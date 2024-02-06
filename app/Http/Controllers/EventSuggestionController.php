<?php

namespace App\Http\Controllers;

use App\Models\EventSuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EventSuggestionController extends Controller
{

    /**
     * Agregar sugerencia de evento al calendario
     *
     * @OA\Post(
     *     path="/api/add-event-suggestion",
     *     tags={"Calendario"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"fecha", "titulo", "descripcion"},
     *             @OA\Property(property="fecha", type="string", format="date", example="2024-01-24"),
     *             @OA\Property(property="titulo", type="string", example="The titulo field is required."),
     *             @OA\Property(property="descripcion", type="string", example="The descripcion field is required."),
     *             @OA\Property(property="entidad", type="string", example="calendar"),
     *             @OA\Property(property="id-exp-event", type="string", example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evento creado con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="El evento se ha creado con éxito"),
     *             @OA\Property(property="event", type="object",
     *                 @OA\Property(property="fecha", type="string", example="2024-01-24"),
     *                 @OA\Property(property="titulo", type="string", example="The titulo field is required."),
     *                 @OA\Property(property="descripcion", type="string", example="The descripcion field is required."),
     *                 @OA\Property(property="code_user", type="string", example="Temis-1"),
     *                 @OA\Property(property="code_company", type="string", example="desarrollo"),
     *                 @OA\Property(property="entidad", type="string", example="calendar"),
     *                 @OA\Property(property="metadata", type="string", example=null),
     *                 @OA\Property(property="updated_at", type="string", example="2024-01-26T00:05:26.000000Z"),
     *                 @OA\Property(property="created_at", type="string", example="2024-01-26T00:05:26.000000Z"),
     *                 @OA\Property(property="id", type="integer", example=11)
     *             )
     *         )
     *     ),
     *    @OA\Response(
     *         response=422,
     *         description="Se produjo un error al crear evento",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Se produjo un error al crear evento"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="fecha", type="array", @OA\Items(type="string", example="The fecha field is required.")),
     *                 @OA\Property(property="titulo", type="array", @OA\Items(type="string", example="The titulo field is required.")),
     *                 @OA\Property(property="descripcion", type="array", @OA\Items(type="string", example="The descripcion field is required.")),
     *                 @OA\Property(property="entidad", type="array", @OA\Items(type="string", example="The entidad field is required.")),
     *                 @OA\Property(property="id-exp-event", type="array", @OA\Items(type="string", example="The id-exp-event field is required."))
     *             )
     *         )
     *     )
     * )
     */

    public function addEvent()
    {
        $request = request();

        // Validar los datos
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date|date_format:Y-m-d H:i:s',
            'titulo' => 'required|string',
            'descripcion' => 'required|string',
            'entidad' => 'required|string',
            'id-exp-event' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                'message' => 'Se produjo un error al crear evento',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Obtener los valores validados
        $fecha = $request->input('fecha');
        $titulo = $request->input('titulo');
        $descripcion = $request->input('descripcion');
        $entidad = $request->input('entidad');
        $idExp = $request->input('id-exp-event');

        $eventoDate = EventSuggestion::where('fecha', "=", $fecha)->where("code_company", Auth::user()->code_company)->get();

        if (count($eventoDate) > 0) {
            return response()->json([
                "status" => false,
                'message' => 'Ya hay un evento programado para esa hora',
                'event' => $eventoDate,
            ], 422);
        }
        $eventSuggestion = EventSuggestion::create([
            'fecha' => $fecha,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'code_user' => Auth::user()->code_user,
            'code_company' => Auth::user()->code_company,
            'entidad' => $entidad,
            'metadata' => $idExp == 0 ? null : $idExp,
        ]);

        return response()->json([
            "status" => true,
            "message" => "El evento se a creado con éxito",
            "event" => $eventSuggestion,
        ], 200);
    }
}
