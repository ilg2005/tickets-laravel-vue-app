<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Удаляем вызовы truncate, так как migrate:fresh уже очистила таблицы
        // DB::table('followups')->truncate();
        // DB::table('tickets')->truncate(); 
        // DB::table('users')->truncate();
        
        // Здесь тоже удаляем, так как таблицы уже пустые
        // \App\Models\User::query()->delete();
        
        $password = '12345678';
        
        User::factory()->count(1)->admin()->create([
            'password' => $password
        ]);
        User::factory()->count(10)->user()->create([
            'password' => $password
        ]);
    }
}
