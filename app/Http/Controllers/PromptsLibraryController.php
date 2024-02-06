<?php

namespace App\Http\Controllers;

use App\Models\Companies_Prompts;
use App\Models\Company;
use App\Models\ModelPrompts;
use App\Models\ModelPromptsCategorias;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromptsLibraryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $id_User=Auth()->id();
        $user=User::where('id', $id_User)->first();

        $company_code=$user->code_company;
        $company=Company::where('code_company',$company_code)->first();

        $prompts=DB::table('prompts')->join('prompts_category','prompts.id_category','=','prompts_category.id')
        ->join('companies_prompts','prompts.id','=','companies_prompts.prompts_id')
        ->select('prompts.id as id','prompts.basic as basic','prompts.title as title','prompts.content as content','prompts_category.name as categoria')
        ->where('companies_prompts.companies_id',$company->id)
        ->get();
        $categorias=ModelPromptsCategorias::select('id', 'name')->get();
        // echo $prompts;
        return view('dashboard.promptsPerzonalizado.crudPrompt', compact('prompts','categorias'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categorias=ModelPromptsCategorias::select('id', 'name')->get();
        return response()->json(['categorias'=>$categorias]);
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
            'title' => 'required|string',
            'id_category' => 'required|string',
            'content' => 'required|string',
        ]);
        // Log::debug($request ->get('id_category'));

        $id_User=Auth()->id();
        $user=User::where('id', $id_User)->first();

        $company_code=$user->code_company;
        $company=Company::where('code_company',$company_code)->first();

        $prompt = new ModelPrompts();
        $prompt->title = $request ->get('title');
        $prompt->id_category = $request ->get('id_category');
        $prompt->content = $request ->get('content');
        $prompt->save(); //Campo basic default 0
        
        $companies_prompt=new Companies_Prompts();
        $companies_prompt->prompts_id = $prompt->id;
        $companies_prompt->companies_id = $company->id ;
        $companies_prompt->save();

        return response()->json(['success' => 'Nuevo Prompt agregado a su base de datos.'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $id_User=Auth()->id();
        $user=User::where('id', $id_User)->first();

        $company_code=$user->code_company;
        $company=Company::where('code_company',$company_code)->first();
        
        $prompts=DB::table('prompts')->join('prompts_category','prompts.id_category','=','prompts_category.id')
        ->join('companies_prompts','prompts.id','=','companies_prompts.prompts_id')
        ->select('prompts.id as id','prompts.basic as basic','prompts.title as title','prompts.content as content','prompts_category.name as categoria')
        ->where('companies_prompts.companies_id',$company->id)
        ->where('id_category',$id)->get();

        $categorias=ModelPromptsCategorias::select('id', 'name')->get();
        return view('dashboard.promptsPerzonalizado.crudPrompt', compact('prompts','categorias','id'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $prompt = ModelPrompts::findOrFail($id);
        $categorias=ModelPromptsCategorias::select('id', 'name')->get();
        return response()->json(['categorias'=>$categorias,'prompt'=>$prompt]);
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
        $request->validate([
            'title' => 'required|string',
            'id_category' => 'required|string',
            'content' => 'required|string',
        ]);

        $prompt = ModelPrompts::findOrFail($id);

        if ($prompt->basic) {
            return response()->json(['forbidden' => 'Los prompts por defecto no pueden ser editados. Te invitamos a crear uno personalizado.'], 200);
        }

        $prompt->title = $request ->get('title');
        $prompt->id_category = $request ->get('id_category');
        $prompt->content = $request ->get('content');
        $prompt->save();
        return response()->json(['success' => 'Prompt actualizado en la base de datos.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $prompt = ModelPrompts::findOrFail($id);
        if ($prompt->basic) {
            return response()->json(['forbidden' => 'Los prompts por defecto no pueden ser eliminados.'], 200);
        }

        $id_User=Auth()->id();
        $user=User::where('id', $id_User)->first();

        $company_code=$user->code_company;
        $company=Company::where('code_company',$company_code)->first();

        DB::table('companies_prompts')
        ->where('companies_id', $company->id)
        ->where('prompts_id', $id)
        ->delete();

        $prompt->delete();
        return Redirect()->route('prompt_libraries.index')->with('success','Prompt eliminado.');
    }
}
