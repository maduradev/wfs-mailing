<?php

namespace App\Services\Shared;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserSignature;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class SignatureService
{
    public function upload(User $user, UploadedFile $file): UserSignature
    {
        $this->validatePng($file);
        $storedPath = null;

        try {
            return DB::transaction(function () use ($user, $file, &$storedPath): UserSignature {
                $lockedUser = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
                $previousSignatures = $lockedUser->signatures()->where('is_active', true)->lockForUpdate()->get();
                $version = ((int) $lockedUser->signatures()->max('version')) + 1;
                $storedPath = sprintf('signatures/%d/v%d-%s.png', $lockedUser->id, $version, Str::uuid());

                if (! Storage::disk('local')->put($storedPath, file_get_contents($file->getRealPath()))) {
                    throw new RuntimeException('Tanda tangan tidak dapat disimpan.');
                }

                foreach ($previousSignatures as $previousSignature) {
                    $previousSignature->forceFill(['is_active' => false])->save();
                }

                $signature = new UserSignature;
                $signature->forceFill([
                    'user_id' => $lockedUser->id,
                    'version' => $version,
                    'file_path' => $storedPath,
                    'mime_type' => 'image/png',
                    'file_size' => $file->getSize(),
                    'sha256' => hash_file('sha256', $file->getRealPath()),
                    'is_active' => true,
                    'uploaded_at' => now(),
                ])->save();

                $auditLog = new AuditLog;
                $auditLog->forceFill([
                    'actor_user_id' => $lockedUser->id,
                    'event' => $previousSignatures->isEmpty() ? 'SIGNATURE_UPLOADED' : 'SIGNATURE_REPLACED',
                    'metadata' => ['version' => $version, 'sha256' => $signature->sha256],
                    'created_at' => now(),
                ]);
                $auditLog->subject()->associate($signature);
                $auditLog->save();

                return $signature;
            });
        } catch (Throwable $exception) {
            if ($storedPath !== null) {
                Storage::disk('local')->delete($storedPath);
            }

            throw $exception;
        }
    }

    public function activeSignature(User $user): ?UserSignature
    {
        return $user->signatures()->where('is_active', true)->orderByDesc('version')->first();
    }

    public function contents(UserSignature $signature): string
    {
        abort_unless(Storage::disk('local')->exists($signature->file_path), 404);

        return Storage::disk('local')->get($signature->file_path);
    }

    private function validatePng(UploadedFile $file): void
    {
        $path = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());
        $detectedMime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        $imageInfo = @getimagesize($path);
        $size = $file->getSize();
        $width = is_array($imageInfo) ? ($imageInfo[0] ?? 0) : 0;
        $height = is_array($imageInfo) ? ($imageInfo[1] ?? 0) : 0;

        if (
            $extension !== 'png'
            || $detectedMime !== 'image/png'
            || ! is_array($imageInfo)
            || ($imageInfo['mime'] ?? null) !== 'image/png'
            || ($imageInfo[2] ?? null) !== IMAGETYPE_PNG
            || $size === false
            || $size > 2 * 1024 * 1024
            || $width < 64
            || $height < 24
            || $width > 4096
            || $height > 2048
        ) {
            $this->rejectInvalidPng();
        }

        $imageBytes = file_get_contents($path);
        $decodedImage = $imageBytes === false ? false : @imagecreatefromstring($imageBytes);

        if ($decodedImage === false) {
            $this->rejectInvalidPng();
        }

        imagedestroy($decodedImage);
    }

    private function rejectInvalidPng(): never
    {
        throw ValidationException::withMessages([
            'signature' => 'Berkas harus berupa PNG yang valid dengan ukuran dan dimensi yang didukung.',
        ]);
    }
}
