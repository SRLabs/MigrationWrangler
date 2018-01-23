<?php

use Illuminate\Support\Carbon;
use SRLabs\MigrationWrangler\Tests\TestCase;

class MigrationsImporterTest extends TestCase
{
    /** @test */
    public function it_replaces_migration_table_data()
    {
        Artisan::call('migrations:import', [
            'file' => __DIR__ . "/../Stubs/migrations.json",
        ]);

        $this->assertEquals("The \"testbench\" migrations table has been reset with 2 entries.\n", Artisan::output());
        $this->assertEquals(2, DB::table('migrations')->count());
    }
}
