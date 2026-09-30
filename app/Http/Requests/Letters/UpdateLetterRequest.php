<?php

namespace App\Http\Requests\Letters;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('letter')) === true;
    }

    public function rules(): array
    {
        $letter = $this->route('letter');
        $storeRules = new StoreLetterRequest;
        $this->merge(['type' => $letter->type->value]);

        return collect($storeRules->detailRules($letter->type, $this->all()))
            ->map(fn (array $rules) => array_merge(['sometimes'], $rules))
            ->all() + [
                'title' => ['sometimes', 'required', 'string', 'max:200'],
                'reason' => ['sometimes', 'nullable', 'string', 'max:5000'],
            ];
    }
}
