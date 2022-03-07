<?php

namespace Tenant\Auth;

use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class TenantAuthProvider extends ServiceProvider
{

    public function boot(Kernel $kernel)
    {
        $this->offerPublishing();
        $this->app->singleton(
            Illuminate\Contracts\Debug\ExceptionHandler::class,
            App\Exceptions\Handler::class
        );
    }

    protected function offerPublishing() {
        // function not available and 'publish' not relevant in Lumen
        if (! function_exists('config_path')) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/tenant-auth.php' => config_path('tenant-auth.php'),
        ], 'config');
    }
}
