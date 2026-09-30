<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::ADMIN;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:80', 'unique:users,username'],
            'nik' => ['nullable', 'string', 'max:40', 'unique:users,nik'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position_id' => [
                'nullable',
                Rule::exists('positions', 'id')->where(
                    fn ($query) => $query->where('department_id', $this->input('department_id')),
                ),
            ],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
