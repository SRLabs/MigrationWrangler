<?php

namespace SRLabs\MigrationWrangler\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Console\ConfirmableTrait;

class MigrationsImporter extends Command
{
    /**
     * Prompt the user to confirm this action if they are in a prodiction env
     */
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrations:import
        {file}
        {--database= : The database connection to use.}
        {--force : Force the operation to run when in production.}
        {--pretend : Simulate the import without making real changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop the existing migrations table and replace it with data from a json file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Ask the user for confirmation if we are in a production environment
        if (! $this->confirmToProceed()) {
            return;
        }

        // Which database connection shall we use?
        $database = $this->option('database') ?? config('database.default');

        // Ensure that the specified connection exists
        if (!config('database.connections.' . $database)) {
            $this->error("You have specified an invalid connection: \"{$database}\"");
            return 1;
        }

        // Ensure that the specified database has a migrations table
        if (! Schema::setConnection(DB::connection($database))->hasTable('migrations')) {
            $this->error("The \"{$database}\" database does not have a migrations table.");
            return 1;
        }

        // Retrieve the name of the json file we will be working with
        $filename = $this->argument('file');
        $path = null;

        // Ensure that the file exists
        if (file_exists(base_path($filename))) {
            $path = base_path($filename);
        } elseif (file_exists($filename)) {
            $path = $filename;
        }

        // Were we able to derive the file path?
        if (!$path) {
            $this->error("You have specified an invalid file.");
            return 1;
        }

        // Read the file
        $contents = file_get_contents($path);

        // Decode the json
        $migrations = collect(json_decode($contents));

        // Are there records we can work with?
        if ($migrations->isEmpty()) {
            $this->error("No valid migration info found");
            return 1;
        }

        // We are expecting the decoded values to be objects
        if (! is_object($migrations->first())) {
            $this->error("The migration data is expected to be an object");
            return 1;
        }

        // Ensure those objects have the expected properties
        $props = get_object_vars($migrations->first());
        if (!array_key_exists('batch', $props) || ! array_key_exists('migration', $props)) {
            $this->error("The json format could not be interpreted correctly.");
            return 1;
        }

        // Is this a dry-run?
        $pretend = $this->option('pretend');

        // Write the new migration table data
        if (! $pretend) {
            $this->writeNewMigrations($migrations);
        }

        // All set
        $this->info("The \"{$database}\" migrations table has been reset with {$migrations->count()} entries.");
    }

    /**
     * Write new data to the migrations table, replacing the current content
     *
     * @param Collection $migrations
     * @return void
     */
    protected function writeNewMigrations($migrations)
    {
        // Truncate the existing migrations
        DB::table('migrations')->truncate();

        // Insert the new migration data
        $migrations->each(function ($row) {
            DB::table('migrations')->insert([
                'migration' => $row->migration,
                'batch' => $row->batch
            ]);
        });
    }
}
