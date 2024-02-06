<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Expediente;
use App\Models\FollowUp;
use App\Models\TempExpedienteAlert;
use Carbon\Carbon;

class CopyExpedientesToTempTable implements ShouldQueue
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
            $existingTempExp = TempExpedienteAlert::where('id_exp', $expediente->id)->first();
            if (!$existingTempExp){
                $tempExp = new TempExpedienteAlert();
                $tempExp->id_exp = $expediente->id;
                $tempExp->n_expediente = $expediente->n_expediente;
                $tempExp->entidad = "judicial";

                $movimientos = FollowUp::where('id_exp', $expediente->id)->orderBy('n_seguimiento', 'desc')->get();

                if ($movimientos->isEmpty()) {
                    continue; // Salta este expediente si no tiene movimientos
                }

                $ultMovi = FollowUp::where('id_exp', $expediente->id)->where('abog_virtual', 'si')->orderBy('n_seguimiento', 'desc')->get()->first();

                $lastTitle = $ultMovi->fecha_resolucion ? "Fecha de Resolución" : "Fecha de Ingreso";
                $lastValue = $ultMovi->fecha_resolucion ? Carbon::parse($ultMovi->fecha_resolucion)->format('d/m/Y') : Carbon::parse($ultMovi->fecha_ingreso)->format('d/m/Y H:i');

                $tempExp->date_ult_movi = $lastValue;
                $tempExp->title_ult_movi = $lastTitle;
                $tempExp->id_ult_movi = $ultMovi->id;
                $tempExp->n_ult_movi = $movimientos->count() - 1;
                $tempExp->data_last = json_encode([
                                        'title' => $lastTitle,
                                        'value' => $lastValue,
                                    ]);

                $pendingDates = [];
                $idsPending = [];
                $contador = 0;

                foreach ($movimientos as $movimiento) {
                    if ($contador < 3 && $movimiento->fecha_resolucion) {
                        $fecha = Carbon::parse($movimiento->fecha_resolucion);
                        $pendingDate = $fecha->format('d/m/Y');
                        $idsPending[] = $movimiento->id;
                        $pendingDates[] = $pendingDate;
                        $contador++;
                    }
                }

                $tempExp->data_pending = json_encode($pendingDates);
                $tempExp->ids_pending = json_encode($idsPending);
                $tempExp->estado = 'pendiente';
                $tempExp->save();
            }
        }
    }
}
