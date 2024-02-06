<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\Suscripcion;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificarAccesoSuprema
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar si el usuario tiene acceso suprema
        $dataCompany = Company::where('code_company', Auth::user()->code_company)->first();
        $dataSuscripcion = Suscripcion::where('id', $dataCompany->id_suscripcion)->first();
        $accessSuprema = $dataSuscripcion->access_suprema;

        if ($accessSuprema === 'yes') {
            return $next($request);
        }

        // Si el acceso no es 'yes', puedes redirigir o denegar el acceso.
        return redirect('/no-access');
    }
}
