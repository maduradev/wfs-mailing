<?php

namespace App\Http\Controllers\Shared;

use App\Enums\LetterStatus;
use App\Enums\LetterType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Letters\StoreLetterAttachmentRequest;
use App\Http\Requests\Letters\StoreLetterRequest;
use App\Http\Requests\Letters\UpdateLetterRequest;
use App\Models\Department;
use App\Models\Letter;
use App\Models\Position;
use App\Models\User;
use App\Services\Shared\AttachmentService;
use App\Services\Shared\AuditLogService;
use App\Services\Shared\LetterService;
use App\Services\Shared\PdfService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LetterController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Letter::class);
        $query = $this->visibleLetters($request->user());
        $type = LetterType::tryFrom((string) $request->query('type'));
        $status = LetterStatus::tryFrom((string) $request->query('status'));
        $search = trim((string) $request->query('q', ''));

        if ($type !== null) {
            $query->where('type', $type->value);
        }

        if ($status !== null) {
            $query->where('status', $status->value);
        }

        if ($search !== '') {
            $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function (Builder $builder) use ($search, $operator): void {
                $builder->where('title', $operator, '%'.$search.'%')
                    ->orWhere('letter_number', $operator, '%'.$search.'%')
                    ->orWhereHas('subject', fn (Builder $subject) => $subject->where('name', $operator, '%'.$search.'%'));
            });
        }

        return view('shared.letters.index', [
            'letters' => $query->latest('updated_at')->paginate(15)->withQueryString(),
            'types' => LetterType::cases(),
            'statuses' => LetterStatus::cases(),
            'filters' => ['type' => $type?->value, 'status' => $status?->value, 'q' => $search],
        ]);
    }

    public function create(string $type): View
    {
        $letterType = LetterType::tryFrom($type);
        abort_if($letterType === null, 404);
        $this->authorize('createType', [Letter::class, $letterType]);

        return view('shared.letters.form', [
            'letter' => new Letter(['type' => $letterType]),
            'type' => $letterType,
            'employees' => $this->employeeOptions($letterType),
            'departments' => Department::query()->where('is_active', true)->orderBy('name')->get(),
            'positions' => Position::query()->where('is_active', true)->with('department')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreLetterRequest $request, LetterService $service): RedirectResponse
    {
        $letter = $service->createDraft($request->user(), $request->validated());

        return redirect()->route('letters.show', $letter)->with('status', 'Draf surat berhasil dibuat.');
    }

    public function show(Letter $letter): View
    {
        $this->authorize('view', $letter);
        $letter->load([
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
            'histories.actor',
            'attachments.uploader',
        ]);

        return view('shared.letters.show', ['letter' => $letter]);
    }

    public function edit(Letter $letter): View
    {
        $this->authorize('update', $letter);

        return view('shared.letters.form', [
            'letter' => $letter->load(['leaveDetails', 'mutationDetails', 'warningDetails']),
            'type' => $letter->type,
            'employees' => $this->employeeOptions($letter->type),
            'departments' => Department::query()->where('is_active', true)->orderBy('name')->get(),
            'positions' => Position::query()->where('is_active', true)->with('department')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateLetterRequest $request, Letter $letter, LetterService $service): RedirectResponse
    {
        $updated = $service->updateDraft($request->user(), $letter, $request->validated());

        return redirect()->route('letters.show', $updated)->with('status', 'Draf surat berhasil diperbarui.');
    }

    public function submit(Request $request, Letter $letter, LetterService $service): RedirectResponse
    {
        $this->authorize('submit', $letter);
        $submitted = $service->submitDraft($request->user(), $letter);

        return redirect()->route('letters.show', $submitted)->with('status', 'Surat berhasil diajukan untuk ditinjau.');
    }

    public function storeAttachment(StoreLetterAttachmentRequest $request, Letter $letter, AttachmentService $service): RedirectResponse
    {
        $service->store($request->user(), $letter, $request->file('attachment'));

        return redirect()->route('letters.show', $letter)->with('status', 'Lampiran berhasil diunggah.');
    }

    public function preview(Request $request, Letter $letter, PdfService $pdfService, AuditLogService $auditLog): Response
    {
        $this->authorize('previewPdf', $letter);
        $pdf = $pdfService->preview($letter);
        $auditLog->record($request->user(), $letter, 'PDF_PREVIEWED', ['sha256' => hash('sha256', $pdf)]);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="pratinjau-surat-'.$letter->id.'.pdf"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function employeeOptions(LetterType $type): Collection
    {
        if (
            auth()->user()->role !== UserRole::HRD_MANAGER
            || ! in_array($type, [LetterType::MUTATION, LetterType::WARNING], true)
        ) {
            return collect();
        }

        return User::query()
            ->with(['department', 'position'])
            ->where('role', UserRole::KARYAWAN->value)
            ->orderBy('name')
            ->get();
    }

    private function visibleLetters(User $user): Builder
    {
        $query = Letter::query()->with(['requester', 'subject']);

        if ($user->role === UserRole::KARYAWAN) {
            return $query->where(fn (Builder $builder) => $builder
                ->where('requested_by', $user->id)
                ->orWhere('subject_user_id', $user->id));
        }

        if ($user->role === UserRole::PRODUCT_MANAGER) {
            if ($user->department_id === null) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where(function (Builder $builder) use ($user): void {
                $builder->whereHas('requester', fn (Builder $requester) => $requester->where('department_id', $user->department_id))
                    ->orWhereHas('subject', fn (Builder $subject) => $subject->where('department_id', $user->department_id));
            });
        }

        if ($user->role === UserRole::OA_OC_MANAGER) {
            return $query->whereIn('status', [
                LetterStatus::APPROVED->value,
                LetterStatus::ISSUED->value,
                LetterStatus::COMPLETED->value,
                LetterStatus::ARCHIVED->value,
            ]);
        }

        return $query;
    }
}
