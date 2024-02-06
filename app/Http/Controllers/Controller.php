<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *             title="API REST LEGALTECH - TEMIS",
 *             version="1.0",
 *             description="Mostando la Lista de URI's de mi API"
 * )
 *
 * @OA\Server(url="http://127.0.0.1:8000/")
 *
 *
 * @OA\SecurityScheme(
 *     type="http",
 *     description="Token de autenticación Bearer",
 *     name="Authorization",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="bearerAuth"
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
