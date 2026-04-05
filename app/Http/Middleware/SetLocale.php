<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $choice = session('locale', 'ru');

        $laravelLocale = $choice === 'kz' ? 'kk' : 'ru';

        if (! in_array($laravelLocale, ['ru', 'kk'], true)) {
            $laravelLocale = 'ru';
        }

        app()->setLocale($laravelLocale);

        return $next($request);
    }
}
