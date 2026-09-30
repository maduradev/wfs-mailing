<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class StoreSignatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'signature' => ['required', 'file', 'max:2048', 'mimetypes:image/png', 'extensions:png'],
        ];
    }
}
