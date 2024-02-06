<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Expediente;
use App\Models\AccionesIndecopi;
use App\Models\FollowUp;
use App\Models\TempExpedienteAlert;
use App\Models\TempIndecopiAlert;
use Carbon\Carbon;

class CopyIndecopiToTempTable implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $expedientes;

    public function __construct($expedientes)
    {
        $this->expedientes = $expedientes;
    }

    public function handle()
    {
        foreach ($this->expedientes as $expediente) {
            // Verificar si ya existe un registro con el mismo id_exp
            $existingTempExp = TempIndecopiAlert::where('id_indecopi', $expediente->id)->first();
            if (!$existingTempExp){
                $tempExp = new TempIndecopiAlert();
                $tempExp->id_indecopi = $expediente->id;
                $tempExp->n_expediente = $expediente->numero;
                $tempExp->entidad = "indecopi";

                $movimientos = AccionesIndecopi::where('id_indecopi', $expediente->id)->orderBy('n_accion', 'desc')->get();

                if ($movimientos->isEmpty()) {
                    continue; // Salta este expediente si no tiene movimientos
                }

                $ultMovi = AccionesIndecopi::where('id_indecopi', $expediente->id)->where('abog_virtual', 'si')->orderBy('n_accion', 'desc')->get()->first();

                $tempExp->id_ult_movi = $ultMovi->id;
                $tempExp->n_ult_movi = $movimientos->count() - 1;

                // cantidad de las acciones que obtuvo el abogado virtual
                $tempExp->update_information = $ultMovi->n_accion;

                $tempExp->estado = 'pendiente';
                $tempExp->detalle = $expediente->metadata;
                $tempExp->save();
            }
        }
    }
}
