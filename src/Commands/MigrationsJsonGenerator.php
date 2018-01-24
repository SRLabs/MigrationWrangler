<?php

namespace SRLabs\MigrationWrangler\Commands;

use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Migrations\Migrator;

class MigrationsJsonGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrations:generate
        {--path= : The path of migrations files to be checked.}
        {--filepath= : The destination for the json file.}
        {--pretty : Write the json with a readable structure.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a migrations json file from the current migration files.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Resolve the path to the database migration files
        $path = $this->option('path') ?? database_path('migrations');

        // Generate an array of migration names and batch numbers
        $migrations = $this->getMigrationNames($path)
            ->reduce(function ($carry, $item) {
                $batch = count($carry) + 1;
                $id = count($carry) + 1;

                $carry[] = [
                    'id' => strval($id),
                    'migration' => $item,
                    'batch' => strval($batch),
                ];

                return $carry;
            }, []);

        // Establish the json encoding options
        $pretty = $this->option('pretty');
        $jsonOptions = 0;
        if ($pretty) {
            $jsonOptions = $jsonOptions | JSON_PRETTY_PRINT;
        }

        // Generate the filename for the new file
        $now = Carbon::now()->format('Ymd');
        $filename = "migrations_{$now}.json";

        // Establish the export path
        $filepath = ($this->option('filepath') ?? database_path()) . '/' . $filename;

        // Write the json to the file
        $file = fopen($filepath, 'w');
        fwrite($file, json_encode($migrations, $jsonOptions));
        fclose($file);

        // All set!
        $this->info("Generated new migration export file at {$filename}");
    }

    /**
     * Get names for all the migration files in a given path.
     * Borrowed from Illuminate\Database
     *
     * @param  string|array  $paths
     * @return array
     */
    public function getMigrationNames($paths)
    {
        return collect($paths)->flatMap(function ($path) {
            return $this->files->glob($path.'/*_*.php');
        })->filter()->map(function ($file) {
            return $this->getMigrationName($file);
        })->values();
    }

    /**
     * Get the name of the migration. Borrowed from Illuminate\Database
     *
     * @param  string  $path
     * @return string
     */
    public function getMigrationName($path)
    {
        return str_replace('.php', '', basename($path));
    }
}
