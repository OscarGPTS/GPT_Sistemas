<?php

namespace Database\Seeders;

use App\Models\EquipmentRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProcurementSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPettyCashUser();
        $this->seedExpenseCategories();
        $this->seedExpenses();
        $this->seedEquipmentRequests();
    }

    private function seedPettyCashUser(): void
    {
        $role = Role::where('name', 'petty_cash')->first();
        if (! $role) {
            return;
        }

        $user = User::updateOrCreate(
            ['email' => 'caja@gptservices.com'],
            [
                'name' => 'Caja Chica TI',
                'password' => Hash::make('password'),
                'employee_code' => 'CAJ-001',
                'department' => 'Finanzas',
                'position' => 'Caja chica TI',
                'is_active' => true,
            ]
        );
        $user->roles()->syncWithoutDetaching([$role->id]);
    }

    private function seedExpenseCategories(): void
    {
        $categories = [
            ['name' => 'Internet',          'color' => '#3b82f6', 'monthly_budget' => 8000,  'description' => 'Servicios de internet corporativo y de respaldo'],
            ['name' => 'Licencias',         'color' => '#8b5cf6', 'monthly_budget' => 25000, 'description' => 'Software empresarial y SaaS'],
            ['name' => 'Hosting',           'color' => '#06b6d4', 'monthly_budget' => 12000, 'description' => 'AWS, Azure, hosting de aplicaciones'],
            ['name' => 'Hardware menor',    'color' => '#f59e0b', 'monthly_budget' => 6000,  'description' => 'Mouse, USB, cables, accesorios'],
            ['name' => 'Servicios externos','color' => '#10b981', 'monthly_budget' => 18000, 'description' => 'Consultoría, servicios profesionales'],
            ['name' => 'Telefonía',         'color' => '#ef4444', 'monthly_budget' => 5000,  'description' => 'Líneas móviles y planes de datos'],
            ['name' => 'Otros',             'color' => '#64748b', 'monthly_budget' => null,  'description' => 'Gastos misceláneos no categorizados'],
        ];
        foreach ($categories as $cat) {
            ExpenseCategory::updateOrCreate(['name' => $cat['name']], $cat);
        }
    }

    private function seedExpenses(): void
    {
        $cats = ExpenseCategory::all()->keyBy('name');
        $admin = User::where('email', 'admin@gptservices.com')->first();
        $petty = User::where('email', 'caja@gptservices.com')->first();
        $creator = $petty ?? $admin;
        if (! $creator) {
            return;
        }

        // Past 4 months of recurring expenses
        $recurring = [
            ['cat' => 'Internet',  'concept' => 'Internet dedicado · Telmex 200Mbps',         'amount' => 4500, 'supplier' => 'Telmex'],
            ['cat' => 'Internet',  'concept' => 'Internet de respaldo · Totalplay',           'amount' => 2800, 'supplier' => 'Totalplay'],
            ['cat' => 'Licencias', 'concept' => 'Microsoft 365 Business Premium · 25 users', 'amount' => 13725, 'supplier' => 'Microsoft / Licensing Online'],
            ['cat' => 'Licencias', 'concept' => 'Adobe Creative Cloud · 3 licencias',         'amount' => 4799, 'supplier' => 'Adobe'],
            ['cat' => 'Hosting',   'concept' => 'AWS EC2 + RDS · ambiente prod',              'amount' => 8450, 'supplier' => 'Amazon Web Services'],
            ['cat' => 'Hosting',   'concept' => 'Cloudflare Pro',                             'amount' => 380,  'supplier' => 'Cloudflare'],
            ['cat' => 'Telefonía', 'concept' => 'Plan corporativo · 12 líneas',               'amount' => 3600, 'supplier' => 'AT&T Empresas'],
        ];

        for ($monthOffset = 3; $monthOffset >= 0; $monthOffset--) {
            $date = Carbon::now()->subMonths($monthOffset);
            foreach ($recurring as $exp) {
                $cat = $cats->get($exp['cat']);
                if (! $cat) continue;
                Expense::create([
                    'category_id' => $cat->id,
                    'type' => 'recurring',
                    'concept' => $exp['concept'].' · '.$date->locale('es')->isoFormat('MMMM YYYY'),
                    'amount' => $exp['amount'] + rand(-50, 200),
                    'expense_date' => $date->copy()->day(rand(1, 5)),
                    'supplier' => $exp['supplier'],
                    'invoice_number' => 'F-'.$date->format('Ym').'-'.rand(1000, 9999),
                    'payment_method' => 'transfer',
                    'created_by' => $creator->id,
                ]);
            }
        }

        // Variable expenses this month
        $variable = [
            ['cat' => 'Hardware menor',    'concept' => '5 mouse Logitech M185 reemplazo', 'amount' => 1495, 'supplier' => 'CompuDabo'],
            ['cat' => 'Hardware menor',    'concept' => 'Cables HDMI + adaptadores USB-C', 'amount' => 890,  'supplier' => 'Office Depot'],
            ['cat' => 'Hardware menor',    'concept' => 'Limpiadores de pantalla y aire',  'amount' => 320,  'supplier' => 'Office Depot'],
            ['cat' => 'Servicios externos','concept' => 'Consultoría seguridad · pentest', 'amount' => 14500, 'supplier' => 'CyberCorp Security'],
            ['cat' => 'Servicios externos','concept' => 'Capacitación AWS · 3 personas',   'amount' => 8400, 'supplier' => 'AWS Training'],
            ['cat' => 'Otros',             'concept' => 'Reparación impresora piso 5',     'amount' => 1200, 'supplier' => 'TecnoSoporte'],
        ];
        foreach ($variable as $exp) {
            $cat = $cats->get($exp['cat']);
            if (! $cat) continue;
            Expense::create([
                'category_id' => $cat->id,
                'type' => 'variable',
                'concept' => $exp['concept'],
                'amount' => $exp['amount'],
                'expense_date' => Carbon::now()->subDays(rand(1, 25)),
                'supplier' => $exp['supplier'],
                'invoice_number' => 'F-'.now()->format('Ym').'-'.rand(1000, 9999),
                'payment_method' => collect(['transfer', 'card', 'cash'])->random(),
                'created_by' => $creator->id,
            ]);
        }
    }

    private function seedEquipmentRequests(): void
    {
        $userOps = User::where('email', 'fcastillo@gptservices.com')->first();
        $userVentas = User::where('email', 'crivera@gptservices.com')->first();
        $userMkt = User::where('email', 'sparedes@gptservices.com')->first();
        $admin = User::where('email', 'admin@gptservices.com')->first();
        $tiLead = User::where('email', 'knunez@gptservices.com')->first();
        $petty = User::where('email', 'caja@gptservices.com')->first();

        if (! $userOps || ! $admin) {
            return;
        }

        // Solicitud entregada (caso completo)
        $delivered = EquipmentRequest::create([
            'requester_id' => $userOps->id,
            'title' => 'Reemplazo de mouse y teclado',
            'justification' => 'Mi teclado actual tiene varias teclas que no responden. Necesito reemplazo para evitar afectación al trabajo diario.',
            'priority' => 'medium',
            'status' => 'delivered',
            'it_validator_id' => $tiLead?->id,
            'it_validated_at' => Carbon::now()->subDays(15),
            'preferred_supplier' => 'CompuDabo',
            'estimated_cost' => 1300,
            'buyer_id' => $petty?->id,
            'purchased_at' => Carbon::now()->subDays(10),
            'actual_supplier' => 'CompuDabo',
            'actual_cost' => 1245,
            'invoice_number' => 'F-2026-1820',
            'delivered_by' => $tiLead?->id,
            'delivered_to' => $userOps->id,
            'delivered_at' => Carbon::now()->subDays(7),
            'delivery_notes' => 'Equipo entregado en oficina, usuario confirmó funcionamiento.',
        ]);
        $delivered->items()->createMany([
            [
                'type' => 'mouse', 'description' => 'Mouse inalámbrico ergonómico',
                'suggested_model' => 'Logitech M720', 'quantity' => 1,
                'estimated_unit_cost' => 600, 'actual_unit_cost' => 545,
                'approved_model' => 'Logitech MX Anywhere 3S',
                'is_inventoriable' => false,
            ],
            [
                'type' => 'teclado', 'description' => 'Teclado USB en español',
                'suggested_model' => 'Logitech K120', 'quantity' => 1,
                'estimated_unit_cost' => 700, 'actual_unit_cost' => 700,
                'approved_model' => 'Logitech K120',
                'is_inventoriable' => false,
            ],
        ]);

        // Solicitud en compra
        if ($userVentas) {
            $purchasing = EquipmentRequest::create([
                'requester_id' => $userVentas->id,
                'title' => 'Headset profesional para llamadas con clientes',
                'justification' => 'Hago llamadas con clientes diariamente y mi laptop integrada tiene mala calidad de audio.',
                'priority' => 'high',
                'status' => 'purchasing',
                'it_validator_id' => $tiLead?->id,
                'it_validated_at' => Carbon::now()->subDays(2),
                'preferred_supplier' => 'PCEL',
                'estimated_cost' => 4800,
                'it_notes' => 'Aprobado modelo Jabra Evolve2 65 por buena reputación en el equipo.',
            ]);
            $purchasing->items()->create([
                'type' => 'headset', 'description' => 'Headset Bluetooth empresarial',
                'suggested_model' => 'Jabra Evolve2 65 USB-C',
                'approved_model' => 'Jabra Evolve2 65 USB-C',
                'quantity' => 1,
                'estimated_unit_cost' => 4800,
                'is_inventoriable' => true,
            ]);
        }

        // Solicitud en revisión (workflow corriendo)
        if ($userMkt) {
            $inReview = EquipmentRequest::create([
                'requester_id' => $userMkt->id,
                'title' => 'Tablet Wacom para diseño',
                'justification' => 'Estoy generando contenido visual diariamente. Una tablet de diseño aceleraría mi flujo en Photoshop e Illustrator.',
                'priority' => 'medium',
                'status' => 'in_review',
            ]);
            $inReview->items()->create([
                'type' => 'tablet', 'description' => 'Tablet de diseño profesional',
                'suggested_model' => 'Wacom Intuos Pro M',
                'quantity' => 1,
                'estimated_unit_cost' => 9800,
                'is_inventoriable' => true,
            ]);

            // Start the workflow for this one
            try {
                app(\App\Services\EquipmentRequestService::class)->submit($inReview->fresh());
            } catch (\Throwable $e) {
                // Ignore in seeder if anything missing
            }
        }

        // Borrador
        EquipmentRequest::create([
            'requester_id' => $userOps->id,
            'title' => 'Memoria RAM adicional para laptop',
            'justification' => 'Mi laptop tiene 8GB y se queda corta cuando uso múltiples herramientas.',
            'priority' => 'low',
            'status' => 'draft',
        ])->items()->create([
            'type' => 'RAM', 'description' => 'Módulo SODIMM DDR4 16GB 3200MHz',
            'suggested_model' => 'Crucial CT16G4SFRA32A',
            'quantity' => 1,
            'estimated_unit_cost' => 1850,
            'is_inventoriable' => false,
        ]);
    }
}
