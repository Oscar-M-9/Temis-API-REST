<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function domainNotFound(Request $request)
    {
        $host = request()->getHttpHost();
        $subdominio = strtok($host, '.');

        return view('notFound.domainNotFound')->with([
            'subdominio' => $subdominio
        ]);
    }
}
