<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('roles', 'manager');
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('employee_code', 'like', "%$search%");
            });
        }
        if ($dept = $request->input('department')) {
            $query->where('department', $dept);
        }
        $users = $query->orderBy('name')->paginate(20)->withQueryString();
        return view('users.index', [
            'users' => $users,
            'filters' => $request->only(['q', 'department']),
        ]);
    }

    public function create(): View
    {
        return view('users.form', [
            'user' => new User(['is_active' => true]),
            'roles' => Role::orderBy('label')->get(),
            'managers' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateUser($request);
        $roles = $data['roles'] ?? [];
        $tempPassword = $data['password'] ?? null;
        if (empty($tempPassword)) {
            $tempPassword = str()->random(12);
        }
        $data['password'] = Hash::make($tempPassword);
        unset($data['roles']);
        $user = User::create($data);
        $user->roles()->sync($roles);

        $user->notify(new \App\Notifications\WelcomeUser($tempPassword));

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado. Se envió el correo de bienvenida.');
    }

    public function edit(User $user): View
    {
        return view('users.form', [
            'user' => $user,
            'roles' => Role::orderBy('label')->get(),
            'managers' => User::where('id', '!=', $user->id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validateUser($request, $user);
        $roles = $data['roles'] ?? [];
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        unset($data['roles']);
        $user->update($data);
        $user->roles()->sync($roles);
        return redirect()->route('users.index')->with('success', 'Usuario actualizado.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $user->update(['is_active' => ! $user->is_active]);
        return back()->with('success', 'Estatus actualizado.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email',
                'unique:users,email'.($user ? ','.$user->id : '')],
            'employee_code' => ['nullable', 'string', 'max:50',
                'unique:users,employee_code'.($user ? ','.$user->id : '')],
            'department' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
            'password' => $user ? ['nullable', 'string', 'min:8'] : ['required', 'string', 'min:8'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
        ]);
    }
}
