<?php

namespace SRLabs\MigrationWrangler\Commands;

use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Console\ConfirmableTrait;

class MigrationsExporter extends Command
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
    protected $signature = 'migrations:export
        {--database= : The database connection to use.}
        {--path= : The destination for the json file.}
        {--pretty : Write the json with a readable structure.}
        {--force : Force the operation to run when in production.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export the migrations table to a json file';

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

        // Establish the json encoding options
        $pretty = $this->option('pretty');
        $jsonOptions = 0;
        if ($pretty) {
            $jsonOptions = $jsonOptions | JSON_PRETTY_PRINT;
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

        // Fetch the migrations table data
        $migrations = DB::table('migrations')->get();

        // Generate the filename for the new file
        $now = Carbon::now()->format('Ymd');
        $filename = "{$database}_migrations_{$now}.json";

        // Establish the export path
        $path = ($this->option('path') ?? database_path()) . '/' . $filename;

        // Write the json to the file
        $file = fopen($path, 'w');
        fwrite($file, json_encode($migrations, $jsonOptions));
        fclose($file);

        // All set!
        $this->info("Exported \"{$database}\" migrations to {$filename}");
    }
}
