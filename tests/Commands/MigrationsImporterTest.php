<?php

use Illuminate\Support\Carbon;
use SRLabs\MigrationWrangler\Tests\TestCase;

class MigrationsImporterTest extends TestCase
{
    /** @test */
    public function it_replaces_migration_table_data()
    {
        $this->artisan('migrations:import', [
            'file' => __DIR__ . "/../Stubs/migrations.json",
        ])->expectsOutput("The \"testbench\" migrations table has been reset with 2 entries.");


        $this->assertEquals(2, DB::table('migrations')->count());
    }
}
