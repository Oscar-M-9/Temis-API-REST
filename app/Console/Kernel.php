<?php

namespace App\Console;

use App\Models\Alert;
use App\Models\Expedientes;
use App\Models\FollowUp;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
        // $schedule->call(function () {
        //     $daysAgo = Carbon::now()->subDays(15);
        //     $alertas = FollowUp::where('initial_date', '<=', $daysAgo)->get();

        //     foreach ($alertas as $alerta) {
        //         // Crear nuevo registro en tabla de alertas acumuladas
        //         $currentDateTime = Carbon::now();
        //         $mysqlDateTime = $currentDateTime->format('Y-m-d H:i:s');
        //         $exp = Expedientes::where('id', '=', $alerta->id_exp)->get();
        //         $expN = json_decode(json_decode($exp)[0]->n_expediente);
        //         $c1 = $expN->c1;
        //         $c2 = $expN->c2;
        //         $c3 = $expN->c3;
        //         $c4 = $expN->c4;
        //         $c5 = $expN->c5;
        //         $c6 = $expN->c6;
        //         $c7 = $expN->c7;
        //         Alert::insert([
        //             'date_time'=> $mysqlDateTime,
        //             'message' =>"Es necesario agregar seguimiento al expediente ".$c1.'-'.$c2.'-'.$c3.'-'.$c4.'-'.$c5.'-'.$c6.'-'.$c7,
        //             'alert' => 'danger',
        //             'type' => 'observacion',
        //             'id_exp' => $alerta->id_exp,
        //             'num_obs' => $alerta->n_seguimiento,
        //         ]);
        //         // Mostrar alerta en el dashboard
        //     }
        // })->daily();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
