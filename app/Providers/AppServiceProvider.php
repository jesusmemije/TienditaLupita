<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;

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
    public function boot(): void
    {
        // Define public_html como la carpeta pública de Laravel
        $this->app->bind('path.public', function () {
            return base_path('../public_html');
        });

        // Configura la ubicación del directorio de Vite
        Vite::useBuildDirectory('build');
    }
}
