<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LegalBotController extends Controller
{
    public function viewIndex()
    {
        return view('dashboard.legalBot.index');
    }

    public function viewConocimiento()
    {
        return view('dashboard.legalBot.conocimiento');
    }

    public function viewEscritoFinal()
    {
        return view('dashboard.legalBot.escritoFinal');
    }

    public function viewAsistenciaLegalIA()
    {
        return view('dashboard.promptsPerzonalizado.promptLibrary');
    }

    public function viewAnalisisExp()
    {
        return view('dashboard.legalTech.analisisExpediente');
    }
}
