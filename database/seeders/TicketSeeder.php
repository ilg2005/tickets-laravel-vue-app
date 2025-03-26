<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Truncate the tickets table
         // DB::table('tickets')->truncate();

        // Ensure you have users in the database before running this
        if (User::count() > 0) {
            Ticket::factory()->count(50)->create();
        } else {
            $this->command->info('No users found. Please seed users first.');
        }
    }
}
