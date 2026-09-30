<?php

namespace Tests\Feature;

use App\Enums\LetterStatus;
use App\Enums\LetterType;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\LetterAttachment;
use App\Models\Position;
use App\Models\User;
use App\Services\Shared\AttachmentService;
use App\Services\Shared\LetterNumberService;
use App\Services\Shared\LetterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StepThreeLetterServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_mutation_draft_uses_server_profile_for_origin_and_can_be_submitted(): void
    {
        [$employee, $sourceDepartment, $sourcePosition] = $this->employeeWithPosition();
        $destinationDepartment = Department::create(['code' => 'DST', 'name' => 'Tujuan']);
        $destinationPosition = Position::create([
            'department_id' => $destinationDepartment->id,
            'code' => 'DST-POS',
            'name' => 'Jabatan Tujuan',
        ]);
        $otherEmployee = User::factory()->create();

        $letter = app(LetterService::class)->createDraft($employee, [
            'type' => LetterType::MUTATION->value,
            'subject_user_id' => $otherEmployee->id,
            'title' => 'Permohonan mutasi',
            'reason' => 'Permohonan karyawan',
            'from_department_id' => $destinationDepartment->id,
            'from_position_id' => $destinationPosition->id,
            'to_department_id' => $destinationDepartment->id,
            'to_position_id' => $destinationPosition->id,
            'effective_on' => '2026-11-01',
            'details' => 'Catatan mutasi',
        ]);

        $this->assertSame(LetterStatus::DRAFT, $letter->status);
        $this->assertSame($employee->id, $letter->subject_user_id);
        $this->assertSame($employee->id, $letter->requested_by);
        $this->assertSame($sourceDepartment->id, $letter->mutationDetails->from_department_id);
        $this->assertSame($sourcePosition->id, $letter->mutationDetails->from_position_id);

        $letter = app(LetterService::class)->updateDraft($employee, $letter, [
            'title' => 'Permohonan mutasi diperbarui',
            'from_department_id' => $destinationDepartment->id,
            'from_position_id' => $destinationPosition->id,
            'to_department_id' => $destinationDepartment->id,
            'to_position_id' => $destinationPosition->id,
            'effective_on' => '2026-12-01',
        ]);

        $this->assertSame($sourceDepartment->id, $letter->mutationDetails->from_department_id);
        $this->assertSame($sourcePosition->id, $letter->mutationDetails->from_position_id);

        $submitted = app(LetterService::class)->submitDraft($employee, $letter);

        $this->assertSame(LetterStatus::SUBMITTED, $submitted->status);
        $this->assertNotNull($submitted->submitted_at);
        $this->assertDatabaseHas('letter_histories', [
            'letter_id' => $letter->id,
            'event' => 'SUBMITTED',
            'from_status' => LetterStatus::DRAFT->value,
            'to_status' => LetterStatus::SUBMITTED->value,
        ]);
    }

    public function test_leave_draft_and_warning_draft_store_only_their_own_details(): void
    {
        $employee = $this->user(UserRole::KARYAWAN);
        $leave = app(LetterService::class)->createDraft($employee, [
            'type' => LetterType::LEAVE->value,
            'subject_user_id' => null,
            'title' => 'Permohonan cuti',
            'leave_type' => 'Cuti sesuai kebijakan perusahaan',
            'starts_on' => '2026-10-10',
            'ends_on' => '2026-10-12',
            'total_days' => 3,
        ]);

        $this->assertSame($employee->id, $leave->subject_user_id);
        $this->assertSame('Cuti sesuai kebijakan perusahaan', $leave->leaveDetails->leave_type);
        $this->assertNull($leave->mutationDetails);
        $this->assertNull($leave->warningDetails);

        $hrd = $this->user(UserRole::HRD_MANAGER);
        $subject = $this->user(UserRole::KARYAWAN);
        $warning = app(LetterService::class)->createDraft($hrd, [
            'type' => LetterType::WARNING->value,
            'subject_user_id' => $subject->id,
            'title' => 'Surat Peringatan',
            'description' => 'Uraian yang menunggu peninjauan HRD.',
        ]);

        $this->assertSame($subject->id, $warning->subject_user_id);
        $this->assertSame('Uraian yang menunggu peninjauan HRD.', $warning->warningDetails->description);
        $this->assertNull($warning->leaveDetails);
        $this->assertNull($warning->mutationDetails);
    }

    public function test_submitted_letters_cannot_be_edited_again(): void
    {
        $employee = $this->user(UserRole::KARYAWAN);
        $letter = app(LetterService::class)->createDraft($employee, [
            'type' => LetterType::LEAVE->value,
            'title' => 'Permohonan cuti',
            'leave_type' => 'Cuti',
            'starts_on' => '2026-10-10',
            'ends_on' => '2026-10-10',
            'total_days' => 1,
        ]);

        app(LetterService::class)->submitDraft($employee, $letter);

        $this->expectException(ValidationException::class);
        app(LetterService::class)->updateDraft($employee, $letter, ['title' => 'Perubahan terlambat']);
    }

    public function test_letter_number_counters_are_unique_per_year_and_type(): void
    {
        $service = app(LetterNumberService::class);

        $this->assertSame('SURAT/CUTI/2026/0001', $service->next(LetterType::LEAVE, 2026));
        $this->assertSame('SURAT/CUTI/2026/0002', $service->next(LetterType::LEAVE, 2026));
        $this->assertSame('SURAT/MUTASI/2026/0001', $service->next(LetterType::MUTATION, 2026));
    }

    public function test_attachment_service_validates_and_stores_private_files_with_audit(): void
    {
        Storage::fake('local');
        $employee = $this->user(UserRole::KARYAWAN);
        $letter = app(LetterService::class)->createDraft($employee, [
            'type' => LetterType::LEAVE->value,
            'title' => 'Permohonan cuti',
            'leave_type' => 'Cuti',
            'starts_on' => '2026-10-10',
            'ends_on' => '2026-10-10',
            'total_days' => 1,
        ]);

        try {
            app(AttachmentService::class)->store(
                $employee,
                $letter,
                UploadedFile::fake()->createWithContent('palsu.png', '%PDF-1.4 bukan PNG'),
            );
            $this->fail('Lampiran dengan MIME dan extension berbeda harus ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('attachment', $exception->errors());
        }

        $attachment = app(AttachmentService::class)->store(
            $employee,
            $letter,
            UploadedFile::fake()->createWithContent('bukti.pdf', "%PDF-1.4\nIsi bukti\n%%EOF"),
        );

        $this->assertInstanceOf(LetterAttachment::class, $attachment);
        $this->assertSame('application/pdf', $attachment->mime_type);
        $this->assertSame(64, strlen($attachment->sha256));
        $this->assertStringStartsWith('letters/'.$letter->id.'/attachments/', $attachment->file_path);
        Storage::disk('local')->assertExists($attachment->file_path);
        $this->assertDatabaseHas('audit_logs', ['event' => 'ATTACHMENT_UPLOADED']);
    }

    private function employeeWithPosition(): array
    {
        $department = Department::create(['code' => 'SRC', 'name' => 'Asal']);
        $position = Position::create([
            'department_id' => $department->id,
            'code' => 'SRC-POS',
            'name' => 'Jabatan Asal',
        ]);
        $employee = $this->user(UserRole::KARYAWAN);
        $employee->forceFill([
            'department_id' => $department->id,
            'position_id' => $position->id,
        ])->save();

        return [$employee->fresh(), $department, $position];
    }

    private function user(UserRole $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();

        return $user->fresh();
    }
}
