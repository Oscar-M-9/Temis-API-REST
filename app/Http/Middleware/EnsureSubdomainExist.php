<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class EnsureSubdomainExist
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
        $host = request()->getHttpHost();
        $subdominio = strtok($host, '.');
        $records = User::where('code_company','=',$subdominio)->get();
        if(count($records) <= 0){
            return redirect('domain-notfound');
        }


        return $next($request);
    }
}
