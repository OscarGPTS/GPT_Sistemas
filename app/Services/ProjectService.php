<?php

namespace App\Services;

use App\Models\Board;
use App\Models\Project;
use App\Models\ProjectRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public const DEFAULT_COLUMNS = [
        ['name' => 'Backlog',     'slug' => 'backlog',     'color' => '#94a3b8', 'position' => 1],
        ['name' => 'Pendiente',   'slug' => 'pending',     'color' => '#f59e0b', 'position' => 2],
        ['name' => 'En progreso', 'slug' => 'in_progress', 'color' => '#3b82f6', 'position' => 3],
        ['name' => 'En revisión', 'slug' => 'in_review',   'color' => '#8b5cf6', 'position' => 4],
        ['name' => 'Completado',  'slug' => 'done',        'color' => '#10b981', 'position' => 5, 'is_done_column' => true],
    ];

    public function __construct(private AuditService $audit)
    {
    }

    public function createProject(array $data, ?User $creator = null): Project
    {
        return DB::transaction(function () use ($data, $creator) {
            $creator = $creator ?? (Auth::user() instanceof User ? Auth::user() : null);

            $project = Project::create(array_merge($data, [
                'created_by' => $creator?->id,
                'manager_id' => $data['manager_id'] ?? $creator?->id,
            ]));

            // Add manager as a member
            if ($project->manager_id) {
                $project->members()->syncWithoutDetaching([
                    $project->manager_id => ['role' => 'manager'],
                ]);
            }

            // Create default board with columns
            $this->createDefaultBoard($project);

            $this->audit->log('project.created', $project);

            return $project->fresh(['boards.columns', 'manager']);
        });
    }

    public function createDefaultBoard(Project $project): Board
    {
        $board = $project->boards()->create([
            'name' => 'Tablero principal',
            'is_default' => true,
            'position' => 0,
        ]);

        foreach (self::DEFAULT_COLUMNS as $col) {
            $board->columns()->create($col);
        }

        return $board->fresh('columns');
    }

    public function convertRequestToProject(ProjectRequest $request): Project
    {
        if ($request->status !== 'approved') {
            throw new \RuntimeException('Solo solicitudes aprobadas pueden convertirse en proyectos.');
        }
        if ($request->project_id) {
            throw new \RuntimeException('Esta solicitud ya fue convertida.');
        }

        return DB::transaction(function () use ($request) {
            $project = $this->createProject([
                'name' => $request->name,
                'description' => $request->description,
                'area' => $request->expected_area,
                'priority' => $request->priority,
                'budget' => $request->budget_estimate,
                'start_date' => $request->desired_start_date ?? now()->toDateString(),
                'end_date' => $request->desired_end_date,
                'status' => 'planning',
            ], $request->requester);

            $request->update([
                'status' => 'converted',
                'project_id' => $project->id,
            ]);

            $this->audit->log('project.created_from_request', $project, [], [
                'request_id' => $request->id,
            ]);

            return $project;
        });
    }

    public function syncMembers(Project $project, array $members): void
    {
        // $members format: [user_id => 'manager|collaborator|observer', ...]
        $sync = collect($members)
            ->mapWithKeys(fn ($role, $userId) => [$userId => ['role' => $role]])
            ->all();
        $project->members()->sync($sync);
    }

    public function archiveProject(Project $project): void
    {
        $project->update(['archived_at' => now(), 'status' => 'completed']);
        $this->audit->log('project.archived', $project);
    }
}
