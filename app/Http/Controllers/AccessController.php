<?php

namespace App\Http\Controllers;

use App\Models\Access;
use App\Models\AccessLog;
use App\Models\AccessType;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use App\Services\AccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccessController extends Controller
{
    public function __construct(private AccessService $service)
    {
    }

    public function index(Request $request): View
    {
        $query = Access::with('type', 'location', 'owner');
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%")
                    ->orWhere('hostname', 'like', "%$search%")
                    ->orWhere('ip', 'like', "%$search%")
                    ->orWhere('url', 'like', "%$search%");
            });
        }
        if ($typeId = $request->input('type_id')) {
            $query->where('type_id', $typeId);
        }
        if ($request->boolean('stale')) {
            $query->where(function ($q) {
                $q->whereNull('last_rotated_at')
                    ->orWhere('last_rotated_at', '<', now()->subDays(90));
            });
        }
        return view('accesses.index', [
            'accesses' => $query->latest()->paginate(20)->withQueryString(),
            'types' => AccessType::where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['q', 'type_id', 'stale']),
        ]);
    }

    public function create(): View
    {
        return view('accesses.form', [
            'access' => new Access(['is_active' => true]),
            'types' => AccessType::where('is_active', true)->orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
            'assets' => Asset::orderBy('internal_code')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAccess($request);
        $extras = $data['extras'] ?? [];
        unset($data['extras']);

        if (! empty($data['password'])) {
            $data['last_rotated_at'] = now();
        }
        $data['created_by'] = auth()->id();

        $access = Access::create($data);
        $this->syncExtras($access, $extras);
        $this->service->logAction($access, $request->user(), 'create', $request);

        return redirect()->route('accesses.show', $access)->with('success', 'Acceso creado.');
    }

    public function show(Access $access, Request $request): View
    {
        $access->load('type', 'location', 'asset', 'owner', 'creator', 'extraFields', 'documents.uploader');
        $this->service->logAction($access, $request->user(), 'view_metadata', $request);

        return view('accesses.show', [
            'access' => $access,
            'hasRevealWindow' => $this->service->hasActiveRevealWindow($access),
            'revealSecondsLeft' => $this->service->revealWindowSecondsLeft($access),
            'recentLogs' => AccessLog::with('user')->where('access_id', $access->id)->latest()->take(10)->get(),
            'canReveal' => auth()->user()->isAdmin() || auth()->user()->hasPermission('accesses.reveal'),
        ]);
    }

    public function edit(Access $access): View
    {
        return view('accesses.form', [
            'access' => $access,
            'types' => AccessType::where('is_active', true)->orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
            'assets' => Asset::orderBy('internal_code')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Access $access): RedirectResponse
    {
        $data = $this->validateAccess($request, $access);
        $extras = $data['extras'] ?? [];
        unset($data['extras']);

        // Track if password was rotated
        $passwordRotated = false;
        if (! empty($data['password']) && $data['password'] !== $access->password) {
            $data['last_rotated_at'] = now();
            $passwordRotated = true;
        } elseif (empty($data['password'])) {
            unset($data['password']);
        }

        $access->update($data);
        $this->syncExtras($access, $extras);

        $this->service->logAction(
            $access,
            $request->user(),
            $passwordRotated ? 'rotate' : 'update',
            $request
        );

        return redirect()->route('accesses.show', $access)->with('success', 'Acceso actualizado.');
    }

    public function destroy(Request $request, Access $access): RedirectResponse
    {
        $this->service->logAction($access, $request->user(), 'delete', $request);
        $access->delete();
        return redirect()->route('accesses.index')->with('success', 'Acceso eliminado.');
    }

    /**
     * Step 1: User requests OTP. Email is sent immediately with 6-digit code.
     */
    public function requestOtp(Request $request, Access $access): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $this->service->requestRevealOtp($access, $request->user(), $request, $data['reason']);

        return back()->with('success', 'Código enviado a '.$request->user()->email.'. Revisa tu bandeja.');
    }

    /**
     * Step 2: User submits the OTP code. If valid, opens a 90-second reveal window.
     */
    public function validateOtp(Request $request, Access $access): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $valid = $this->service->validateOtp($access, $request->user(), $data['code'], $request);

        if (! $valid) {
            return back()->withErrors(['code' => 'Código inválido o expirado.']);
        }

        return back()->with('success', 'Código verificado · ventana abierta por '.AccessService::REVEAL_WINDOW_SECONDS.' segundos.');
    }

    /**
     * Step 3: AJAX endpoint to fetch the actual decrypted value during the reveal window.
     */
    public function reveal(Request $request, Access $access): JsonResponse
    {
        $data = $request->validate([
            'field' => ['required', 'in:password,username,notes'],
        ]);

        $value = $this->service->reveal($access, $request->user(), $request, $data['field']);

        if ($value === null) {
            return response()->json(['error' => 'Ventana de revelación expirada o no autorizada.'], 403);
        }

        return response()->json([
            'field' => $data['field'],
            'value' => $value,
            'seconds_left' => $this->service->revealWindowSecondsLeft($access),
        ]);
    }

    /**
     * Generate a strong password helper (AJAX).
     */
    public function generatePassword(): JsonResponse
    {
        return response()->json(['password' => $this->service->generatePassword(20)]);
    }

    public function logs(Request $request): View
    {
        $query = AccessLog::with('user', 'access');

        if ($accessId = $request->input('access_id')) {
            $query->where('access_id', $accessId);
        }
        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        return view('accesses.logs', [
            'logs' => $query->latest()->paginate(50)->withQueryString(),
            'filters' => $request->only(['access_id', 'action', 'user_id']),
        ]);
    }

    private function syncExtras(Access $access, array $extras): void
    {
        $access->extraFields()->delete();
        foreach (array_values($extras) as $i => $extra) {
            if (empty($extra['field_name']) || ! isset($extra['field_value'])) {
                continue;
            }
            $access->extraFields()->create([
                'field_name' => $extra['field_name'],
                'field_label' => $extra['field_label'] ?? $extra['field_name'],
                'field_value' => $extra['field_value'],
                'is_sensitive' => ! empty($extra['is_sensitive']),
                'position' => $i + 1,
            ]);
        }
    }

    private function validateAccess(Request $request, ?Access $access = null): array
    {
        return $request->validate([
            'type_id' => ['required', 'exists:access_types,id'],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'hostname' => ['nullable', 'string', 'max:200'],
            'ip' => ['nullable', 'string', 'max:45'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'url' => ['nullable', 'url', 'max:500'],
            'username' => ['nullable', 'string'],
            'password' => $access ? ['nullable', 'string'] : ['required', 'string', 'min:6'],
            'notes' => ['nullable', 'string'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'asset_id' => ['nullable', 'exists:assets,id'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['nullable'],
            'extras' => ['nullable', 'array'],
            'extras.*.field_name' => ['nullable', 'string', 'max:80'],
            'extras.*.field_label' => ['nullable', 'string', 'max:100'],
            'extras.*.field_value' => ['nullable', 'string'],
            'extras.*.is_sensitive' => ['nullable'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
