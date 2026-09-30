<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\LetterAttachment;
use App\Services\Shared\AttachmentService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LetterAttachmentController extends Controller
{
    public function download(LetterAttachment $attachment, AttachmentService $service): StreamedResponse
    {
        $this->authorize('downloadAttachment', $attachment->letter);

        return $service->download($attachment);
    }
}
