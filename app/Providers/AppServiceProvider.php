<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Remover cabecera X-Powered-By
        header_remove('X-Powered-By');

        // Remover cabecera de versión de Laravel
        Response::macro('noLaravelVersion', function ($content) {
            $response = Response::make($content);
            $response->headers->remove('X-Powered-By');
            $response->headers->remove('X-Frame-Options');
            $response->headers->remove('Server');
            return $response;
        });
        Validator::extend('letterswithspace', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^[\pL\s]+$/u', $value);
        });
        Validator::extend('number',function($attribute, $value, $parameters, $validator){
            return preg_match('/^[1-9][0-9]{0,10}$/', $value);
        });
        Paginator::useBootstrap();
    }
}
