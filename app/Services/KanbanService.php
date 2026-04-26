<?php

namespace App\Services;

use App\Models\BoardColumn;
use App\Models\Task;
use App\Models\TaskHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KanbanService
{
    /**
     * Move a task to a new column at a specific position. Reorders surrounding tasks.
     */
    public function moveTask(Task $task, int $columnId, int $position): void
    {
        DB::transaction(function () use ($task, $columnId, $position) {
            $fromColumnId = $task->column_id;
            $fromPosition = $task->position;
            $movedColumn = $fromColumnId !== $columnId;

            $newColumn = BoardColumn::findOrFail($columnId);
            if ($newColumn->board_id !== $task->board_id) {
                throw new \RuntimeException('La columna destino pertenece a otro tablero.');
            }

            // 1. Remove from old column (close gap)
            if ($movedColumn) {
                Task::where('column_id', $fromColumnId)
                    ->where('position', '>', $fromPosition)
                    ->decrement('position');
            } else {
                // same column reorder: shift tasks accordingly
                if ($position > $fromPosition) {
                    Task::where('column_id', $fromColumnId)
                        ->whereBetween('position', [$fromPosition + 1, $position])
                        ->decrement('position');
                } elseif ($position < $fromPosition) {
                    Task::where('column_id', $fromColumnId)
                        ->whereBetween('position', [$position, $fromPosition - 1])
                        ->increment('position');
                }
            }

            // 2. Make space in destination column for new position
            if ($movedColumn) {
                Task::where('column_id', $columnId)
                    ->where('position', '>=', $position)
                    ->increment('position');
            }

            // 3. Update the task itself
            $task->update([
                'column_id' => $columnId,
                'position' => $position,
                'started_at' => $task->started_at ?? ($newColumn->slug !== 'backlog' ? now() : null),
                'completed_at' => $newColumn->is_done_column ? now() : null,
            ]);

            // 4. History
            if ($movedColumn) {
                TaskHistory::create([
                    'task_id' => $task->id,
                    'user_id' => Auth::id(),
                    'action' => 'moved',
                    'data' => ['from_column' => $fromColumnId, 'to_column' => $columnId],
                ]);
            }
        });
    }

    /**
     * Add a task at the end of a column.
     */
    public function appendTaskToColumn(Task $task, int $columnId): void
    {
        $position = (int) (Task::where('column_id', $columnId)->max('position') ?? 0) + 1;
        $this->moveTask($task, $columnId, $position);
    }
}
