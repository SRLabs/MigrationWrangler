<?php

use SRLabs\MigrationWrangler\Tests\TestCase;

class MigrationsExporterTest extends TestCase
{
    /** @test */
    public function it_runs_stubbed_migrations()
    {
        // Artisan::call('migrate', array('--path' => './tests/Stubs', '--force' => true));

        dd(DB::table('migrations')->get());
    }
}
