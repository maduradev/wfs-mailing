<?php

namespace App\Services\Shared;

use App\Enums\LetterStatus;
use App\Enums\LetterType;
use App\Enums\UserRole;
use App\Models\Letter;
use App\Models\LetterHistory;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LetterService
{
    public function createDraft(User $actor, array $data): Letter
    {
        return DB::transaction(function () use ($actor, $data): Letter {
            $type = $data['type'] instanceof LetterType ? $data['type'] : LetterType::from($data['type']);
            $isEmployee = $actor->role === UserRole::KARYAWAN;
            $letter = new Letter;
            $letter->forceFill([
                'type' => $type,
                'status' => LetterStatus::DRAFT,
                'requested_by' => $actor->id,
                'subject_user_id' => $isEmployee ? $actor->id : $data['subject_user_id'],
                'title' => $data['title'],
                'reason' => $data['reason'] ?? null,
            ])->save();

            $details = Arr::except($data, [
                'type',
                'subject_user_id',
                'title',
                'reason',
            ]);

            if ($type === LetterType::MUTATION && $isEmployee) {
                $details['from_department_id'] = $actor->department_id;
                $details['from_position_id'] = $actor->position_id;
            }

            $this->saveDetails($letter, $type, $details);
            $this->recordHistory($letter, $actor, 'CREATED', null, LetterStatus::DRAFT, ['type' => $type->value]);

            return $letter->fresh(['leaveDetails', 'mutationDetails', 'warningDetails']);
        });
    }

    public function updateDraft(User $actor, Letter $letter, array $data): Letter
    {
        return DB::transaction(function () use ($actor, $letter, $data): Letter {
            $lockedLetter = Letter::query()->whereKey($letter->getKey())->lockForUpdate()->firstOrFail();

            if ($lockedLetter->status !== LetterStatus::DRAFT) {
                throw ValidationException::withMessages([
                    'letter' => 'Surat yang sudah diajukan tidak dapat diubah.',
                ]);
            }

            $lockedLetter->fill(Arr::only($data, ['title', 'reason']))->save();
            $details = Arr::except($data, ['title', 'reason', 'subject_user_id']);
            if ($lockedLetter->type === LetterType::MUTATION && $actor->role === UserRole::KARYAWAN) {
                $details['from_department_id'] = $actor->department_id;
                $details['from_position_id'] = $actor->position_id;
            }
            $this->saveDetails($lockedLetter, $lockedLetter->type, $details);
            $this->recordHistory(
                $lockedLetter,
                $actor,
                'UPDATED',
                LetterStatus::DRAFT,
                LetterStatus::DRAFT,
                ['changed_fields' => array_keys($data)],
            );

            return $lockedLetter->fresh(['leaveDetails', 'mutationDetails', 'warningDetails']);
        });
    }

    public function submitDraft(User $actor, Letter $letter): Letter
    {
        return DB::transaction(function () use ($actor, $letter): Letter {
            $lockedLetter = Letter::query()->whereKey($letter->getKey())->lockForUpdate()->firstOrFail();

            if ($lockedLetter->status !== LetterStatus::DRAFT) {
                throw ValidationException::withMessages([
                    'letter' => 'Hanya draf yang dapat diajukan.',
                ]);
            }

            $detailsRelation = match ($lockedLetter->type) {
                LetterType::LEAVE => 'leaveDetails',
                LetterType::MUTATION => 'mutationDetails',
                LetterType::WARNING => 'warningDetails',
            };

            if (! $lockedLetter->{$detailsRelation}()->exists()) {
                throw ValidationException::withMessages([
                    'letter' => 'Lengkapi rincian surat sebelum mengajukan.',
                ]);
            }

            $lockedLetter->forceFill([
                'status' => LetterStatus::SUBMITTED,
                'submitted_at' => now(),
            ])->save();
            $this->recordHistory($lockedLetter, $actor, 'SUBMITTED', LetterStatus::DRAFT, LetterStatus::SUBMITTED);

            return $lockedLetter->fresh(['leaveDetails', 'mutationDetails', 'warningDetails']);
        });
    }

    private function saveDetails(Letter $letter, LetterType $type, array $details): void
    {
        $relation = match ($type) {
            LetterType::LEAVE => $letter->leaveDetails(),
            LetterType::MUTATION => $letter->mutationDetails(),
            LetterType::WARNING => $letter->warningDetails(),
        };

        $relation->updateOrCreate([], $details);
    }

    private function recordHistory(
        Letter $letter,
        User $actor,
        string $event,
        ?LetterStatus $fromStatus,
        LetterStatus $toStatus,
        array $metadata = [],
    ): void {
        $history = new LetterHistory;
        $history->forceFill([
            'actor_user_id' => $actor->id,
            'event' => $event,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
        $letter->histories()->save($history);
    }
}
