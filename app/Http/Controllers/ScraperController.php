<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\ScrapingJob;
use Illuminate\Http\Request;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\View;

class ScraperController extends Controller
{

    public function scraper()
    {
        return view('scraper.scraper');
    }

    public function scraping(Request $request)
    {
        $entidadExp = $request->input('entidad-exp');

        $scrapingConfigs = [
            //? Resultado por codigo de expediente
            //? [Reporte de expediente], [Partes procesales]
            0 => [
                'script' => 'web_scraping_1_buscar_data.js',
                'params' => ['code-exp'],
                'modo' => 0,
            ],
            //? Resultado por codigo de expediente
            //? [Segimiento del expediente]
            // 'S0' => [
            //     'script' => 'web_scraping_1_buscar_data_seguimiento.js',
            //     'params' => ['code-exp'],
            //     'modo' => 0,
            // ],
            //? Primer resultado por codigo de expediente
            1 => [
                'script' => 'web_scraping_1_buscar_data_result.js',
                'params' => ['code-exp'],
                'modo' => 0,
            ],
            //? Primer resultado por filtro
            2 => [
                'script' => 'web_scraping_1_buscar_data_result.js',
                'params' => ['judi', 'inst', 'espec', 'anio', 'nExp'],
                'modo' => 1,
            ],
            //? CEJ supremo
            3 => [
                // 'script' => 'web_scraping_2_buscar_data_result.js',
                // 'params' => ['judi', 'inst', 'espec', 'anio', 'nExp'],
                // 'modo' => 1,
            ],
            //? INDECOPI - Búsqueda por número de Reclamo/Buen Oficio
            4 => [
                'script' => 'web_scraping_3_buscar.js',
                'params' => ['tipo', 'numero', 'anio', 'lugar'],
                'modo' => 0,
            ],
            //? INDECOPI - Búsqueda por número de Reclamo/Buen Oficio
            5 => [
                'script' => 'web_scraping_3_buscar.js',
                'params' => ['tipo', 'fechadel', 'fechaAl', 'tipoDoc', 'nDoc', 'nombreRazon', 'apellidoP', 'apellidoM'],
                'modo' => 1,
            ],
            //? INDECOPI - Búsqueda por Reclamado/Proveedor
            6 => [
                'script' => 'web_scraping_3_buscar.js',
                'params' => ['tipo', 'fechadel', 'fechaAl', 'tipoDoc', 'nDoc', 'nombreRazon', 'apellidoP', 'apellidoM'],
                'modo' => 2,
            ],
            // ? obtener la data de indecopi con el detalle
            'I0' => [
                'script' => 'web_scraping_3_data.js',
                'params' => ['detalle'],
                'modo' => 2,
            ],
            // Agrega más configuraciones según sea necesario
        ];

        if (isset($scrapingConfigs[$entidadExp])) {

             // Seleccionando la entidad para el scraping.
            $config = $scrapingConfigs[$entidadExp];
            // Obteniendo el parametro.
            $params = $config['params'];

            // Seleccionando la entidad para el scraping.
            $config = $scrapingConfigs[$entidadExp];
            // Obteniendo el parametro.
            $params = $config['params'];
            // creando el proceso con los argumentos dados
            $processArgs = array_merge(['node', '../' . $config['script']], [$config['modo']], array_map(function ($param) use ($request) {
                return $request->input($param);
            }, $params));

            // dd($processArgs);

            try {
                // Establecer limite de tiempo en el API
                set_time_limit(0);
                $process = new Process($processArgs);
                // Establecer el límite de tiempo a 60 segundos
                // $process->setTimeout(60);

                $process->run();

                if (!$process->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }

                // Obtener el resultado del proceso
                $result = $process->getOutput();
                $resultArray = json_decode($result, true);
                // Procesar los datos según sea necesario y retornar la respuesta JSON
                return response()->json(['data' => $resultArray], 200);
            } catch (\Exception $e) {
                 // El proceso ha excedido el tiempo límite de 60 segundos
                // Destruir el proceso (esto puede no ser necesario ya que Symfony lo destruirá automáticamente)
                // $process->stop(1);

                // Manejar el error si ocurre alguna excepción
                return response()->json(['error' => $e->getMessage()], 500);
            }


        } else {
            return response()->json(['error' => 'Invalid entidad-exp value'], 400);
        }
    }




    // // Seleccionando la entidad para el scraping.
            // $config = $scrapingConfigs[$entidadExp];
            // // Obteniendo el parametro.
            // $params = $config['params'];
            // // creando el proceso con los argumentos dados
            // $processArgs = array_merge(['node', '../' . $config['script']], [$config['modo']], array_map(function ($param) use ($request) {
            //     return $request->input($param);
            // }, $params));

            // // dd($processArgs);

            // // try {
            //     // Establecer limite de tiempo en el API
            //     set_time_limit(0);
            //     $process = new Process($processArgs);
            //     // Establecer el límite de tiempo a 60 segundos
            //     // $process->setTimeout(60);

            //     $process->run();

            //     if (!$process->isSuccessful()) {
            //         throw new ProcessFailedException($process);
            //     }

            //     // Obtener el resultado del proceso
            //     $result = $process->getOutput();
            //     $resultArray = json_decode($result, true);
            //     // Procesar los datos según sea necesario y retornar la respuesta JSON
            //     return response()->json(['data' => $resultArray], 200);
            // } catch (\Exception $e) {
            //      // El proceso ha excedido el tiempo límite de 60 segundos
            //     // Destruir el proceso (esto puede no ser necesario ya que Symfony lo destruirá automáticamente)
            //     // $process->stop(1);

            //     // Manejar el error si ocurre alguna excepción
            //     return response()->json(['error' => $e->getMessage()], 500);
            // }


}
