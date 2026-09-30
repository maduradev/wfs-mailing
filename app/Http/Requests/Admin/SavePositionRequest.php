<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::ADMIN;
    }

    public function rules(): array
    {
        $position = $this->route('position');
        $departmentId = $this->input('department_id', $position?->department_id);

        return [
            'department_id' => ['required', 'exists:departments,id'],
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('positions', 'code')->where(fn ($query) => $query->where('department_id', $departmentId))->ignore($position),
            ],
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('positions', 'name')->where(fn ($query) => $query->where('department_id', $departmentId))->ignore($position),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
