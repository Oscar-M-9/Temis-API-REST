<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Laravel\Sanctum\PersonalAccessToken;

class RevokeOtherTokens
{
    public function handle(Authenticated $event)
    {
        // \Log::info('Evento Authenticated disparado.');
        $user = $event->user;

        // Verificar si el usuario y el token actual existen
        if ($user && $user->api_token) {
            // \Log::info('Usuario y token actuales válidos.');
            // Obtener el token actual
            $currentToken = $user->api_token;

            $segments = explode('|', $currentToken);

            // Obtener el ID, que es el primer segmento
            $currentTokenId = $segments[0];
            // Revoke other tokens except the current one
            $tokens = PersonalAccessToken::where('tokenable_id', $user->id)
                ->where('id', '!=', $currentTokenId)
                ->get();

            foreach ($tokens as $token) {
                $token->delete();
            }
        } else {
            // \Log::warning('Usuario o token actual nulos.');
        }
    }

    // public function handle(Authenticated $event)
    // {
    //     \Log::info('Evento Authenticated disparado.');
    //     $user = $event->user;
    //     dd($user->api_token);

    //     // Verificar si el usuario y el token actual existen
    //     if ($user && $user->api_token) {
    //         \Log::info('Usuario y token actuales válidos.');
    //         // Obtener el token actual
    //         $currentTokenId = $event->user->api_token;

    //         // Revoke other tokens except the current one
    //         $tokens = PersonalAccessToken::where('tokenable_id', $user->id)
    //             ->where('id', '!=', $currentTokenId)
    //             ->get();

    //         foreach ($tokens as $token) {
    //             $token->delete();
    //         }
    //     } else {
    //         \Log::warning('Usuario o token actual nulos.');
    //     }
    // }

    // public function handle(Authenticated $event)
    // {
    //     $user = $event->user;

    //     // Revoke other tokens except the current one
    //     $tokens = PersonalAccessToken::where('tokenable_id', $user->id)
    //         ->where('id', '!=', $event->user->currentAccessToken()->id)
    //         ->get();

    //     foreach ($tokens as $token) {
    //         $token->delete();
    //     }
    // }
}
