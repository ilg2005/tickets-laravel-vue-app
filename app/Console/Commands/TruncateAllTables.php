<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncateAllTables extends Command
{
    protected $signature = 'db:truncate-all';
    protected $description = 'Truncate all database tables';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Get all table names
        $tables = DB::select('SELECT tablename FROM pg_tables WHERE schemaname = \'public\';');
        $tables = array_map(function($table) { return $table->tablename; }, $tables);

        // Disable foreign key constraints
        DB::statement('SET CONSTRAINTS ALL DEFERRED;');

        // Truncate each table
        foreach ($tables as $table) {
            DB::statement("TRUNCATE TABLE {$table} RESTART IDENTITY CASCADE;");
        }

        // Re-enable foreign key constraints
        DB::statement('SET CONSTRAINTS ALL IMMEDIATE;');

        $this->info('All tables have been truncated.');
    }
}
