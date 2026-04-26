<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Workflow;
use Illuminate\Database\Seeder;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::pluck('id', 'name');

        $workflow = Workflow::updateOrCreate(
            ['slug' => 'equipment-request'],
            [
                'name' => 'Solicitud de Equipo',
                'target_type' => 'equipment_request',
                'description' => 'Flujo estándar de aprobación para solicitud de equipo nuevo.',
                'is_active' => true,
                'mode' => 'sequential',
            ]
        );

        $steps = [
            ['order' => 1, 'name' => 'Aprobación Jefe Directo', 'approver_type' => 'manager'],
            ['order' => 2, 'name' => 'Aprobación RH', 'approver_type' => 'role', 'role_id' => $roles['hr'] ?? null],
            ['order' => 3, 'name' => 'Aprobación TI', 'approver_type' => 'role', 'role_id' => $roles['it'] ?? null],
        ];

        $workflow->steps()->delete();
        foreach ($steps as $step) {
            $workflow->steps()->create($step);
        }

        $ticket = Workflow::updateOrCreate(
            ['slug' => 'ticket-approval'],
            [
                'name' => 'Aprobación de Ticket',
                'target_type' => 'ticket',
                'description' => 'Flujo opcional para tickets que requieren aprobación.',
                'is_active' => false,
                'mode' => 'sequential',
            ]
        );
        $ticket->steps()->delete();
        $ticket->steps()->create([
            'order' => 1,
            'name' => 'Aprobación Jefe Directo',
            'approver_type' => 'manager',
        ]);
    }
}
