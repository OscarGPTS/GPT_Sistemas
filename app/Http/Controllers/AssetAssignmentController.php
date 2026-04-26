<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;
use App\Services\AssetAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetAssignmentController extends Controller
{
    public function __construct(private AssetAssignmentService $service)
    {
    }

    public function index(Request $request): View
    {
        $query = AssetAssignment::with(['asset', 'user', 'assignedBy'])->latest('assigned_at');
        if ($request->boolean('active')) {
            $query->where('is_active', true);
        }
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }
        return view('assignments.index', [
            'assignments' => $query->paginate(20)->withQueryString(),
            'users' => User::orderBy('name')->get(),
            'filters' => $request->only(['user_id', 'active']),
        ]);
    }

    public function create(Request $request): View
    {
        $assetId = $request->input('asset_id');
        return view('assignments.form', [
            'asset' => $assetId ? Asset::findOrFail($assetId) : null,
            'availableAssets' => Asset::where('status', 'available')->orderBy('internal_code')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'user_id' => ['required', 'exists:users,id'],
            'assignment_reason' => ['nullable', 'string'],
            'assignment_location' => ['nullable', 'string'],
            'condition_out' => ['nullable', 'string'],
        ]);

        $asset = Asset::findOrFail($data['asset_id']);
        $user = User::findOrFail($data['user_id']);
        $assignment = $this->service->assign($asset, $user, $data);

        return redirect()->route('assets.show', $assignment->asset_id)
            ->with('success', 'Activo asignado correctamente.');
    }

    public function release(Request $request, Asset $asset): RedirectResponse
    {
        $data = $request->validate([
            'return_reason' => ['nullable', 'string'],
            'condition_in' => ['nullable', 'string'],
        ]);
        $this->service->release($asset, $data);
        return redirect()->route('assets.show', $asset)->with('success', 'Activo liberado.');
    }
}
