<?php

namespace App\Providers;

use App\Auth\UsuarioProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Request $request): void
    {
        if (! $this->app->runningInConsole()) {
            $baseUrl = rtrim($request->getSchemeAndHttpHost() . $request->getBaseUrl(), '/');

            URL::forceRootUrl($baseUrl ?: config('app.url'));

            // Forzar HTTPS cuando se usa ngrok o cuando la request es segura
            if ($request->isSecure() || $request->header('x-forwarded-proto') === 'https') {
                URL::forceScheme('https');
            }
        }

        Auth::provider('usuarios', function ($app, array $config) {
            return new UsuarioProvider($app['hash'], $config['model']);
        });
    }
}
