<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Credit;
use App\Models\Suscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    // obtener el total de credito para el uso del bot
    public function getTotalCredit(Request $request)
    {
        $company = $request->input('company');

        $credits = Credit::where('code_company', $company)->first();
        $tableCompany = Company::where('code_company', $company)->first();
        $suscripcion = Suscripcion::where('id', $tableCompany->id_suscripcion)->first();
        $total = 0;
        if ($credits) {
            $total = $credits->total;
        }
        $remainingCredit = $suscripcion->limit_credit - $total;
        return response()->json([
            'company' => $company,
            'total' => $total,
            'current_period_start' => $credits->current_period_start,
            'current_period_end' => $credits->current_period_end,
            'credit_total' => $suscripcion->limit_credit,
            'remaining_credit' => $remainingCredit,
        ]);
    }

    public function updateCreditConsumptionPromts()
    {
        $credits = Credit::where('code_company', Auth::user()->code_company)->first();
        Credit::where('id', $credits->id)->update([
            'used_prompts' => DB::raw('used_prompts + 1'),
            'total' => DB::raw('total + 1'),
        ]);
        // Credit::where('id', $credits->id)->increment('used_suggestions');
        // Credit::where('id', $credits->id)->increment('total');

        return response()->json([
            'success' => 'success',
            'status' => 200,
        ]);
    }

    public function updateCreditConsumptionWritting()
    {
        $credits = Credit::where('code_company', Auth::user()->code_company)->first();
        Credit::where('id', $credits->id)->update([
            'used_writtings' => DB::raw('used_writtings + 1'),
            'total' => DB::raw('total + 1'),
        ]);

        return response()->json([
            'success' => 'success',
            'status' => 200,
        ]);
    }

    public function updateCreditConsumptionTrainingKnowledge()
    {
        $credits = Credit::where('code_company', Auth::user()->code_company)->first();
        Credit::where('id', $credits->id)->update([
            'used_training_knowledge' => DB::raw('used_training_knowledge + 1'),
            'total' => DB::raw('total + 1'),
        ]);

        return response()->json([
            'success' => 'success',
            'status' => 200,
        ]);
    }

    // public function updateCreditConsumptionPromtsGeneration()
    // {
    //     $credits = Credit::where('code_company', Auth::user()->code_company)->first();
    //     Credit::where('id', $credits->id)->update([
    //         'used_prompts_generation' => DB::raw('used_prompts_generation + 1'),
    //         'total' => DB::raw('total + 1'),
    //     ]);

    //     return response()->json([
    //         'success' => 'success',
    //         'status' => 200,
    //     ]);
    // }
}
