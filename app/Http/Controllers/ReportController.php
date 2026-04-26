<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\MaintenanceRecord;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index', [
            'assetsByStatus' => Asset::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'assetsByType' => Asset::selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type'),
            'ticketsByStatus' => Ticket::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'ticketsByPriority' => Ticket::selectRaw('priority, count(*) as total')->groupBy('priority')->pluck('total', 'priority'),
            'maintenanceDue' => MaintenanceRecord::where('status', 'scheduled')
                ->whereDate('scheduled_date', '<=', now()->addDays(30))
                ->with('asset')->orderBy('scheduled_date')->limit(25)->get(),
        ]);
    }

    public function exportAssets(Request $request): StreamedResponse
    {
        $filename = 'activos_'.now()->format('Ymd_His').'.csv';
        return $this->streamCsv($filename, function ($out) {
            fputcsv($out, ['Código', 'Tipo', 'Marca', 'Modelo', 'Serie', 'Estado', 'Condición', 'Ubicación', 'Costo', 'Fecha Compra', 'Garantía']);
            Asset::with('category')->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $a) {
                    fputcsv($out, [
                        $a->internal_code, $a->type, $a->brand, $a->model, $a->serial_number,
                        $a->status, $a->condition, $a->location, $a->purchase_cost,
                        optional($a->purchase_date)->format('Y-m-d'),
                        optional($a->warranty_until)->format('Y-m-d'),
                    ]);
                }
            });
        });
    }

    public function exportTickets(Request $request): StreamedResponse
    {
        $filename = 'tickets_'.now()->format('Ymd_His').'.csv';
        return $this->streamCsv($filename, function ($out) {
            fputcsv($out, ['Código', 'Tipo', 'Prioridad', 'Estado', 'Asunto', 'Solicitante', 'Asignado', 'Creado', 'Resuelto']);
            Ticket::with('requester', 'assignee')->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $t) {
                    fputcsv($out, [
                        $t->code, $t->type, $t->priority, $t->status, $t->subject,
                        $t->requester?->name, $t->assignee?->name,
                        $t->created_at?->format('Y-m-d H:i'),
                        optional($t->resolved_at)->format('Y-m-d H:i'),
                    ]);
                }
            });
        });
    }

    public function exportAssignments(): StreamedResponse
    {
        $filename = 'asignaciones_'.now()->format('Ymd_His').'.csv';
        return $this->streamCsv($filename, function ($out) {
            fputcsv($out, ['Activo', 'Usuario', 'Asignado Por', 'Fecha Asignación', 'Fecha Devolución', 'Activa', 'Motivo']);
            AssetAssignment::with('asset', 'user', 'assignedBy')->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $a) {
                    fputcsv($out, [
                        $a->asset?->internal_code, $a->user?->name, $a->assignedBy?->name,
                        optional($a->assigned_at)->format('Y-m-d H:i'),
                        optional($a->returned_at)->format('Y-m-d H:i'),
                        $a->is_active ? 'SI' : 'NO',
                        $a->assignment_reason,
                    ]);
                }
            });
        });
    }

    private function streamCsv(string $filename, \Closure $callback): StreamedResponse
    {
        return response()->streamDownload(function () use ($callback) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel UTF-8
            $callback($out);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
