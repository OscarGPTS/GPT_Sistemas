<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketHistory;
use App\Models\User;
use App\Models\WorkflowApproval;
use App\Models\WorkflowInstance;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedAssets();
        $this->seedAssignments();
        $this->seedMaintenance();
        $this->seedTickets();
        $this->seedWorkflowInstances();
    }

    private function seedUsers(): void
    {
        $roleIds = Role::pluck('id', 'name');

        $adminManager = User::where('email', 'admin@gptservices.com')->first();
        $gerente = User::where('email', 'gerente@gptservices.com')->first();

        $people = [
            // TI team
            ['code' => 'TI-002', 'name' => 'Ricardo Mendoza', 'email' => 'rmendoza@gptservices.com', 'dept' => 'TI', 'pos' => 'Especialista Soporte', 'role' => 'it', 'manager' => $adminManager?->id],
            ['code' => 'TI-003', 'name' => 'Karla Núñez', 'email' => 'knunez@gptservices.com', 'dept' => 'TI', 'pos' => 'Analista de Infraestructura', 'role' => 'it', 'manager' => $adminManager?->id],
            ['code' => 'TI-004', 'name' => 'Daniel Ortiz', 'email' => 'dortiz@gptservices.com', 'dept' => 'TI', 'pos' => 'DevOps Junior', 'role' => 'it', 'manager' => $adminManager?->id],
            // Finance
            ['code' => 'FIN-001', 'name' => 'Patricia Salgado', 'email' => 'psalgado@gptservices.com', 'dept' => 'Finanzas', 'pos' => 'Directora de Finanzas', 'role' => 'manager', 'manager' => null],
            ['code' => 'FIN-002', 'name' => 'José Ramírez', 'email' => 'jramirez@gptservices.com', 'dept' => 'Finanzas', 'pos' => 'Contador Senior', 'role' => 'user'],
            ['code' => 'FIN-003', 'name' => 'Andrea Solís', 'email' => 'asolis@gptservices.com', 'dept' => 'Finanzas', 'pos' => 'Analista Contable', 'role' => 'user'],
            // RH
            ['code' => 'RH-001', 'name' => 'Laura Velázquez', 'email' => 'lvelazquez@gptservices.com', 'dept' => 'Recursos Humanos', 'pos' => 'Gerente RH', 'role' => 'hr', 'manager' => null],
            ['code' => 'RH-002', 'name' => 'Mariana Torres', 'email' => 'mtorres@gptservices.com', 'dept' => 'Recursos Humanos', 'pos' => 'Reclutadora', 'role' => 'hr'],
            // Operaciones
            ['code' => 'OPS-002', 'name' => 'Fernando Castillo', 'email' => 'fcastillo@gptservices.com', 'dept' => 'Operaciones', 'pos' => 'Coordinador de Operaciones', 'role' => 'user', 'manager' => $gerente?->id],
            ['code' => 'OPS-003', 'name' => 'Alejandra Ruiz', 'email' => 'aruiz@gptservices.com', 'dept' => 'Operaciones', 'pos' => 'Analista de Procesos', 'role' => 'user', 'manager' => $gerente?->id],
            ['code' => 'OPS-004', 'name' => 'Héctor Domínguez', 'email' => 'hdominguez@gptservices.com', 'dept' => 'Operaciones', 'pos' => 'Supervisor', 'role' => 'user', 'manager' => $gerente?->id],
            // Ventas
            ['code' => 'SAL-001', 'name' => 'Eduardo Gálvez', 'email' => 'egalvez@gptservices.com', 'dept' => 'Ventas', 'pos' => 'Director Comercial', 'role' => 'manager'],
            ['code' => 'SAL-002', 'name' => 'Claudia Rivera', 'email' => 'crivera@gptservices.com', 'dept' => 'Ventas', 'pos' => 'Ejecutiva de Cuenta', 'role' => 'user'],
            ['code' => 'SAL-003', 'name' => 'Roberto Aguilar', 'email' => 'raguilar@gptservices.com', 'dept' => 'Ventas', 'pos' => 'Ejecutivo de Ventas', 'role' => 'user'],
            // Marketing
            ['code' => 'MKT-001', 'name' => 'Valeria Ibarra', 'email' => 'vibarra@gptservices.com', 'dept' => 'Marketing', 'pos' => 'Coordinadora de Marketing', 'role' => 'user'],
            ['code' => 'MKT-002', 'name' => 'Sergio Paredes', 'email' => 'sparedes@gptservices.com', 'dept' => 'Marketing', 'pos' => 'Diseñador Gráfico', 'role' => 'user'],
            // Auditor
            ['code' => 'AUD-001', 'name' => 'Gabriela Cano', 'email' => 'auditor@gptservices.com', 'dept' => 'Auditoría', 'pos' => 'Auditor Interno', 'role' => 'auditor'],
        ];

        // managers first pass (so other users can reference them by email)
        foreach ($people as $p) {
            $user = User::updateOrCreate(
                ['email' => $p['email']],
                [
                    'name' => $p['name'],
                    'password' => Hash::make('password'),
                    'employee_code' => $p['code'],
                    'department' => $p['dept'],
                    'position' => $p['pos'],
                    'is_active' => true,
                    'manager_id' => $p['manager'] ?? null,
                ]
            );
            if (isset($roleIds[$p['role']])) {
                $user->roles()->sync([$roleIds[$p['role']]]);
            }
        }

        // Fix manager relationships that depend on other seeded users
        $director = User::where('email', 'psalgado@gptservices.com')->first();
        if ($director) {
            User::whereIn('email', ['jramirez@gptservices.com', 'asolis@gptservices.com'])
                ->update(['manager_id' => $director->id]);
        }
        $rh = User::where('email', 'lvelazquez@gptservices.com')->first();
        if ($rh) {
            User::where('email', 'mtorres@gptservices.com')->update(['manager_id' => $rh->id]);
        }
        $comercial = User::where('email', 'egalvez@gptservices.com')->first();
        if ($comercial) {
            User::whereIn('email', ['crivera@gptservices.com', 'raguilar@gptservices.com', 'vibarra@gptservices.com', 'sparedes@gptservices.com'])
                ->update(['manager_id' => $comercial->id]);
        }
    }

    private function seedAssets(): void
    {
        $categories = AssetCategory::pluck('id', 'name');

        $catalog = [
            // Laptops
            ['cat' => 'Laptops', 'type' => 'laptop', 'brand' => 'Dell', 'model' => 'Latitude 5540', 'cost' => 24500, 'condition' => 'good',
             'specs' => ['cpu' => 'Intel i7-1365U', 'ram' => '16GB', 'disk' => '512GB SSD', 'os' => 'Windows 11 Pro']],
            ['cat' => 'Laptops', 'type' => 'laptop', 'brand' => 'Dell', 'model' => 'Latitude 7440', 'cost' => 32000, 'condition' => 'new',
             'specs' => ['cpu' => 'Intel i7-1365U', 'ram' => '32GB', 'disk' => '1TB SSD']],
            ['cat' => 'Laptops', 'type' => 'laptop', 'brand' => 'HP', 'model' => 'EliteBook 840 G10', 'cost' => 28990, 'condition' => 'good',
             'specs' => ['cpu' => 'Intel i7-1355U', 'ram' => '16GB', 'disk' => '512GB SSD']],
            ['cat' => 'Laptops', 'type' => 'laptop', 'brand' => 'HP', 'model' => 'ProBook 450 G10', 'cost' => 19800, 'condition' => 'good',
             'specs' => ['cpu' => 'Intel i5-1335U', 'ram' => '8GB', 'disk' => '256GB SSD']],
            ['cat' => 'Laptops', 'type' => 'laptop', 'brand' => 'Lenovo', 'model' => 'ThinkPad T14 Gen 4', 'cost' => 27500, 'condition' => 'good',
             'specs' => ['cpu' => 'Intel i7-1355U', 'ram' => '16GB', 'disk' => '512GB SSD']],
            ['cat' => 'Laptops', 'type' => 'laptop', 'brand' => 'Lenovo', 'model' => 'ThinkPad X1 Carbon Gen 11', 'cost' => 42000, 'condition' => 'new',
             'specs' => ['cpu' => 'Intel i7-1365U', 'ram' => '32GB', 'disk' => '1TB SSD']],
            ['cat' => 'Laptops', 'type' => 'laptop', 'brand' => 'Apple', 'model' => 'MacBook Pro 14" M3', 'cost' => 48990, 'condition' => 'new',
             'specs' => ['cpu' => 'Apple M3 Pro', 'ram' => '18GB', 'disk' => '512GB SSD']],
            ['cat' => 'Laptops', 'type' => 'laptop', 'brand' => 'Apple', 'model' => 'MacBook Air 13" M2', 'cost' => 26990, 'condition' => 'good',
             'specs' => ['cpu' => 'Apple M2', 'ram' => '8GB', 'disk' => '256GB SSD']],

            // Desktops
            ['cat' => 'Desktops', 'type' => 'desktop', 'brand' => 'Dell', 'model' => 'OptiPlex 7010', 'cost' => 15800, 'condition' => 'good',
             'specs' => ['cpu' => 'Intel i5-13500', 'ram' => '16GB', 'disk' => '512GB SSD']],
            ['cat' => 'Desktops', 'type' => 'desktop', 'brand' => 'HP', 'model' => 'EliteDesk 800 G9', 'cost' => 17500, 'condition' => 'good',
             'specs' => ['cpu' => 'Intel i7-13700', 'ram' => '16GB', 'disk' => '1TB SSD']],
            ['cat' => 'Desktops', 'type' => 'desktop', 'brand' => 'Lenovo', 'model' => 'ThinkCentre M90s', 'cost' => 16900, 'condition' => 'fair',
             'specs' => ['cpu' => 'Intel i5-13500', 'ram' => '8GB', 'disk' => '256GB SSD']],

            // Monitores
            ['cat' => 'Monitores', 'type' => 'monitor', 'brand' => 'Dell', 'model' => 'UltraSharp U2723QE 27"', 'cost' => 12500, 'condition' => 'new',
             'specs' => ['size' => '27"', 'res' => '4K UHD', 'panel' => 'IPS']],
            ['cat' => 'Monitores', 'type' => 'monitor', 'brand' => 'LG', 'model' => '27UP850-W 27" 4K', 'cost' => 8990, 'condition' => 'good'],
            ['cat' => 'Monitores', 'type' => 'monitor', 'brand' => 'HP', 'model' => 'E24 G5 24"', 'cost' => 4800, 'condition' => 'good'],
            ['cat' => 'Monitores', 'type' => 'monitor', 'brand' => 'Samsung', 'model' => 'S8 S27B800 27"', 'cost' => 9500, 'condition' => 'good'],

            // Impresoras
            ['cat' => 'Impresoras', 'type' => 'printer', 'brand' => 'HP', 'model' => 'LaserJet Pro M404dn', 'cost' => 6500, 'condition' => 'good'],
            ['cat' => 'Impresoras', 'type' => 'printer', 'brand' => 'Brother', 'model' => 'MFC-L8900CDW', 'cost' => 15900, 'condition' => 'good'],
            ['cat' => 'Impresoras', 'type' => 'printer', 'brand' => 'Epson', 'model' => 'EcoTank L6290', 'cost' => 8900, 'condition' => 'new'],

            // Teléfonos
            ['cat' => 'Teléfonos', 'type' => 'phone', 'brand' => 'Apple', 'model' => 'iPhone 15 Pro 128GB', 'cost' => 23999, 'condition' => 'new'],
            ['cat' => 'Teléfonos', 'type' => 'phone', 'brand' => 'Samsung', 'model' => 'Galaxy S24 256GB', 'cost' => 21999, 'condition' => 'good'],
            ['cat' => 'Teléfonos', 'type' => 'phone', 'brand' => 'Motorola', 'model' => 'Edge 40 Pro', 'cost' => 14999, 'condition' => 'good'],

            // Redes
            ['cat' => 'Redes', 'type' => 'network', 'brand' => 'Cisco', 'model' => 'Catalyst 9200-24T', 'cost' => 58900, 'condition' => 'good'],
            ['cat' => 'Redes', 'type' => 'network', 'brand' => 'Ubiquiti', 'model' => 'UniFi U6-Pro AP', 'cost' => 3599, 'condition' => 'new'],
            ['cat' => 'Redes', 'type' => 'network', 'brand' => 'TP-Link', 'model' => 'Omada ER605 Router', 'cost' => 1999, 'condition' => 'good'],

            // Periféricos
            ['cat' => 'Periféricos', 'type' => 'peripheral', 'brand' => 'Logitech', 'model' => 'MX Master 3S', 'cost' => 2499, 'condition' => 'new'],
            ['cat' => 'Periféricos', 'type' => 'peripheral', 'brand' => 'Logitech', 'model' => 'MX Keys S', 'cost' => 2799, 'condition' => 'new'],
            ['cat' => 'Periféricos', 'type' => 'peripheral', 'brand' => 'Jabra', 'model' => 'Evolve2 65 Headset', 'cost' => 4800, 'condition' => 'good'],
            ['cat' => 'Periféricos', 'type' => 'peripheral', 'brand' => 'Webcam', 'model' => 'Logitech C920 HD Pro', 'cost' => 1599, 'condition' => 'good'],

            // Servidores
            ['cat' => 'Servidores', 'type' => 'server', 'brand' => 'Dell', 'model' => 'PowerEdge R750', 'cost' => 189000, 'condition' => 'good',
             'specs' => ['cpu' => '2x Intel Xeon Gold 6342', 'ram' => '128GB DDR4', 'disk' => '4x 2TB SSD RAID-10']],
            ['cat' => 'Servidores', 'type' => 'server', 'brand' => 'HP', 'model' => 'ProLiant DL380 Gen11', 'cost' => 215000, 'condition' => 'new'],

            // Software
            ['cat' => 'Software', 'type' => 'software', 'brand' => 'Microsoft', 'model' => 'Microsoft 365 Business Premium (Licencia anual)', 'cost' => 5490, 'condition' => 'new'],
            ['cat' => 'Software', 'type' => 'software', 'brand' => 'Adobe', 'model' => 'Creative Cloud All Apps (Licencia anual)', 'cost' => 18990, 'condition' => 'new'],
            ['cat' => 'Software', 'type' => 'software', 'brand' => 'Atlassian', 'model' => 'Jira Software Premium', 'cost' => 12500, 'condition' => 'new'],
        ];

        $locations = [
            'Oficina CDMX - Piso 5',
            'Oficina CDMX - Piso 6',
            'Oficina Guadalajara',
            'Oficina Monterrey',
            'Site Principal - DataCenter',
            'Almacén TI',
            'Home Office',
        ];
        $suppliers = ['CompuDabo', 'Office Depot Business', 'CT Internacional', 'Ingram Micro', 'PCEL', 'Dell México', 'Licensing Online'];

        $counter = 1;
        foreach ($catalog as $item) {
            // create 1-3 units of each model
            $units = rand(1, 3);
            for ($i = 0; $i < $units; $i++) {
                $purchaseDate = Carbon::now()->subDays(rand(30, 1200));
                Asset::updateOrCreate(
                    ['internal_code' => sprintf('GPT-%03d', $counter++)],
                    [
                        'category_id' => $categories[$item['cat']] ?? null,
                        'type' => $item['type'],
                        'brand' => $item['brand'],
                        'model' => $item['model'],
                        'serial_number' => strtoupper(substr(md5($item['brand'].$item['model'].$i.microtime()), 0, 12)),
                        'description' => $item['model'],
                        'location' => $locations[array_rand($locations)],
                        'status' => 'available',
                        'condition' => $item['condition'] ?? 'good',
                        'purchase_date' => $purchaseDate->format('Y-m-d'),
                        'purchase_cost' => $item['cost'],
                        'supplier' => $suppliers[array_rand($suppliers)],
                        'warranty_until' => $purchaseDate->copy()->addYears(rand(1, 3))->format('Y-m-d'),
                        'specs' => $item['specs'] ?? null,
                    ]
                );
            }
        }
    }

    private function seedAssignments(): void
    {
        $users = User::whereIn('department', ['Finanzas', 'Operaciones', 'Ventas', 'Marketing', 'Recursos Humanos', 'TI'])
            ->where('is_active', true)
            ->get();
        $admin = User::where('email', 'admin@gptservices.com')->first();

        // Assign laptops + peripherals to each user
        $available = Asset::where('status', 'available')->whereIn('type', ['laptop', 'monitor', 'peripheral', 'phone'])->get();
        $used = [];

        foreach ($users as $user) {
            // 1 laptop
            $laptop = $available->where('type', 'laptop')->whereNotIn('id', $used)->first();
            if ($laptop) {
                $this->createAssignment($laptop, $user, $admin, rand(30, 400));
                $used[] = $laptop->id;
            }
            // 1 monitor (70% chance)
            if (rand(1, 10) <= 7) {
                $monitor = $available->where('type', 'monitor')->whereNotIn('id', $used)->first();
                if ($monitor) {
                    $this->createAssignment($monitor, $user, $admin, rand(30, 350));
                    $used[] = $monitor->id;
                }
            }
            // 1 peripheral (60% chance)
            if (rand(1, 10) <= 6) {
                $per = $available->where('type', 'peripheral')->whereNotIn('id', $used)->first();
                if ($per) {
                    $this->createAssignment($per, $user, $admin, rand(30, 300));
                    $used[] = $per->id;
                }
            }
        }

        // Create a couple of historic returned assignments for realism
        $laptop = Asset::where('type', 'laptop')->where('status', 'available')->first();
        $someUser = User::where('email', 'jramirez@gptservices.com')->first();
        if ($laptop && $someUser) {
            AssetAssignment::create([
                'asset_id' => $laptop->id,
                'user_id' => $someUser->id,
                'assigned_by' => $admin?->id,
                'assigned_at' => Carbon::now()->subDays(600),
                'returned_at' => Carbon::now()->subDays(200),
                'assignment_reason' => 'Reemplazo programado',
                'return_reason' => 'Ciclo de renovación',
                'assignment_location' => 'Oficina CDMX - Piso 5',
                'is_active' => false,
            ]);
        }
    }

    private function createAssignment(Asset $asset, User $user, ?User $admin, int $daysAgo): void
    {
        AssetAssignment::create([
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'assigned_by' => $admin?->id,
            'assigned_at' => Carbon::now()->subDays($daysAgo),
            'assignment_reason' => 'Asignación inicial a colaborador',
            'assignment_location' => $asset->location,
            'condition_out' => $asset->condition,
            'is_active' => true,
        ]);
        $asset->update(['status' => 'assigned']);
    }

    private function seedMaintenance(): void
    {
        $laptops = Asset::whereIn('type', ['laptop', 'desktop'])->take(12)->get();
        $server = Asset::where('type', 'server')->first();
        $ti = User::where('email', 'rmendoza@gptservices.com')->first();

        // Schedules
        foreach ($laptops->take(6) as $asset) {
            MaintenanceSchedule::create([
                'asset_id' => $asset->id,
                'type' => 'preventive',
                'title' => 'Limpieza interna y pasta térmica',
                'description' => 'Mantenimiento preventivo anual: limpieza, pasta térmica, respaldo de configuración.',
                'frequency' => 'annual',
                'next_due_at' => Carbon::now()->addDays(rand(-15, 90))->format('Y-m-d'),
                'responsible_id' => $ti?->id,
                'is_active' => true,
            ]);
        }
        if ($server) {
            MaintenanceSchedule::create([
                'asset_id' => $server->id,
                'type' => 'preventive',
                'title' => 'Mantenimiento trimestral del servidor',
                'description' => 'Revisión de logs, respaldos, firmware y limpieza.',
                'frequency' => 'quarterly',
                'next_due_at' => Carbon::now()->addDays(15)->format('Y-m-d'),
                'responsible_id' => $ti?->id,
                'is_active' => true,
            ]);
        }

        // Completed records
        foreach ($laptops->take(4) as $asset) {
            MaintenanceRecord::create([
                'asset_id' => $asset->id,
                'type' => 'preventive',
                'status' => 'completed',
                'title' => 'Mantenimiento preventivo',
                'description' => 'Limpieza y actualización de sistema operativo.',
                'scheduled_date' => Carbon::now()->subDays(rand(60, 300))->format('Y-m-d'),
                'performed_date' => Carbon::now()->subDays(rand(30, 250))->format('Y-m-d'),
                'responsible_id' => $ti?->id,
                'provider' => 'TI Interno',
                'cost' => rand(0, 800),
                'result_notes' => 'Equipo funcionando correctamente tras el mantenimiento.',
            ]);
        }
        // Upcoming
        foreach ($laptops->slice(4, 4) as $asset) {
            MaintenanceRecord::create([
                'asset_id' => $asset->id,
                'type' => 'preventive',
                'status' => 'scheduled',
                'title' => 'Mantenimiento programado',
                'description' => 'Limpieza interna y revisión.',
                'scheduled_date' => Carbon::now()->addDays(rand(1, 25))->format('Y-m-d'),
                'responsible_id' => $ti?->id,
            ]);
        }
        // A corrective
        $brokenLaptop = $laptops->last();
        if ($brokenLaptop) {
            MaintenanceRecord::create([
                'asset_id' => $brokenLaptop->id,
                'type' => 'corrective',
                'status' => 'in_progress',
                'title' => 'Cambio de batería',
                'description' => 'La batería no carga. Requiere reemplazo.',
                'scheduled_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
                'responsible_id' => $ti?->id,
                'provider' => 'Centro Dell México',
                'cost' => 2800,
            ]);
        }
    }

    private function seedTickets(): void
    {
        $requesters = User::whereIn('email', [
            'jramirez@gptservices.com', 'asolis@gptservices.com', 'fcastillo@gptservices.com',
            'aruiz@gptservices.com', 'crivera@gptservices.com', 'vibarra@gptservices.com',
        ])->get();
        $assignees = User::whereIn('email', [
            'rmendoza@gptservices.com', 'knunez@gptservices.com', 'ti@gptservices.com',
        ])->get();

        $tickets = [
            ['type' => 'incident',   'priority' => 'high',   'status' => 'in_progress', 'subject' => 'Mi laptop no enciende después de actualización',     'description' => 'Actualicé Windows anoche y esta mañana la laptop no pasa del logo. Ya la dejé reposar pero sigue igual. Es urgente porque tengo junta a las 11.'],
            ['type' => 'request',    'priority' => 'medium', 'status' => 'open',        'subject' => 'Solicito licencia de Adobe Illustrator',            'description' => 'Como parte del nuevo proyecto de rebranding necesito acceso a Illustrator por al menos 3 meses. Lo necesito idealmente antes del 30 del mes.'],
            ['type' => 'incident',   'priority' => 'urgent', 'status' => 'resolved',    'subject' => 'Correo corporativo no sincroniza en Outlook',       'description' => 'Desde ayer en la tarde mi Outlook no está recibiendo correos. He revisado la bandeja web y sí llegan allí.'],
            ['type' => 'request',    'priority' => 'low',    'status' => 'open',        'subject' => 'Solicito segundo monitor para home office',         'description' => 'Llevo trabajando 2 meses desde casa y me sería muy útil un monitor extra. Tengo uno en la oficina sin uso.'],
            ['type' => 'maintenance','priority' => 'medium', 'status' => 'in_progress', 'subject' => 'Laptop con ruido fuerte del ventilador',            'description' => 'Desde hace una semana se escucha muy fuerte el ventilador. Creo que necesita limpieza interna.'],
            ['type' => 'incident',   'priority' => 'medium', 'status' => 'on_hold',     'subject' => 'No puedo imprimir en la impresora del piso 5',      'description' => 'La impresora LaserJet del piso 5 no aparece en mi lista de impresoras. Antes funcionaba.'],
            ['type' => 'request',    'priority' => 'medium', 'status' => 'open',        'subject' => 'Configurar VPN para trabajo remoto',                'description' => 'Voy a trabajar remoto la próxima semana y necesito la VPN configurada. Ya tengo aprobación de mi jefe.'],
            ['type' => 'incident',   'priority' => 'low',    'status' => 'closed',      'subject' => 'Wi-Fi lento en sala de juntas Norte',               'description' => 'En esa sala la conexión cae mucho. No logro hacer videollamadas estables.'],
        ];

        foreach ($tickets as $i => $t) {
            $requester = $requesters->random();
            $assignee = in_array($t['status'], ['open']) ? null : $assignees->random();

            $ticket = Ticket::create(array_merge($t, [
                'requester_id' => $requester->id,
                'assignee_id' => $assignee?->id,
                'asset_id' => null,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 5)),
                'resolved_at' => in_array($t['status'], ['resolved', 'closed']) ? Carbon::now()->subDays(rand(1, 5)) : null,
                'closed_at' => $t['status'] === 'closed' ? Carbon::now()->subDays(rand(1, 3)) : null,
            ]));

            // History
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $requester->id,
                'action' => 'created',
                'data' => ['priority' => $t['priority']],
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->created_at,
            ]);
            if ($assignee) {
                TicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $assignee->id,
                    'action' => 'assigned',
                    'data' => ['to' => $assignee->id],
                ]);
            }

            // Comments
            if ($assignee) {
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $assignee->id,
                    'body' => '¡Hola! Ya tomé tu ticket. Voy a revisarlo y te mantengo informado.',
                    'is_internal' => false,
                    'created_at' => $ticket->created_at->copy()->addMinutes(30),
                    'updated_at' => $ticket->created_at->copy()->addMinutes(30),
                ]);
            }
            if ($t['status'] === 'resolved' || $t['status'] === 'closed') {
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $assignee?->id ?? $requester->id,
                    'body' => 'Problema resuelto. Marcado como cerrado. Cualquier recurrencia avísenme.',
                    'is_internal' => false,
                ]);
            }
        }
    }

    private function seedWorkflowInstances(): void
    {
        // Already one instance exists from the engine test, let's add one pending and one rejected
        $workflow = \App\Models\Workflow::where('slug', 'equipment-request')->first();
        if (! $workflow) {
            return;
        }
        $engine = app(\App\Services\WorkflowEngine::class);

        $requester1 = User::where('email', 'crivera@gptservices.com')->first();
        if ($requester1) {
            $engine->start($workflow, $requester1, null, [
                'equipo_solicitado' => 'Laptop HP EliteBook 840',
                'justificacion' => 'Reemplazo del equipo actual por fallas frecuentes.',
            ]);
        }

        $requester2 = User::where('email', 'vibarra@gptservices.com')->first();
        if ($requester2) {
            $inst = $engine->start($workflow, $requester2, null, [
                'equipo_solicitado' => 'iPad Pro 12.9" + Apple Pencil',
                'justificacion' => 'Presentaciones a clientes en sitio.',
            ]);
            // Reject at first step via admin
            $admin = User::where('email', 'admin@gptservices.com')->first();
            if ($admin) {
                $engine->decide($inst, $admin, 'rejected', 'Presupuesto agotado del Q actual. Reconsiderar próximo trimestre.');
            }
        }
    }
}
