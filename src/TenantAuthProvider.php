<?php

namespace Tenant\Auth;

use Illuminate\Foundation\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Tenant\Auth\Middleware\TenantAuthMiddleware;

class TenantAuthProvider extends ServiceProvider
{

    public function boot(Kernel $kernel)
    {
        $this->offerPublishing();
        $kernel->pushMiddleware(TenantAuthMiddleware::class);
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