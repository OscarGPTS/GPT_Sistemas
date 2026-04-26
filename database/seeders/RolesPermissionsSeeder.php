<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'label' => 'Administrador', 'description' => 'Acceso total al sistema'],
            ['name' => 'it', 'label' => 'Sistemas / TI', 'description' => 'Gestión técnica, activos y tickets'],
            ['name' => 'manager', 'label' => 'Jefe de área', 'description' => 'Aprobaciones del área'],
            ['name' => 'hr', 'label' => 'Recursos Humanos', 'description' => 'Gestión de personas y procesos'],
            ['name' => 'user', 'label' => 'Usuario estándar', 'description' => 'Solicita tickets y consulta sus activos'],
            ['name' => 'auditor', 'label' => 'Auditor', 'description' => 'Solo lectura y reportes'],
        ];

        foreach ($roles as $data) {
            Role::updateOrCreate(['name' => $data['name']], $data);
        }

        $permissions = [
            // Assets
            ['name' => 'assets.view', 'label' => 'Ver activos', 'group' => 'assets'],
            ['name' => 'assets.create', 'label' => 'Crear activos', 'group' => 'assets'],
            ['name' => 'assets.update', 'label' => 'Actualizar activos', 'group' => 'assets'],
            ['name' => 'assets.delete', 'label' => 'Eliminar activos', 'group' => 'assets'],
            ['name' => 'assets.import', 'label' => 'Importar activos', 'group' => 'assets'],
            // Assignments
            ['name' => 'assignments.view', 'label' => 'Ver asignaciones', 'group' => 'assignments'],
            ['name' => 'assignments.manage', 'label' => 'Gestionar asignaciones', 'group' => 'assignments'],
            // Maintenance
            ['name' => 'maintenance.view', 'label' => 'Ver mantenimientos', 'group' => 'maintenance'],
            ['name' => 'maintenance.manage', 'label' => 'Gestionar mantenimientos', 'group' => 'maintenance'],
            // Tickets
            ['name' => 'tickets.view', 'label' => 'Ver tickets', 'group' => 'tickets'],
            ['name' => 'tickets.create', 'label' => 'Crear tickets', 'group' => 'tickets'],
            ['name' => 'tickets.manage', 'label' => 'Gestionar tickets', 'group' => 'tickets'],
            // Workflows
            ['name' => 'workflows.view', 'label' => 'Ver flujos', 'group' => 'workflows'],
            ['name' => 'workflows.manage', 'label' => 'Gestionar flujos', 'group' => 'workflows'],
            ['name' => 'approvals.view', 'label' => 'Ver aprobaciones', 'group' => 'workflows'],
            ['name' => 'approvals.decide', 'label' => 'Aprobar/Rechazar', 'group' => 'workflows'],
            // Reports
            ['name' => 'reports.view', 'label' => 'Ver reportes', 'group' => 'reports'],
            ['name' => 'reports.export', 'label' => 'Exportar reportes', 'group' => 'reports'],
            // Users & roles
            ['name' => 'users.view', 'label' => 'Ver usuarios', 'group' => 'users'],
            ['name' => 'users.manage', 'label' => 'Gestionar usuarios', 'group' => 'users'],
            ['name' => 'audit.view', 'label' => 'Ver auditoría', 'group' => 'audit'],
        ];

        foreach ($permissions as $data) {
            Permission::updateOrCreate(['name' => $data['name']], $data);
        }

        $map = [
            'admin' => Permission::pluck('id')->all(),
            'it' => Permission::whereIn('group', ['assets', 'assignments', 'maintenance', 'tickets', 'reports', 'workflows'])
                ->pluck('id')->all(),
            'manager' => Permission::whereIn('name', [
                'assets.view', 'assignments.view', 'tickets.view', 'tickets.create',
                'approvals.view', 'approvals.decide', 'reports.view',
            ])->pluck('id')->all(),
            'hr' => Permission::whereIn('name', [
                'users.view', 'approvals.view', 'approvals.decide', 'reports.view',
            ])->pluck('id')->all(),
            'user' => Permission::whereIn('name', [
                'assets.view', 'tickets.view', 'tickets.create', 'assignments.view',
            ])->pluck('id')->all(),
            'auditor' => Permission::whereIn('name', [
                'assets.view', 'assignments.view', 'maintenance.view', 'tickets.view',
                'workflows.view', 'approvals.view', 'reports.view', 'audit.view', 'users.view',
            ])->pluck('id')->all(),
        ];

        foreach ($map as $role => $permissionIds) {
            Role::where('name', $role)->first()?->permissions()->sync($permissionIds);
        }
    }
}
