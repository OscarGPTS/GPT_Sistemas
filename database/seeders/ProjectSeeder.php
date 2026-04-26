<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectRequest;
use App\Models\Task;
use App\Models\TaskChecklist;
use App\Models\TaskChecklistItem;
use App\Models\User;
use App\Services\ProjectService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(ProjectService::class);

        $admin = User::where('email', 'admin@gptservices.com')->first();
        $manager = User::where('email', 'gerente@gptservices.com')->first();
        $director = User::where('email', 'psalgado@gptservices.com')->first();
        $ti = User::where('email', 'rmendoza@gptservices.com')->first();
        $tiLead = User::where('email', 'knunez@gptservices.com')->first();
        $marketing = User::where('email', 'vibarra@gptservices.com')->first();
        $design = User::where('email', 'sparedes@gptservices.com')->first();

        $projects = [
            [
                'name' => 'Renovación de equipos cómputo Q3',
                'description' => 'Plan trimestral de renovación de laptops para usuarios con equipos mayores a 4 años. Incluye levantamiento, compra, configuración y entrega.',
                'area' => 'TI',
                'manager' => $tiLead,
                'members' => [$ti, $admin],
                'status' => 'in_progress',
                'priority' => 'high',
                'color' => '#6366f1',
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->addDays(60),
                'budget' => 480000,
                'tasks' => [
                    ['col' => 'done',        'title' => 'Levantamiento de inventario actual',   'priority' => 'high',   'assignee' => $ti, 'days' => -28],
                    ['col' => 'done',        'title' => 'Cotización con 3 proveedores',         'priority' => 'high',   'assignee' => $tiLead, 'days' => -20],
                    ['col' => 'done',        'title' => 'Aprobación de presupuesto',            'priority' => 'urgent', 'assignee' => $admin, 'days' => -15],
                    ['col' => 'in_review',   'title' => 'Confirmar orden de compra',            'priority' => 'high',   'assignee' => $tiLead, 'days' => -5,
                     'tags' => ['compras','proveedor'],
                     'checklist' => [['Validar especificaciones', true], ['Firmar OC', false], ['Programar entrega', false]]],
                    ['col' => 'in_progress', 'title' => 'Preparar imágenes de Windows 11',      'priority' => 'medium', 'assignee' => $ti, 'days' => -3,
                     'tags' => ['ti','despliegue'],
                     'checklist' => [['Imagen base', true], ['Apps corporativas', true], ['Antivirus', false], ['Pruebas en piloto', false]]],
                    ['col' => 'pending',     'title' => 'Coordinar entrega con usuarios finales','priority'=> 'medium', 'assignee' => $ti, 'days' => 5],
                    ['col' => 'pending',     'title' => 'Migración de datos a nuevos equipos',  'priority' => 'high',   'assignee' => $ti, 'days' => 10],
                    ['col' => 'backlog',     'title' => 'Reciclaje de equipos antiguos',        'priority' => 'low',    'assignee' => null, 'days' => 30],
                ],
            ],
            [
                'name' => 'Rediseño de página corporativa',
                'description' => 'Renovación de la página web corporativa con enfoque mobile-first, mejor SEO y nuevo branding.',
                'area' => 'Marketing',
                'manager' => $marketing,
                'members' => [$design, $director],
                'status' => 'in_progress',
                'priority' => 'medium',
                'color' => '#10b981',
                'start_date' => Carbon::now()->subDays(15),
                'end_date' => Carbon::now()->addDays(75),
                'budget' => 220000,
                'tasks' => [
                    ['col' => 'done',        'title' => 'Análisis de la página actual y referencias', 'priority' => 'medium', 'assignee' => $marketing, 'days' => -14],
                    ['col' => 'in_progress', 'title' => 'Wireframes en Figma',                       'priority' => 'high',   'assignee' => $design, 'days' => -3,
                     'tags' => ['diseño','figma'],
                     'checklist' => [['Home', true], ['Servicios', true], ['Casos de éxito', false], ['Contacto', false]]],
                    ['col' => 'in_progress', 'title' => 'Definir paleta cromática y tipografías',    'priority' => 'medium', 'assignee' => $design, 'days' => -2],
                    ['col' => 'pending',     'title' => 'Aprobar mockups con dirección',             'priority' => 'high',   'assignee' => $marketing, 'days' => 4,
                     'tags' => ['aprobación']],
                    ['col' => 'pending',     'title' => 'Redactar copy para todas las secciones',    'priority' => 'medium', 'assignee' => $marketing, 'days' => 7],
                    ['col' => 'backlog',     'title' => 'Implementación frontend',                   'priority' => 'medium', 'assignee' => null, 'days' => 30,
                     'tags' => ['desarrollo']],
                    ['col' => 'backlog',     'title' => 'Migración de contenido',                    'priority' => 'low',    'assignee' => null, 'days' => 45],
                    ['col' => 'backlog',     'title' => 'SEO y analítica',                           'priority' => 'medium', 'assignee' => null, 'days' => 60],
                ],
            ],
            [
                'name' => 'Implementación ERP módulo financiero',
                'description' => 'Migración del control de cuentas por pagar y cobrar al nuevo ERP. Incluye configuración, capacitación y go-live.',
                'area' => 'Finanzas',
                'manager' => $director,
                'members' => [$admin, $tiLead],
                'status' => 'planning',
                'priority' => 'urgent',
                'color' => '#f59e0b',
                'start_date' => Carbon::now()->addDays(10),
                'end_date' => Carbon::now()->addDays(120),
                'budget' => 850000,
                'tasks' => [
                    ['col' => 'pending',  'title' => 'Mapeo de cuentas contables',           'priority' => 'high',   'assignee' => $director, 'days' => 15],
                    ['col' => 'pending',  'title' => 'Configuración de catálogos',           'priority' => 'medium', 'assignee' => $tiLead,   'days' => 25],
                    ['col' => 'backlog',  'title' => 'Pruebas de integración con bancos',    'priority' => 'high',   'assignee' => null,      'days' => 50],
                    ['col' => 'backlog',  'title' => 'Capacitación a usuarios finanzas',     'priority' => 'medium', 'assignee' => null,      'days' => 70],
                    ['col' => 'backlog',  'title' => 'Plan de go-live y rollback',           'priority' => 'urgent', 'assignee' => null,      'days' => 90],
                ],
            ],
            [
                'name' => 'Programa de capacitación TI 2026',
                'description' => 'Plan anual de capacitación en seguridad, herramientas y mejores prácticas para todos los colaboradores.',
                'area' => 'Recursos Humanos',
                'manager' => User::where('email','lvelazquez@gptservices.com')->first(),
                'members' => [$ti, $tiLead],
                'status' => 'completed',
                'priority' => 'low',
                'color' => '#8b5cf6',
                'start_date' => Carbon::now()->subDays(180),
                'end_date' => Carbon::now()->subDays(15),
                'budget' => 95000,
                'tasks' => [
                    ['col' => 'done', 'title' => 'Diagnóstico de necesidades formativas', 'priority' => 'medium', 'assignee' => $ti, 'days' => -170],
                    ['col' => 'done', 'title' => 'Calendario de cursos',                  'priority' => 'medium', 'assignee' => $ti, 'days' => -140],
                    ['col' => 'done', 'title' => 'Sesiones de phishing',                  'priority' => 'high',   'assignee' => $tiLead, 'days' => -110],
                    ['col' => 'done', 'title' => 'Evaluación final y certificados',       'priority' => 'low',    'assignee' => $ti, 'days' => -30],
                ],
            ],
        ];

        foreach ($projects as $cfg) {
            $project = $service->createProject([
                'name' => $cfg['name'],
                'description' => $cfg['description'],
                'area' => $cfg['area'],
                'manager_id' => $cfg['manager']?->id,
                'status' => $cfg['status'],
                'priority' => $cfg['priority'],
                'color' => $cfg['color'],
                'start_date' => $cfg['start_date'],
                'end_date' => $cfg['end_date'],
                'budget' => $cfg['budget'],
            ], $cfg['manager']);

            // Add members
            $memberMap = [];
            foreach ($cfg['members'] as $m) {
                if ($m) {
                    $memberMap[$m->id] = 'collaborator';
                }
            }
            if (! empty($memberMap)) {
                $service->syncMembers($project, $memberMap);
            }

            // Tasks
            $columns = $project->defaultBoard->columns->keyBy('slug');
            foreach ($cfg['tasks'] as $idx => $t) {
                $col = $columns->get($t['col']);
                if (! $col) {
                    continue;
                }
                $task = Task::create([
                    'project_id' => $project->id,
                    'board_id' => $project->defaultBoard->id,
                    'column_id' => $col->id,
                    'title' => $t['title'],
                    'description' => 'Tarea generada automáticamente como parte del seed de demostración.',
                    'assignee_id' => $t['assignee']?->id,
                    'created_by' => $cfg['manager']?->id,
                    'priority' => $t['priority'],
                    'due_at' => Carbon::now()->addDays($t['days']),
                    'started_at' => in_array($t['col'], ['in_progress', 'in_review', 'done']) ? Carbon::now()->subDays(max(1, abs($t['days']))) : null,
                    'completed_at' => $t['col'] === 'done' ? Carbon::now()->addDays($t['days']) : null,
                    'position' => $idx + 1,
                    'tags' => $t['tags'] ?? null,
                ]);

                if (! empty($t['checklist'])) {
                    $cl = TaskChecklist::create([
                        'task_id' => $task->id,
                        'title' => 'Subtareas',
                        'position' => 1,
                    ]);
                    foreach ($t['checklist'] as $i => [$text, $done]) {
                        TaskChecklistItem::create([
                            'checklist_id' => $cl->id,
                            'text' => $text,
                            'is_done' => $done,
                            'position' => $i + 1,
                        ]);
                    }
                }
            }
        }

        // Project request examples
        $requester = User::where('email', 'fcastillo@gptservices.com')->first();
        if ($requester) {
            ProjectRequest::create([
                'requester_id' => $requester->id,
                'name' => 'Automatización de reportes operativos',
                'description' => 'Desarrollar un dashboard automatizado que reduzca el tiempo de generación de reportes operativos de 3 días a 30 minutos mediante integración directa con la base de datos.',
                'justification' => 'Actualmente el equipo dedica 3 días/mes a consolidar manualmente datos de varias hojas. Esta automatización liberaría ~36 días-persona al año.',
                'impact' => 'high',
                'priority' => 'high',
                'budget_estimate' => 180000,
                'expected_area' => 'Operaciones',
                'desired_start_date' => Carbon::now()->addDays(15),
                'desired_end_date' => Carbon::now()->addDays(90),
                'status' => 'in_review',
            ]);
        }
        $requester2 = User::where('email', 'vibarra@gptservices.com')->first();
        if ($requester2) {
            ProjectRequest::create([
                'requester_id' => $requester2->id,
                'name' => 'Campaña de adquisición digital Q4',
                'description' => 'Estrategia integral de pauta digital para impulsar adquisición de clientes durante el último trimestre. Incluye Google Ads, LinkedIn y producción de contenido.',
                'justification' => 'El presupuesto actual de marketing termina en septiembre. Sin esta campaña perderemos el momentum de demanda generado en Q3.',
                'impact' => 'medium',
                'priority' => 'medium',
                'budget_estimate' => 350000,
                'expected_area' => 'Marketing',
                'status' => 'draft',
            ]);
        }
    }
}
