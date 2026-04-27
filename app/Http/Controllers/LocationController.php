<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Services\LocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function __construct(private LocationService $service)
    {
    }

    public function index(): View
    {
        return view('locations.index', [
            'tree' => $this->service->nestedTree(),
            'flatList' => $this->service->flatTree(),
            'totalCount' => Location::count(),
            'activeCount' => Location::where('is_active', true)->count(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('locations.form', [
            'location' => new Location([
                'is_active' => true,
                'type' => 'area',
                'parent_id' => $request->input('parent_id'),
            ]),
            'flatList' => $this->service->flatTree(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateLocation($request);
        Location::create($data);
        return redirect()->route('locations.index')->with('success', 'Ubicación creada.');
    }

    public function edit(Location $location): View
    {
        return view('locations.form', [
            'location' => $location,
            'flatList' => $this->service->flatTree(),
        ]);
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $data = $this->validateLocation($request, $location);
        if (! $this->service->isValidParent($location, $data['parent_id'] ?? null)) {
            return back()->withErrors(['parent_id' => 'No se puede mover una ubicación dentro de sí misma o sus descendientes.']);
        }
        $location->update($data);
        return redirect()->route('locations.index')->with('success', 'Ubicación actualizada.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        if ($location->children()->count() > 0) {
            return back()->withErrors(['location' => 'No se puede eliminar: tiene sub-ubicaciones. Elimina o reasigna primero.']);
        }
        if ($location->deviceUsers()->count() > 0) {
            return back()->withErrors(['location' => 'No se puede eliminar: tiene usuarios de impresión asociados.']);
        }
        $location->delete();
        return back()->with('success', 'Ubicación eliminada.');
    }

    private function validateLocation(Request $request, ?Location $location = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:'.implode(',', array_keys(Location::TYPES))],
            'parent_id' => ['nullable', 'exists:locations,id'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
