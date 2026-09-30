<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::ADMIN;
    }

    public function rules(): array
    {
        $department = $this->route('department');

        return [
            'code' => ['required', 'string', 'max:40', Rule::unique('departments', 'code')->ignore($department)],
            'name' => ['required', 'string', 'max:120', Rule::unique('departments', 'name')->ignore($department)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
