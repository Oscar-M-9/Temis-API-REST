<?php

namespace App\Jobs;

use App\Models\NotificationSinoe;
use App\Models\TempSinoeAlert;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CopySinoeToTempTable implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $expedientes;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($expedientes)
    {
        $this->expedientes = $expedientes;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->expedientes as $expediente) {
            // Verificar si ya existe un registro con el mismo id_exp
            $existingTempExp = TempSinoeAlert::where('id_exp', $expediente->id)->first();
            if (!$existingTempExp){
                $tempExp = new TempSinoeAlert();
                $tempExp->id_exp = $expediente->id;
                $tempExp->n_expediente = $expediente->n_expediente;
                $tempExp->entidad = "sinoe";

                $movimientos = NotificationSinoe::where('id_exp', $expediente->id)->orderBy('id', 'desc')->get();

                if ($movimientos->isEmpty()) {
                    continue; // Salta este expediente si no tiene movimientos
                }

                $ultMovi = NotificationSinoe::where('id_exp', $expediente->id)->where('abog_virtual', 'si')->orderBy('id', 'desc')->get()->first();

                $fechaConvertida = Carbon::createFromFormat('Y-m-d H:i:s', $ultMovi->fecha);
                $fechaFormateada = $fechaConvertida->format('d/m/Y H:i:s');

                $tempExp->uid = $ultMovi->uid_credenciales_sinoe;
                $tempExp->fecha_hora = $fechaFormateada;
                $tempExp->id_ult_movi = $ultMovi->id;
                $tempExp->estado = 'pendiente';
                $tempExp->save();
            }
        }
    }
}
