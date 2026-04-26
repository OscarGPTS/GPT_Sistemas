<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Services\MaintenanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function __construct(private MaintenanceService $service)
    {
    }

    public function index(Request $request): View
    {
        $records = MaintenanceRecord::with(['asset', 'responsible'])
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('type'), fn ($q, $t) => $q->where('type', $t))
            ->orderBy('scheduled_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        $schedules = MaintenanceSchedule::with('asset', 'responsible')
            ->where('is_active', true)
            ->orderBy('next_due_at')
            ->paginate(10, ['*'], 'sched_page');

        return view('maintenance.index', compact('records', 'schedules'));
    }

    public function createRecord(Request $request): View
    {
        return view('maintenance.record_form', [
            'record' => new MaintenanceRecord(),
            'assets' => Asset::orderBy('internal_code')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeRecord(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'type' => ['required', 'in:preventive,corrective'],
            'status' => ['required', 'in:scheduled,in_progress,completed,cancelled'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'scheduled_date' => ['required', 'date'],
            'performed_date' => ['nullable', 'date'],
            'responsible_id' => ['nullable', 'exists:users,id'],
            'provider' => ['nullable', 'string', 'max:150'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'result_notes' => ['nullable', 'string'],
        ]);
        $record = MaintenanceRecord::create($data);
        if ($record->status === 'in_progress' || $record->status === 'scheduled') {
            Asset::whereKey($record->asset_id)->update(['status' => 'in_maintenance']);
        }
        return redirect()->route('maintenance.index')->with('success', 'Mantenimiento registrado.');
    }

    public function completeRecord(Request $request, MaintenanceRecord $record): RedirectResponse
    {
        $data = $request->validate([
            'performed_date' => ['required', 'date'],
            'provider' => ['nullable', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'result_notes' => ['nullable', 'string'],
        ]);
        $this->service->markCompleted($record, $data);
        Asset::whereKey($record->asset_id)->update(['status' => 'available']);
        return back()->with('success', 'Mantenimiento completado.');
    }

    public function createSchedule(): View
    {
        return view('maintenance.schedule_form', [
            'schedule' => new MaintenanceSchedule(),
            'assets' => Asset::orderBy('internal_code')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeSchedule(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'type' => ['required', 'in:preventive,corrective'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'frequency' => ['required', 'in:monthly,quarterly,biannual,annual,custom'],
            'interval_days' => ['nullable', 'integer', 'min:1'],
            'next_due_at' => ['required', 'date'],
            'responsible_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        MaintenanceSchedule::create($data);
        return redirect()->route('maintenance.index')->with('success', 'Programación creada.');
    }

    public function generate(): RedirectResponse
    {
        $count = $this->service->generateDueRecords();
        return back()->with('success', "Se generaron $count registros de mantenimiento próximos.");
    }
}
