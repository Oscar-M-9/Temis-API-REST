<?php

namespace App\Jobs;

use App\Models\HistorialDocumentosSinoe;
use App\Models\NotificationSinoe;
use App\Models\TempSinoeDocumentAlert;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CopySinoeDocumentToTempTable implements ShouldQueue
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
            $existingTempExp = TempSinoeDocumentAlert::where('id_exp', $expediente->id)->first();
            if (!$existingTempExp){
                $tempExp = new TempSinoeDocumentAlert();
                $tempExp->id_exp = $expediente->id;
                $tempExp->n_expediente = $expediente->n_expediente;
                $tempExp->entidad = "sinoe";

                $ultMovi = NotificationSinoe::where('id_exp', $expediente->id)->where('abog_virtual', 'si')->orderBy('id', 'desc')->get()->first();

                $ultHistorial = HistorialDocumentosSinoe::where('id_exp', $expediente->id)->where('metadata', 'si')->orderBy('id', 'desc')->get()->first();

                if ($ultHistorial){
                    $fechaConvertida = Carbon::createFromFormat('Y-m-d H:i:s', $ultHistorial->fecha_presentacion );
                    $fechaFormateada = $fechaConvertida->format('d/m/Y H:i:s');
                }

                $tempExp->uid = $ultMovi->uid_credenciales_sinoe;
                $tempExp->fecha_hora = $fechaFormateada ?? "";
                $tempExp->id_ult_movi = $ultMovi->id;
                $tempExp->estado = 'pendiente';
                $tempExp->save();
            }
        }
    }
}
