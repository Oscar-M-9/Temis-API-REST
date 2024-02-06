<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AccionesIndecopi;
use App\Models\SeguimientoSuprema;
use App\Models\TempIndecopiAlert;
use App\Models\TempSupremaAlert;
use App\Models\VistaCausaSuprema;
use Carbon\Carbon;

class CopySupremaToTempTable implements ShouldQueue
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
            $existingTempExp = TempSupremaAlert::where('id_suprema', $expediente->id)->first();
            if (!$existingTempExp){
                $tempExp = new TempSupremaAlert();
                $tempExp->id_suprema = $expediente->id;
                $tempExp->n_expediente = $expediente->n_expediente;
                $tempExp->entidad = "suprema";

                $movimientos = SeguimientoSuprema::where('id_exp', $expediente->id)->orderBy('n_seguimiento', 'desc')->get();

                if ($movimientos->isEmpty()) {
                    continue; // Salta este expediente si no tiene movimientos
                }

                $ultMovi = SeguimientoSuprema::where('id_exp', $expediente->id)->where('abog_virtual', 'si')->orderBy('n_seguimiento', 'desc')->get()->first();

                $tempExp->id_ult_movi = $ultMovi->id;
                $tempExp->n_ult_movi = $movimientos->count() - 1;

                // cantidad de seguimiento que obtuvo el abogado virtual
                $tempExp->count_movi = $ultMovi->n_seguimiento;

                $vistaCausas = VistaCausaSuprema::where('id_exp', $expediente->id)->get();
                $idVistaCausas = [];
                foreach ($vistaCausas as $key => $value) {
                    $idVistaCausas[] = $value->id;
                }
                $tempExp->vista_causa = $vistaCausas->count();
                $tempExp->ids_vista_causa = json_encode($idVistaCausas);

                $tempExp->estado = 'pendiente';
                $tempExp->url = $expediente->url_suprema;
                $tempExp->save();
            }
        }
    }
}
