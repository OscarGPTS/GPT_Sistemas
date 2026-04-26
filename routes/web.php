<?php

use App\Http\Controllers\AssetAssignmentController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\Auth\Auth0Controller;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectRequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/auth/auth0/redirect', [Auth0Controller::class, 'redirect'])->name('auth0.redirect');
    Route::get('/auth/auth0/callback', [Auth0Controller::class, 'callback'])->name('auth0.callback');

    Route::get('/login/google', [GoogleController::class, 'redirect'])->name('login.google');
    Route::get('/login/google/callback', [GoogleController::class, 'callback'])->name('login.google.callback');
});
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Authenticated
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Assets
    Route::middleware('permission:assets.view')->group(function () {
        Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
        Route::get('/assets/{asset}', [AssetController::class, 'show'])->name('assets.show');
    });
    Route::middleware('permission:assets.create')->group(function () {
        Route::get('/assets/create/new', [AssetController::class, 'create'])->name('assets.create');
        Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
    });
    Route::middleware('permission:assets.update')->group(function () {
        Route::get('/assets/{asset}/edit', [AssetController::class, 'edit'])->name('assets.edit');
        Route::put('/assets/{asset}', [AssetController::class, 'update'])->name('assets.update');
    });
    Route::middleware('permission:assets.delete')->delete('/assets/{asset}', [AssetController::class, 'destroy'])
        ->name('assets.destroy');
    Route::middleware('permission:assets.import')->group(function () {
        Route::get('/assets/import/form', [AssetController::class, 'importForm'])->name('assets.import.form');
        Route::post('/assets/import', [AssetController::class, 'import'])->name('assets.import');
    });

    // Assignments
    Route::middleware('permission:assignments.view')->group(function () {
        Route::get('/assignments', [AssetAssignmentController::class, 'index'])->name('assignments.index');
    });
    Route::middleware('permission:assignments.manage')->group(function () {
        Route::get('/assignments/create', [AssetAssignmentController::class, 'create'])->name('assignments.create');
        Route::post('/assignments', [AssetAssignmentController::class, 'store'])->name('assignments.store');
        Route::post('/assignments/release/{asset}', [AssetAssignmentController::class, 'release'])->name('assignments.release');
    });

    // Maintenance
    Route::middleware('permission:maintenance.view')->group(function () {
        Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    });
    Route::middleware('permission:maintenance.manage')->group(function () {
        Route::get('/maintenance/records/create', [MaintenanceController::class, 'createRecord'])->name('maintenance.records.create');
        Route::post('/maintenance/records', [MaintenanceController::class, 'storeRecord'])->name('maintenance.records.store');
        Route::post('/maintenance/records/{record}/complete', [MaintenanceController::class, 'completeRecord'])->name('maintenance.records.complete');
        Route::get('/maintenance/schedules/create', [MaintenanceController::class, 'createSchedule'])->name('maintenance.schedules.create');
        Route::post('/maintenance/schedules', [MaintenanceController::class, 'storeSchedule'])->name('maintenance.schedules.store');
        Route::post('/maintenance/generate', [MaintenanceController::class, 'generate'])->name('maintenance.generate');
    });

    // Tickets
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::put('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
    Route::post('/tickets/{ticket}/comment', [TicketController::class, 'comment'])->name('tickets.comment');
    Route::get('/tickets/attachment/{id}', [TicketController::class, 'downloadAttachment'])->name('tickets.attachment');

    // Workflows
    Route::middleware('permission:workflows.view')->group(function () {
        Route::get('/workflows', [WorkflowController::class, 'index'])->name('workflows.index');
        Route::get('/workflows/{workflow}', [WorkflowController::class, 'show'])->name('workflows.show');
        Route::get('/workflow-instances', [WorkflowController::class, 'instances'])->name('workflows.instances');
        Route::get('/workflow-instances/{instance}', [WorkflowController::class, 'showInstance'])->name('workflows.instances.show');
    });
    Route::middleware('permission:workflows.manage')->group(function () {
        Route::get('/workflows/create/new', [WorkflowController::class, 'create'])->name('workflows.create');
        Route::post('/workflows', [WorkflowController::class, 'store'])->name('workflows.store');
        Route::get('/workflows/{workflow}/edit', [WorkflowController::class, 'edit'])->name('workflows.edit');
        Route::put('/workflows/{workflow}', [WorkflowController::class, 'update'])->name('workflows.update');
        Route::delete('/workflows/{workflow}', [WorkflowController::class, 'destroy'])->name('workflows.destroy');
        Route::post('/workflows/{workflow}/start', [WorkflowController::class, 'startInstance'])->name('workflows.instances.start');
    });
    Route::get('/my-approvals', [WorkflowController::class, 'myApprovals'])->name('workflows.my_approvals');
    Route::post('/workflow-instances/{instance}/decide', [WorkflowController::class, 'decide'])
        ->name('workflows.instances.decide');

    // Reports
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });
    Route::middleware('permission:reports.export')->group(function () {
        Route::get('/reports/export/assets', [ReportController::class, 'exportAssets'])->name('reports.export.assets');
        Route::get('/reports/export/tickets', [ReportController::class, 'exportTickets'])->name('reports.export.tickets');
        Route::get('/reports/export/assignments', [ReportController::class, 'exportAssignments'])->name('reports.export.assignments');
    });

    // Users
    Route::middleware('permission:users.view')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
    });
    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
    });

    // Audit
    Route::middleware('permission:audit.view')->group(function () {
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    });

    // ============================================
    // PROJECTS / KANBAN
    // ============================================
    Route::middleware('permission:projects.view')->group(function () {
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('/projects/{project:slug}/board', [BoardController::class, 'show'])->name('projects.board');
    });
    Route::middleware('permission:projects.create')->group(function () {
        Route::get('/projects/create/new', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    });
    Route::middleware('permission:projects.update')->group(function () {
        Route::get('/projects/{project:slug}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/projects/{project:slug}', [ProjectController::class, 'update'])->name('projects.update');
        Route::post('/projects/{project:slug}/archive', [ProjectController::class, 'archive'])->name('projects.archive');
    });
    Route::middleware('permission:projects.delete')->delete('/projects/{project:slug}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    // Project requests
    Route::middleware('permission:project_requests.view')->group(function () {
        Route::get('/project-requests', [ProjectRequestController::class, 'index'])->name('project_requests.index');
        Route::get('/project-requests/{projectRequest}', [ProjectRequestController::class, 'show'])->name('project_requests.show');
    });
    Route::middleware('permission:project_requests.create')->group(function () {
        Route::get('/project-requests/create/new', [ProjectRequestController::class, 'create'])->name('project_requests.create');
        Route::post('/project-requests', [ProjectRequestController::class, 'store'])->name('project_requests.store');
        Route::post('/project-requests/{projectRequest}/submit', [ProjectRequestController::class, 'submit'])->name('project_requests.submit');
    });
    Route::middleware('permission:projects.create')->post('/project-requests/{projectRequest}/convert', [ProjectRequestController::class, 'convert'])->name('project_requests.convert');

    // Tasks
    Route::middleware('permission:tasks.view')->group(function () {
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::get('/tasks/attachment/{id}', [TaskController::class, 'downloadAttachment'])->name('tasks.attachment');
    });
    Route::middleware('permission:tasks.manage')->group(function () {
        Route::post('/projects/{project:slug}/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::post('/tasks/{task}/move', [BoardController::class, 'moveTask'])->name('tasks.move');
        Route::post('/tasks/{task}/comment', [TaskController::class, 'comment'])->name('tasks.comment');
        Route::post('/tasks/{task}/attachments', [TaskController::class, 'uploadAttachment'])->name('tasks.attachments.store');
        Route::post('/tasks/{task}/checklists', [TaskController::class, 'storeChecklist'])->name('tasks.checklists.store');
        Route::delete('/checklists/{checklist}', [TaskController::class, 'destroyChecklist'])->name('checklists.destroy');
        Route::post('/checklists/{checklist}/items', [TaskController::class, 'storeChecklistItem'])->name('checklists.items.store');
        Route::post('/checklist-items/{item}/toggle', [TaskController::class, 'toggleChecklistItem'])->name('checklist_items.toggle');
        Route::delete('/checklist-items/{item}', [TaskController::class, 'destroyChecklistItem'])->name('checklist_items.destroy');
        // Board columns
        Route::post('/boards/{board}/columns', [BoardController::class, 'storeColumn'])->name('board_columns.store');
        Route::put('/board-columns/{column}', [BoardController::class, 'updateColumn'])->name('board_columns.update');
        Route::delete('/board-columns/{column}', [BoardController::class, 'destroyColumn'])->name('board_columns.destroy');
    });

    // Notifications (inbox + bell dropdown)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::get('/notifications/{id}/open', [NotificationController::class, 'markRead'])->name('notifications.open');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});
