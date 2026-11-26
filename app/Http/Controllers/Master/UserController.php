<?php

declare(strict_types=1);

namespace App\Http\Controllers\Master;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with(['unit', 'supervisor'])
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', '%' . $request->string('search') . '%')
                ->orWhere('email', 'like', '%' . $request->string('search') . '%')
                ->orWhere('employee_id', 'like', '%' . $request->string('search') . '%')))
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')->toString()))
            ->when($request->filled('unit_id'), fn ($q) => $q->where('unit_id', $request->integer('unit_id')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('master.users.index', [
            'users' => $users,
            'units' => Unit::orderBy('name')->get(),
            'supervisors' => User::whereIn('role', [UserRole::Admin->value, UserRole::Supervisor->value])->orderBy('name')->get(),
            'roles' => UserRole::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['nullable', 'string', 'max:50', 'unique:users,employee_id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'unit_id' => ['nullable', 'exists:units,id'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'position' => ['nullable', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        User::create([
            'employee_id' => $data['employee_id'] ?: null,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'unit_id' => $data['unit_id'] ?: null,
            'supervisor_id' => $data['supervisor_id'] ?: null,
            'position' => $data['position'] ?: null,
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('master.users.index')->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['nullable', 'string', 'max:50', Rule::unique('users', 'employee_id')->ignore($user->id)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'unit_id' => ['nullable', 'exists:units,id'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'position' => ['nullable', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'employee_id' => $data['employee_id'] ?: null,
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'unit_id' => $data['unit_id'] ?: null,
            'supervisor_id' => ($data['supervisor_id'] && (int) $data['supervisor_id'] !== $user->id) ? $data['supervisor_id'] : null,
            'position' => $data['position'] ?: null,
            'is_active' => $request->boolean('is_active'),
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        return redirect()->route('master.users.index')->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->tasks()->exists() || $user->workLogs()->exists() || $user->targets()->exists()) {
            $user->update(['is_active' => false]);

            return back()->with('success', 'Pengguna memiliki riwayat pekerjaan sehingga dinonaktifkan (bukan dihapus permanen).');
        }

        $user->delete();

        return redirect()->route('master.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
