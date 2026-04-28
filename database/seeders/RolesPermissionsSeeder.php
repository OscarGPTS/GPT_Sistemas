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
            ['name' => 'petty_cash', 'label' => 'Caja Chica', 'description' => 'Registra compras y sube facturas'],
            ['name' => 'security_admin', 'label' => 'Admin Seguridad', 'description' => 'Gestor del vault de credenciales y cámaras'],
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
            // Projects
            ['name' => 'projects.view', 'label' => 'Ver proyectos', 'group' => 'projects'],
            ['name' => 'projects.create', 'label' => 'Crear proyectos', 'group' => 'projects'],
            ['name' => 'projects.update', 'label' => 'Actualizar proyectos', 'group' => 'projects'],
            ['name' => 'projects.delete', 'label' => 'Eliminar proyectos', 'group' => 'projects'],
            ['name' => 'projects.manage_members', 'label' => 'Gestionar miembros', 'group' => 'projects'],
            ['name' => 'project_requests.view', 'label' => 'Ver solicitudes', 'group' => 'projects'],
            ['name' => 'project_requests.create', 'label' => 'Crear solicitudes', 'group' => 'projects'],
            ['name' => 'tasks.view', 'label' => 'Ver tareas', 'group' => 'projects'],
            ['name' => 'tasks.manage', 'label' => 'Gestionar tareas', 'group' => 'projects'],
            // Equipment requests
            ['name' => 'equipment_requests.view', 'label' => 'Ver solicitudes de equipo', 'group' => 'procurement'],
            ['name' => 'equipment_requests.create', 'label' => 'Solicitar equipo', 'group' => 'procurement'],
            ['name' => 'equipment_requests.validate', 'label' => 'Validar solicitudes (TI)', 'group' => 'procurement'],
            ['name' => 'equipment_requests.purchase', 'label' => 'Registrar compras', 'group' => 'procurement'],
            ['name' => 'equipment_requests.deliver', 'label' => 'Registrar entregas', 'group' => 'procurement'],
            // Expenses
            ['name' => 'expenses.view', 'label' => 'Ver gastos', 'group' => 'finance'],
            ['name' => 'expenses.manage', 'label' => 'Registrar gastos', 'group' => 'finance'],
            ['name' => 'expense_categories.manage', 'label' => 'Gestionar categorías', 'group' => 'finance'],
            // Directory (locations + device users)
            ['name' => 'locations.view', 'label' => 'Ver ubicaciones', 'group' => 'directory'],
            ['name' => 'locations.manage', 'label' => 'Gestionar ubicaciones', 'group' => 'directory'],
            ['name' => 'device_users.view', 'label' => 'Ver usuarios de impresión', 'group' => 'directory'],
            ['name' => 'device_users.manage', 'label' => 'Gestionar usuarios de impresión', 'group' => 'directory'],
            // Access vault
            ['name' => 'accesses.view', 'label' => 'Ver accesos (enmascarado)', 'group' => 'security'],
            ['name' => 'accesses.create', 'label' => 'Crear accesos', 'group' => 'security'],
            ['name' => 'accesses.update', 'label' => 'Editar accesos', 'group' => 'security'],
            ['name' => 'accesses.delete', 'label' => 'Eliminar accesos', 'group' => 'security'],
            ['name' => 'accesses.reveal', 'label' => 'Revelar credenciales (con OTP)', 'group' => 'security'],
            ['name' => 'accesses.audit', 'label' => 'Ver auditoría de accesos', 'group' => 'security'],
            ['name' => 'access_types.manage', 'label' => 'Gestionar tipos de acceso', 'group' => 'security'],
            // Cameras
            ['name' => 'cameras.view', 'label' => 'Ver cámaras', 'group' => 'security'],
            ['name' => 'cameras.manage', 'label' => 'Gestionar cámaras', 'group' => 'security'],
        ];

        foreach ($permissions as $data) {
            Permission::updateOrCreate(['name' => $data['name']], $data);
        }

        $map = [
            'admin' => Permission::pluck('id')->all(),
            'it' => Permission::whereIn('group', ['assets', 'assignments', 'maintenance', 'tickets', 'reports', 'workflows', 'projects', 'procurement', 'finance', 'directory'])
                ->pluck('id')
                ->merge(Permission::whereIn('name', ['accesses.view', 'cameras.view', 'cameras.manage'])->pluck('id'))
                ->unique()->all(),
            'manager' => Permission::whereIn('name', [
                'assets.view', 'assignments.view', 'tickets.view', 'tickets.create',
                'approvals.view', 'approvals.decide', 'reports.view',
                'projects.view', 'projects.create', 'projects.update', 'projects.manage_members',
                'project_requests.view', 'project_requests.create',
                'tasks.view', 'tasks.manage',
                'equipment_requests.view', 'equipment_requests.create',
                'expenses.view',
            ])->pluck('id')->all(),
            'hr' => Permission::whereIn('name', [
                'users.view', 'approvals.view', 'approvals.decide', 'reports.view',
                'projects.view', 'project_requests.view',
                'equipment_requests.view',
            ])->pluck('id')->all(),
            'user' => Permission::whereIn('name', [
                'assets.view', 'tickets.view', 'tickets.create', 'assignments.view',
                'projects.view', 'project_requests.create', 'project_requests.view',
                'tasks.view',
                'equipment_requests.view', 'equipment_requests.create',
                'locations.view',
            ])->pluck('id')->all(),
            'auditor' => Permission::whereIn('name', [
                'assets.view', 'assignments.view', 'maintenance.view', 'tickets.view',
                'workflows.view', 'approvals.view', 'reports.view', 'audit.view', 'users.view',
                'projects.view', 'project_requests.view', 'tasks.view',
                'equipment_requests.view', 'expenses.view',
                'locations.view', 'device_users.view',
                'accesses.audit',
            ])->pluck('id')->all(),
            'petty_cash' => Permission::whereIn('name', [
                'equipment_requests.view', 'equipment_requests.purchase', 'equipment_requests.deliver',
                'expenses.view', 'expenses.manage', 'reports.view',
            ])->pluck('id')->all(),
            'security_admin' => Permission::whereIn('group', ['security'])
                ->pluck('id')
                ->merge(Permission::whereIn('name', ['locations.view', 'audit.view'])->pluck('id'))
                ->unique()->all(),
        ];

        foreach ($map as $role => $permissionIds) {
            Role::where('name', $role)->first()?->permissions()->sync($permissionIds);
        }
    }
}
