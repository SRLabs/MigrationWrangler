<?php

use Illuminate\Support\Carbon;
use SRLabs\MigrationWrangler\Tests\TestCase;

class MigrationsExporterTest extends TestCase
{
    /** @test */
    public function it_exports_migrations_as_json()
    {
        $path = __DIR__ . "/../Database";
        Artisan::call('migrations:export', [
            '--filepath' => $path,
        ]);

        $now = Carbon::now()->format('Ymd');
        $expectedFile = __DIR__ . "/../Stubs/migrations.json";
        $expectedPrettyFile = __DIR__ . "/../Stubs/pretty_migrations.json";
        $generatedFile = $path . '/testbench_migrations_' . $now . ".json";
        $this->assertFileExists($generatedFile);
        $this->assertFileEquals($expectedFile, $generatedFile);
        $this->assertFileNotEquals($expectedPrettyFile, $generatedFile);
        unlink($generatedFile);
    }

    /** @test */
    public function it_exports_migrations_as_json_with_pretty_print()
    {
        $path = __DIR__ . "/../Database";
        Artisan::call('migrations:export', [
            '--filepath' => $path,
            '--pretty' => true
        ]);

        $now = Carbon::now()->format('Ymd');
        $expectedFile = __DIR__ . "/../Stubs/migrations.json";
        $expectedPrettyFile = __DIR__ . "/../Stubs/pretty_migrations.json";
        $generatedFile = $path . '/testbench_migrations_' . $now . ".json";
        $this->assertFileExists($generatedFile);
        $this->assertFileEquals($expectedPrettyFile, $generatedFile);
        $this->assertFileNotEquals($expectedFile, $generatedFile);
        unlink($generatedFile);
    }
}
