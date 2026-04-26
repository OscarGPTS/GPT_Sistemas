<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\MaintenanceRecord;
use App\Models\Ticket;
use App\Models\WorkflowInstance;
use App\Services\WorkflowEngine;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, WorkflowEngine $engine): View
    {
        $user = $request->user();

        $stats = [
            'assets_total' => Asset::count(),
            'assets_available' => Asset::where('status', 'available')->count(),
            'assets_assigned' => Asset::where('status', 'assigned')->count(),
            'assets_maintenance' => Asset::where('status', 'in_maintenance')->count(),
            'tickets_open' => Ticket::whereIn('status', ['open', 'in_progress', 'on_hold'])->count(),
            'tickets_mine' => Ticket::where('requester_id', $user->id)->whereIn('status', ['open', 'in_progress', 'on_hold'])->count(),
            'maintenance_due' => MaintenanceRecord::where('status', 'scheduled')
                ->whereDate('scheduled_date', '<=', now()->addDays(14))->count(),
            'approvals_mine' => $engine->pendingForUser($user)->count(),
            'workflows_active' => WorkflowInstance::where('status', 'in_review')->count(),
        ];

        $recentTickets = Ticket::with(['requester', 'assignee'])
            ->latest()->take(5)->get();

        $myAssets = $user->activeAssignments()->with('asset')->take(5)->get();

        return view('dashboard', compact('stats', 'recentTickets', 'myAssets'));
    }
}
