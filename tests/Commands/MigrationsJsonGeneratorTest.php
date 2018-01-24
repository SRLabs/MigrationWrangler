<?php

use Illuminate\Support\Carbon;
use SRLabs\MigrationWrangler\Tests\TestCase;

class MigrationsJsonGeneratorTest extends TestCase
{
    /** @test */
    public function it_replaces_migration_table_data()
    {
        $path = __DIR__ . "/../Database";

        Artisan::call('migrations:generate', [
            '--path' => $path,
            '--filepath' => $path,
        ]);

        $now = Carbon::now()->format('Ymd');
        $expectedFile = __DIR__ . "/../Stubs/migrations.json";
        $expectedPrettyFile = __DIR__ . "/../Stubs/pretty_migrations.json";
        $generatedFile = $path . '/migrations_' . $now . ".json";
        $this->assertFileExists($generatedFile);
        $this->assertFileEquals($expectedFile, $generatedFile);
        $this->assertFileNotEquals($expectedPrettyFile, $generatedFile);
        unlink($generatedFile);
    }
}
