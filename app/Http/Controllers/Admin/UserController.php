<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Department;
use App\Models\User;
use App\Services\Shared\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->with(['department', 'position'])->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return $this->form(new User);
    }

    public function store(StoreUserRequest $request, AuditLogService $auditLog): RedirectResponse
    {
        $data = $request->validated();
        $user = new User;
        $user->fill(Arr::only($data, ['name', 'email', 'username', 'nik', 'phone', 'password']));
        $user->forceFill([
            'role' => $data['role'],
            'department_id' => $data['department_id'] ?? null,
            'position_id' => $data['position_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
        $user->save();
        $auditLog->record($request->user(), $user, 'USER_CREATED', ['role' => $user->role->value]);

        return redirect()->route('admin.users.index')->with('status', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        abort_if($user->is(auth()->user()), 403);

        return $this->form($user);
    }

    public function update(UpdateUserRequest $request, User $user, AuditLogService $auditLog): RedirectResponse
    {
        $data = $request->validated();
        $nextRole = $data['role'] ?? $user->role->value;
        $nextActive = $data['is_active'] ?? $user->is_active;

        if (
            $user->role === UserRole::ADMIN
            && $user->is_active
            && ($nextRole !== UserRole::ADMIN->value || ! $nextActive)
            && User::query()->where('role', UserRole::ADMIN->value)->where('is_active', true)->count() <= 1
        ) {
            throw ValidationException::withMessages([
                'role' => 'Administrator aktif terakhir tidak dapat dinonaktifkan atau diturunkan perannya.',
            ]);
        }

        $user->fill(Arr::only($data, ['name', 'email', 'username', 'nik', 'phone']));

        $managedFields = Arr::only($data, ['role', 'department_id', 'position_id', 'is_active']);

        if (! empty($data['password'])) {
            $managedFields['password'] = $data['password'];
        }

        $user->forceFill($managedFields)->save();
        $auditLog->record(
            $request->user(),
            $user,
            array_key_exists('is_active', $data) ? 'ACCOUNT_STATUS_CHANGED' : 'USER_UPDATED',
            ['changed_fields' => array_keys($data)],
        );

        return redirect()->route('admin.users.index')->with('status', 'Data pengguna berhasil diperbarui.');
    }

    private function form(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'roles' => UserRole::cases(),
            'departments' => Department::query()->orderBy('name')->get(),
        ]);
    }
}
