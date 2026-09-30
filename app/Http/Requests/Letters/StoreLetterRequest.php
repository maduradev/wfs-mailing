<?php

namespace App\Http\Requests\Letters;

use App\Enums\LetterType;
use App\Models\Letter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $type = LetterType::tryFrom((string) $this->input('type'));

        return $type !== null && $this->user()?->can('createType', [Letter::class, $type]) === true;
    }

    public function rules(): array
    {
        $type = LetterType::tryFrom((string) $this->input('type'));
        $rules = [
            'type' => ['required', Rule::enum(LetterType::class)],
            'subject_user_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:200'],
            'reason' => ['nullable', 'string', 'max:5000'],
        ];

        if ($this->user()?->role->value !== 'karyawan') {
            $rules['subject_user_id'] = ['required', 'exists:users,id'];
        }

        return $rules + $this->detailRules($type, $this->all());
    }

    public function detailRules(?LetterType $type, array $input = []): array
    {
        return match ($type) {
            LetterType::LEAVE => [
                'leave_type' => ['required', 'string', 'max:100'],
                'starts_on' => ['required', 'date'],
                'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
                'total_days' => ['required', 'numeric', 'gt:0'],
                'details' => ['nullable', 'string', 'max:5000'],
                'return_address' => ['nullable', 'string', 'max:1000'],
            ],
            LetterType::MUTATION => [
                'from_department_id' => ['nullable', 'exists:departments,id'],
                'to_department_id' => ['required', 'exists:departments,id'],
                'from_position_id' => [
                    'nullable',
                    Rule::exists('positions', 'id')->where(
                        fn ($query) => $query->where('department_id', $input['from_department_id'] ?? null),
                    ),
                ],
                'to_position_id' => [
                    'required',
                    Rule::exists('positions', 'id')->where(
                        fn ($query) => $query->where('department_id', $input['to_department_id'] ?? null),
                    ),
                ],
                'effective_on' => ['required', 'date'],
                'details' => ['nullable', 'string', 'max:5000'],
            ],
            LetterType::WARNING => [
                'warning_level' => ['nullable', 'string', 'max:30'],
                'offense_on' => ['nullable', 'date'],
                'description' => ['required', 'string', 'max:10000'],
                'legal_basis' => ['nullable', 'string', 'max:5000'],
                'valid_from' => ['nullable', 'date'],
                'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            ],
            default => [],
        };
    }
}
