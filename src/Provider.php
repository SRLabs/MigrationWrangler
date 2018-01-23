<?php

namespace SRLabs\MigrationWrangler;

use Illuminate\Support\ServiceProvider;
use SRLabs\MigrationWrangler\Commands\MigrationsImporter;
use SRLabs\MigrationWrangler\Commands\MigrationsExporter;

class Provider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MigrationsExporter::class,
                MigrationsImporter::class,
            ]);
        }
    }
}
