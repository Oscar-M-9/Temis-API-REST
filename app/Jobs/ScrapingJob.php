<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ScrapingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $config;
    private $params;
    private $requestData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($config, $params, $requestData)
    {
        //
        $this->config = $config;
        $this->params = $params;
        $this->requestData = $requestData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $request = $this->requestData;
        $params = $this->config['params'];
        // creando el proceso con los argumentos dados
        $processArgs = array_merge(['node', '../' . $this->config['script']], [$this->config['modo']], array_map(function ($param) use ($request) {
            return $request[$param];
        }, $params));
        // dd($processArgs);
        try {
            // Establecer limite de tiempo en el API
            set_time_limit(0);
            $process = new Process($processArgs);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            // Obtener el resultado del proceso
            $result = $process->getOutput();
            $resultArray = json_decode($result, true);
            // Procesar los datos según sea necesario y retornar la respuesta JSON
            // var_dump($resultArray);
            return ['data' => $resultArray];
        } catch (\Exception $e) {
            // Manejar el error si ocurre alguna excepción
            // var_dump($e);
            return ['error' => $e->getMessage()];
        }
    }
}
