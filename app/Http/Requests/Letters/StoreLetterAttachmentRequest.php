<?php

namespace App\Http\Requests\Letters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreLetterAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('addAttachment', $this->route('letter')) === true;
    }

    public function rules(): array
    {
        return [
            'attachment' => [
                'required',
                File::types(['pdf', 'png', 'jpg', 'jpeg'])->max(10 * 1024),
                'mimetypes:application/pdf,image/png,image/jpeg',
            ],
        ];
    }
}
