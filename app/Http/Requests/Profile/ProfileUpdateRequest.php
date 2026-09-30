<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $user = $this->user();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'username' => ['nullable', 'string', 'max:80', Rule::unique('users', 'username')->ignore($user)],
            'nik' => ['nullable', 'string', 'max:40', Rule::unique('users', 'nik')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
