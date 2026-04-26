<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesPermissionsSeeder::class,
        ]);

        $categories = [
            'Laptops', 'Desktops', 'Monitores', 'Impresoras', 'Teléfonos',
            'Redes', 'Periféricos', 'Servidores', 'Software', 'Otros',
        ];
        foreach ($categories as $cat) {
            AssetCategory::firstOrCreate(['name' => $cat]);
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@gptservices.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'employee_code' => 'ADM-001',
                'department' => 'TI',
                'position' => 'Administrador de Sistemas',
                'is_active' => true,
            ]
        );
        $admin->roles()->sync([Role::where('name', 'admin')->value('id')]);

        $it = User::updateOrCreate(
            ['email' => 'ti@gptservices.com'],
            [
                'name' => 'Operador TI',
                'password' => Hash::make('password'),
                'employee_code' => 'TI-001',
                'department' => 'TI',
                'position' => 'Soporte TI',
                'is_active' => true,
                'manager_id' => $admin->id,
            ]
        );
        $it->roles()->sync([Role::where('name', 'it')->value('id')]);

        $manager = User::updateOrCreate(
            ['email' => 'gerente@gptservices.com'],
            [
                'name' => 'Gerente de Área',
                'password' => Hash::make('password'),
                'employee_code' => 'GER-001',
                'department' => 'Operaciones',
                'position' => 'Gerente',
                'is_active' => true,
            ]
        );
        $manager->roles()->sync([Role::where('name', 'manager')->value('id')]);

        $user = User::updateOrCreate(
            ['email' => 'usuario@gptservices.com'],
            [
                'name' => 'Usuario Estándar',
                'password' => Hash::make('password'),
                'employee_code' => 'USR-001',
                'department' => 'Operaciones',
                'position' => 'Analista',
                'is_active' => true,
                'manager_id' => $manager->id,
            ]
        );
        $user->roles()->sync([Role::where('name', 'user')->value('id')]);

        $this->call([
            WorkflowSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
