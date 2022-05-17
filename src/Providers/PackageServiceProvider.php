<?php
namespace Package\Kennofizet\EmailInternal\Providers;

use Illuminate\Support\ServiceProvider;

class PackageServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../migrations');
    }
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/email_internal.php','email_internal'
        );
    }
}
