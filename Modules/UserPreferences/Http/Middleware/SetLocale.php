<?php

namespace Modules\UserPreferences\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Modules\UserPreferences\Entities\UserPrefence;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user() && !$request->user())
            throw new Error('Não está logado');

        $user = auth()->user() ? auth()->user() : $request->user();
        $userLang = $user->preferences->lang;

        $supportedLocales = config('languages');

        if (!$userLang || !in_array($userLang, $supportedLocales))
            $userLang = 'en';

        Cookie::queue('lang', $userLang, 60 * 24 * 365);

        App::setLocale($userLang);

        $carbonLocales = [
            'pt' => 'pt_BR',
            'en' => 'en',
            'es' => 'es',
        ];

        Carbon::setLocale($carbonLocales[$userLang]);

        return $next($request);
    }
}
