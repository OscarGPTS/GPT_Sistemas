<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function __construct(private AuditService $audit)
    {
    }

    public function index(Request $request): View
    {
        $query = Asset::with(['category', 'currentAssignment.user']);

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('internal_code', 'like', "%$search%")
                    ->orWhere('serial_number', 'like', "%$search%")
                    ->orWhere('brand', 'like', "%$search%")
                    ->orWhere('model', 'like', "%$search%");
            });
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $assets = $query->latest()->paginate(20)->withQueryString();

        return view('assets.index', [
            'assets' => $assets,
            'categories' => AssetCategory::orderBy('name')->get(),
            'filters' => $request->only(['q', 'status', 'category_id', 'type']),
        ]);
    }

    public function create(): View
    {
        return view('assets.form', [
            'asset' => new Asset(),
            'categories' => AssetCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAsset($request);
        $data['created_by'] = $request->user()->id;
        $asset = Asset::create($data);
        $this->audit->log('asset.created', $asset, [], $asset->toArray());

        return redirect()->route('assets.show', $asset)->with('success', 'Activo creado correctamente.');
    }

    public function show(Asset $asset): View
    {
        $asset->load([
            'category', 'creator',
            'assignments.user', 'assignments.assignedBy',
            'maintenanceRecords' => fn ($q) => $q->latest('scheduled_date'),
            'maintenanceSchedules',
            'tickets' => fn ($q) => $q->latest()->limit(10),
            'deviceUsers.location',
        ]);
        return view('assets.show', compact('asset'));
    }

    public function edit(Asset $asset): View
    {
        return view('assets.form', [
            'asset' => $asset,
            'categories' => AssetCategory::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $data = $this->validateAsset($request, $asset);
        $old = $asset->toArray();
        $asset->update($data);
        $this->audit->log('asset.updated', $asset, $old, $asset->toArray());

        return redirect()->route('assets.show', $asset)->with('success', 'Activo actualizado.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $old = $asset->toArray();
        $asset->delete();
        $this->audit->log('asset.deleted', $asset, $old, []);

        return redirect()->route('assets.index')->with('success', 'Activo dado de baja.');
    }

    public function importForm(): View
    {
        return view('assets.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        if (! $handle) {
            return back()->withErrors(['file' => 'No se pudo leer el archivo.']);
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return back()->withErrors(['file' => 'Archivo vacío.']);
        }
        $header = array_map(fn ($h) => trim(strtolower((string) $h)), $header);
        $created = 0;
        $errors = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            try {
                Asset::create([
                    'internal_code' => $data['internal_code'] ?? ('A-'.uniqid()),
                    'type' => $data['type'] ?? 'other',
                    'brand' => $data['brand'] ?? null,
                    'model' => $data['model'] ?? null,
                    'serial_number' => $data['serial_number'] ?? null,
                    'location' => $data['location'] ?? null,
                    'status' => $data['status'] ?? 'available',
                    'condition' => $data['condition'] ?? 'good',
                    'purchase_date' => ! empty($data['purchase_date']) ? $data['purchase_date'] : null,
                    'purchase_cost' => ! empty($data['purchase_cost']) ? $data['purchase_cost'] : null,
                    'warranty_until' => ! empty($data['warranty_until']) ? $data['warranty_until'] : null,
                    'created_by' => $request->user()->id,
                ]);
                $created++;
            } catch (\Throwable) {
                $errors++;
            }
        }
        fclose($handle);

        return redirect()->route('assets.index')
            ->with('success', "Importación finalizada. Creados: $created. Errores: $errors.");
    }

    private function validateAsset(Request $request, ?Asset $asset = null): array
    {
        return $request->validate([
            'internal_code' => ['required', 'string', 'max:50',
                'unique:assets,internal_code'.($asset ? ','.$asset->id : '')],
            'category_id' => ['nullable', 'exists:asset_categories,id'],
            'type' => ['required', 'string', 'max:50'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100',
                'unique:assets,serial_number'.($asset ? ','.$asset->id : '')],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:150'],
            'status' => ['required', 'in:available,assigned,in_maintenance,retired,lost'],
            'condition' => ['required', 'in:new,good,fair,poor'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:150'],
            'warranty_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
