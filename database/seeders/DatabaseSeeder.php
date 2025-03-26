<?php

namespace Database\Seeders;

// use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //  $this->call([
        //     RolesTableSeeder::class,
        //     UserSeeder::class,
        //     TicketSeeder::class,
        // ]);

        // Disable foreign key checks before truncating tables
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate the tables
        // DB::table('role_user')->truncate(); // If you have a pivot table for roles
        DB::table('model_has_roles')->truncate(); // If you have this table for Spatie roles
        DB::table('model_has_permissions')->truncate(); // If you have this table for Spatie permissions

        // Call individual seeders
        $this->call([
            // RolesTableSeeder::class,
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            TicketSeeder::class,
        ]);

        // Enable foreign key checks after truncating tables
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
