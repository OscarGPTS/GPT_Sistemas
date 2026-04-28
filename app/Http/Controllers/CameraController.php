<?php

namespace App\Http\Controllers;

use App\Models\Access;
use App\Models\Asset;
use App\Models\AssetCamera;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CameraController extends Controller
{
    public function index(Request $request): View
    {
        $query = Asset::with('camera', 'category')
            ->where(function ($q) {
                $q->where('type', 'camera')
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', '%mar%'));
            });

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('internal_code', 'like', "%$search%")
                    ->orWhere('brand', 'like', "%$search%")
                    ->orWhere('model', 'like', "%$search%")
                    ->orWhere('location', 'like', "%$search%");
            });
        }

        return view('cameras.index', [
            'cameras' => $query->orderBy('internal_code')->paginate(24)->withQueryString(),
            'filters' => $request->only('q'),
        ]);
    }

    public function show(Asset $asset): View
    {
        abort_unless($asset->isCamera(), 404);
        $asset->load('camera.access', 'category', 'currentAssignment.user');

        return view('cameras.show', [
            'asset' => $asset,
            'camera' => $asset->camera,
            'canManage' => auth()->user()->isAdmin() || auth()->user()->hasPermission('cameras.manage'),
        ]);
    }

    public function edit(Asset $asset): View
    {
        abort_unless($asset->isCamera(), 404);
        $asset->load('camera');
        $camera = $asset->camera ?? new AssetCamera(['asset_id' => $asset->id, 'protocol' => 'rtsp']);

        return view('cameras.form', [
            'asset' => $asset,
            'camera' => $camera,
            'accesses' => Access::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        abort_unless($asset->isCamera(), 404);
        abort_unless(auth()->user()->isAdmin() || auth()->user()->hasPermission('cameras.manage'), 403);

        $data = $request->validate([
            'protocol' => ['required', 'in:rtsp,http,mjpeg,hls,onvif,other'],
            'stream_url' => ['nullable', 'string', 'max:500'],
            'mjpeg_url' => ['nullable', 'string', 'max:500'],
            'snapshot_url' => ['nullable', 'string', 'max:500'],
            'nvr_url' => ['nullable', 'url', 'max:500'],
            'resolution' => ['nullable', 'string', 'max:30'],
            'fps' => ['nullable', 'integer', 'min:1', 'max:120'],
            'has_audio' => ['nullable'],
            'has_motion_detection' => ['nullable'],
            'has_ptz' => ['nullable'],
            'access_id' => ['nullable', 'exists:accesses,id'],
        ]);
        foreach (['has_audio', 'has_motion_detection', 'has_ptz'] as $f) {
            $data[$f] = $request->boolean($f);
        }
        $data['asset_id'] = $asset->id;

        AssetCamera::updateOrCreate(['asset_id' => $asset->id], $data);

        return redirect()->route('cameras.show', $asset)->with('success', 'Configuración de cámara actualizada.');
    }
}
