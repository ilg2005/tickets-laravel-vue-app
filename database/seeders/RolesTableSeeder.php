<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Truncate the roles table
        // DB::table('roles')->truncate();

        // Check if the role 'admin' exists, if not create it
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin']);
        }

        // Check if the role 'user' exists, if not create it
        if (!Role::where('name', 'user')->exists()) {
            Role::create(['name' => 'user']);
        }
    }
}



