<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\ApplicationVersion;

class ApplicationVersionController extends Controller
{
    //
    /**
     * @OA\Get(
     *     path="/api/application-version/{platform}",
     *     tags={"Aplicación"},
     *     summary="Verificar versión de la aplicación",
     *     description="Verifica si la versión de la aplicación es compatible con la plataforma especificada.",
     *     operationId="checkAppVersion",
     *     @OA\Parameter(
     *         name="platform",
     *         in="path",
     *         description="Plataforma de la aplicación (android/ios)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"android", "ios"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Versión compatible",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Plataforma compatible"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="platform", type="string", example="android"),
     *                 @OA\Property(property="version", type="string", example="1.0.0"),
     *                 @OA\Property(property="metadata", type="string", example="null"),
     *                 @OA\Property(property="created_at", type="string", example="2024-03-06T23:25:10.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2024-03-06T23:25:10.000000Z")
     *             ),
     *             @OA\Property(property="platform", type="string", example="android")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Plataforma no compatible",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Plataforma no compatible"),
     *             @OA\Property(property="platform", type="string", example="macos")
     *         )
     *     )
     * )
     */

    public function index($platform)
    {
        $platformExist = ApplicationVersion::where('platform', $platform)->first();
        if ($platformExist) {
            return response()->json([
                "status" => true,
                "message" => "Plataforma compatible",
                "data" => $platformExist,
                "platform" => $platform,
            ], 200);
        }
        return response()->json([
            "status" => false,
            "message" => "Plataforma no compatible",
            "platform" => $platform,
        ], 404);
    }
}
