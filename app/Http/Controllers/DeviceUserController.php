<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\DeviceUser;
use App\Models\Location;
use App\Models\User;
use App\Services\LocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DeviceUserController extends Controller
{
    public function __construct(private LocationService $locations)
    {
    }

    public function index(Request $request): View
    {
        $query = DeviceUser::with('location.parent', 'user');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                    ->orWhere('employee_code', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('print_code', 'like', "%$search%");
            });
        }
        if ($locationId = $request->input('location_id')) {
            // Include descendants
            $location = Location::find($locationId);
            if ($location) {
                $query->whereIn('location_id', $location->descendantIds());
            }
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->input('is_active'));
        }

        return view('device_users.index', [
            'deviceUsers' => $query->orderBy('full_name')->paginate(20)->withQueryString(),
            'flatLocations' => $this->locations->flatTree(),
            'filters' => $request->only(['q', 'location_id', 'is_active']),
        ]);
    }

    public function create(): View
    {
        return view('device_users.form', [
            'deviceUser' => new DeviceUser([
                'is_active' => true,
                'print_code' => $this->generateUniqueCode(),
            ]),
            'flatLocations' => $this->locations->flatTree(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
            'printers' => Asset::whereHas('category', fn ($q) => $q->where('name', 'like', '%mpresor%'))
                ->orWhere('type', 'printer')
                ->orderBy('internal_code')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateDeviceUser($request);
        $printerIds = $data['printers'] ?? [];
        unset($data['printers']);

        $deviceUser = DeviceUser::create($data);
        if (! empty($printerIds)) {
            $deviceUser->printers()->sync(
                collect($printerIds)->mapWithKeys(fn ($id) => [
                    $id => ['granted_at' => now(), 'granted_by' => auth()->id()],
                ])->all()
            );
        }
        return redirect()->route('device_users.show', $deviceUser)->with('success', 'Usuario de impresión creado.');
    }

    public function show(DeviceUser $deviceUser): View
    {
        $deviceUser->load('location.parent', 'user', 'printers');
        return view('device_users.show', [
            'deviceUser' => $deviceUser,
            'canSeeCode' => $deviceUser->isVisibleTo(auth()->user()),
        ]);
    }

    public function edit(DeviceUser $deviceUser): View
    {
        return view('device_users.form', [
            'deviceUser' => $deviceUser,
            'flatLocations' => $this->locations->flatTree(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
            'printers' => Asset::whereHas('category', fn ($q) => $q->where('name', 'like', '%mpresor%'))
                ->orWhere('type', 'printer')
                ->orderBy('internal_code')->get(),
        ]);
    }

    public function update(Request $request, DeviceUser $deviceUser): RedirectResponse
    {
        $data = $this->validateDeviceUser($request, $deviceUser);
        $printerIds = $data['printers'] ?? [];
        unset($data['printers']);

        $deviceUser->update($data);

        $deviceUser->printers()->sync(
            collect($printerIds)->mapWithKeys(fn ($id) => [
                $id => ['granted_at' => now(), 'granted_by' => auth()->id()],
            ])->all()
        );

        return redirect()->route('device_users.show', $deviceUser)->with('success', 'Usuario actualizado.');
    }

    public function regenerateCode(DeviceUser $deviceUser): RedirectResponse
    {
        $deviceUser->update(['print_code' => $this->generateUniqueCode()]);
        return back()->with('success', 'Código regenerado.');
    }

    public function toggleActive(DeviceUser $deviceUser): RedirectResponse
    {
        $deviceUser->update(['is_active' => ! $deviceUser->is_active]);
        return back()->with('success', 'Estatus actualizado.');
    }

    public function destroy(DeviceUser $deviceUser): RedirectResponse
    {
        $deviceUser->delete();
        return redirect()->route('device_users.index')->with('success', 'Usuario eliminado.');
    }

    /**
     * "My print code" page — anyone can see their own.
     */
    public function mine(Request $request): View
    {
        $user = $request->user();
        $deviceUser = DeviceUser::with('location.parent', 'printers')
            ->where('user_id', $user->id)
            ->first();

        return view('device_users.mine', [
            'deviceUser' => $deviceUser,
            'user' => $user,
        ]);
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = (string) random_int(1000, 999999);
        } while (DeviceUser::where('print_code', $code)->exists());
        return $code;
    }

    private function validateDeviceUser(Request $request, ?DeviceUser $deviceUser = null): array
    {
        return $request->validate([
            'employee_code' => ['nullable', 'string', 'max:50'],
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'mailbox' => ['nullable', 'string', 'max:150'],
            'print_code' => ['required', 'string', 'max:20',
                'unique:device_users,print_code'.($deviceUser ? ','.$deviceUser->id : '')],
            'location_id' => ['nullable', 'exists:locations,id'],
            'user_id' => ['nullable', 'exists:users,id',
                'unique:device_users,user_id'.($deviceUser ? ','.$deviceUser->id : '')],
            'is_active' => ['nullable'],
            'notes' => ['nullable', 'string'],
            'printers' => ['nullable', 'array'],
            'printers.*' => ['exists:assets,id'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
