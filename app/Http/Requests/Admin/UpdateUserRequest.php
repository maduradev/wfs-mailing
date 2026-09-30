<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::ADMIN
            && $this->route('user')?->isNot($this->user());
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $departmentId = $this->input('department_id', $user->department_id);

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'username' => ['nullable', 'string', 'max:80', Rule::unique('users', 'username')->ignore($user)],
            'nik' => ['nullable', 'string', 'max:40', Rule::unique('users', 'nik')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['sometimes', 'required', Rule::enum(UserRole::class)],
            'department_id' => ['sometimes', 'nullable', 'exists:departments,id'],
            'position_id' => [
                'sometimes',
                'nullable',
                Rule::exists('positions', 'id')->where(
                    fn ($query) => $query->where('department_id', $departmentId),
                ),
            ],
            'password' => ['sometimes', 'nullable', 'string', 'min:12', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
