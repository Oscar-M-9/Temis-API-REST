<?php

namespace App\Http\Controllers;

use App\Models\Companies_Prompts;
use App\Models\Company;
use App\Models\ModelPrompts;
use App\Models\ModelPromptsCategorias;
use App\Models\User;
use Illuminate\Http\Request;

class PromptsGeneratorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('dashboard.promptsPerzonalizado.promptsGenerador');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $category = ModelPromptsCategorias::select('id', 'name')->get();
        return response()->json(['categorias'=>$category]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string',
            'categoria' => 'required|string',
            'prompt' => 'required|string',
        ]);

        $id_User=Auth()->id();
        $user=User::where('id', $id_User)->first();

        $company_code=$user->code_company;
        $company=Company::where('code_company',$company_code)->first();

        $prompt = new ModelPrompts();
        $prompt->title = $request ->get('titulo');
        $prompt->id_category = $request ->get('categoria');
        $prompt->content = $request ->get('prompt');
        $prompt->save();

        $companies_prompt=new Companies_Prompts();
        $companies_prompt->prompts_id = $prompt->id;
        $companies_prompt->companies_id = $company->id ;
        $companies_prompt->save();

        return response()->json(['mensaje' => 'Dato almacenado correctamente'],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
