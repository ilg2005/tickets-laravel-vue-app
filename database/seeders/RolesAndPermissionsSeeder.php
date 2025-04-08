<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Constants\Permissions;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the roles table
        // DB::table('roles')->truncate();

        // Создаем роли
        $adminRole = Role::firstOrCreate(['name' => Permissions::ROLE_ADMIN]);
        $userRole = Role::firstOrCreate(['name' => Permissions::ROLE_USER]);
        
        // Создаем разрешения
        foreach (Permissions::all() as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // Назначаем разрешения ролям
        $adminRole->syncPermissions(Permissions::adminPermissions());
        $userRole->syncPermissions(Permissions::userPermissions());
        
        // Очистка кэша разрешений
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
