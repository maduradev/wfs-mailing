<?php

namespace App\Services\Shared;

use App\Models\AuditLog;
use App\Models\Letter;
use App\Models\LetterAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AttachmentService
{
    private const MIME_BY_EXTENSION = [
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
    ];

    public function store(User $actor, Letter $letter, UploadedFile $file): LetterAttachment
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $detectedMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());

        if (
            $file->getSize() === false
            || $file->getSize() > 10 * 1024 * 1024
            || ! isset(self::MIME_BY_EXTENSION[$extension])
            || $detectedMime !== self::MIME_BY_EXTENSION[$extension]
        ) {
            throw ValidationException::withMessages([
                'attachment' => 'Lampiran harus berupa PDF, PNG, atau JPEG yang valid dan berukuran maksimal 10 MB.',
            ]);
        }

        $contents = file_get_contents($file->getRealPath());

        if ($contents === false || ! $this->hasValidFileSignature($extension, $contents)) {
            throw ValidationException::withMessages([
                'attachment' => 'Isi berkas lampiran tidak sesuai dengan formatnya.',
            ]);
        }

        $path = sprintf('letters/%d/attachments/%s.%s', $letter->id, Str::uuid(), $extension);

        try {
            return DB::transaction(function () use ($actor, $letter, $file, $extension, $detectedMime, $contents, $path): LetterAttachment {
                if (! Storage::disk('local')->put($path, $contents)) {
                    throw new RuntimeException('Lampiran tidak dapat disimpan.');
                }

                $attachment = new LetterAttachment;
                $attachment->forceFill([
                    'uploaded_by' => $actor->id,
                    'disk' => 'local',
                    'file_path' => $path,
                    'original_name' => $this->safeOriginalName($file->getClientOriginalName()),
                    'mime_type' => $detectedMime,
                    'file_size' => strlen($contents),
                    'sha256' => hash('sha256', $contents),
                ]);
                $letter->attachments()->save($attachment);

                $audit = new AuditLog;
                $audit->forceFill([
                    'actor_user_id' => $actor->id,
                    'event' => 'ATTACHMENT_UPLOADED',
                    'metadata' => ['attachment_id' => $attachment->id, 'extension' => $extension],
                    'created_at' => now(),
                ]);
                $audit->subject()->associate($letter);
                $audit->save();

                return $attachment;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }
    }

    public function download(LetterAttachment $attachment): StreamedResponse
    {
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->file_path), 404);

        return Storage::disk($attachment->disk)->download(
            $attachment->file_path,
            $attachment->original_name,
            [
                'Content-Type' => $attachment->mime_type,
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }

    private function hasValidFileSignature(string $extension, string $contents): bool
    {
        return match ($extension) {
            'pdf' => str_starts_with($contents, '%PDF-'),
            'png' => str_starts_with($contents, "\x89PNG\r\n\x1a\n") && @getimagesizefromstring($contents) !== false,
            'jpg', 'jpeg' => str_starts_with($contents, "\xff\xd8\xff") && @getimagesizefromstring($contents) !== false,
            default => false,
        };
    }

    private function safeOriginalName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[\x00-\x1F\x7F]/', '', $name) ?? 'lampiran';

        return mb_substr($name, 0, 255);
    }
}
