<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LegalTechController extends Controller
{
    //
    public function view() {
        return view('dashboard.legalTech.index');
    }

    public function viewCourses() {
        return view('dashboard.legalTech.courses');
    }

    public function viewJurisprudencia() {
        return view('dashboard.legalTech.jurisprudencia');
    }
}
