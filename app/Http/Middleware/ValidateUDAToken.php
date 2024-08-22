<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateUDAToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        
        // Obtén el token del encabezado de la solicitud
        $token = $request->header('Authorization');

        // Verifica si el token coincide con el token estático definido en el archivo .env
        if ($token !== 'Bearer '.config('app.token_uda')) {
            return response()->json(['message' => 'TOKEN INVÁLIDO'], 401);
        }

        return $next($request);
    }
}
