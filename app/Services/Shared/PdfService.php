<?php

namespace App\Services\Shared;

use App\Enums\LetterType;
use App\Models\Letter;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    public function preview(Letter $letter): string
    {
        $letter->loadMissing([
            'requester.department',
            'requester.position',
            'subject.department',
            'subject.position',
            'leaveDetails',
            'mutationDetails.fromDepartment',
            'mutationDetails.toDepartment',
            'mutationDetails.fromPosition',
            'mutationDetails.toPosition',
            'warningDetails',
        ]);

        $view = match ($letter->type) {
            LetterType::LEAVE => 'shared.letters.pdf.leave',
            LetterType::MUTATION => 'shared.letters.pdf.mutation',
            LetterType::WARNING => 'shared.letters.pdf.warning',
        };

        return Pdf::loadView($view, [
            'letter' => $letter,
            'previewNotice' => 'PRATINJAU - BELUM DITERBITKAN ATAU DITANDATANGANI',
        ])->setPaper('a4')->output();
    }
}
