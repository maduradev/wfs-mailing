<?php

namespace Tests\Feature;

use App\Enums\LetterStatus;
use App\Enums\LetterType;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Letter;
use App\Models\LetterAttachment;
use App\Models\Position;
use App\Models\User;
use App\Services\Shared\LetterService;
use App\Services\Shared\PdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StepThreeLetterWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_create_view_edit_and_submit_own_leave_draft(): void
    {
        $employee = $this->user(UserRole::KARYAWAN);
        $payload = [
            'type' => LetterType::LEAVE->value,
            'title' => 'Permohonan cuti keluarga',
            'reason' => 'Keperluan keluarga',
            'leave_type' => 'Cuti sesuai kebijakan perusahaan',
            'starts_on' => '2026-10-10',
            'ends_on' => '2026-10-12',
            'total_days' => 3,
        ];

        $this->actingAs($employee)->post(route('letters.store'), $payload)
            ->assertRedirect();

        $letter = Letter::query()->where('requested_by', $employee->id)->firstOrFail();
        $this->get(route('letters.show', $letter))->assertOk()->assertSee('Permohonan cuti keluarga');
        $this->put(route('letters.update', $letter), $payload + ['title' => 'Judul pengganti'])
            ->assertRedirect(route('letters.show', $letter));

        $this->post(route('letters.submit', $letter))->assertRedirect(route('letters.show', $letter));
        $this->assertSame(LetterStatus::SUBMITTED, $letter->fresh()->status);
        $this->put(route('letters.update', $letter), ['title' => 'Tidak boleh diubah'])
            ->assertForbidden();
    }

    public function test_employee_cannot_create_warning_and_validation_rejects_unknown_leave_policy_fields(): void
    {
        $employee = $this->user(UserRole::KARYAWAN);

        $this->actingAs($employee)->get(route('letters.create', LetterType::WARNING->value))->assertForbidden();

        $this->post(route('letters.store'), [
            'type' => LetterType::LEAVE->value,
            'title' => 'Permohonan cuti',
            'starts_on' => '2026-10-12',
            'ends_on' => '2026-10-10',
            'total_days' => 0,
        ])->assertSessionHasErrors(['leave_type', 'ends_on', 'total_days']);

        $hrd = $this->user(UserRole::HRD_MANAGER);
        $subject = $this->user(UserRole::KARYAWAN);
        $this->actingAs($hrd)->post(route('letters.store'), [
            'type' => LetterType::WARNING->value,
            'subject_user_id' => $subject->id,
            'title' => 'Draf peringatan untuk ditinjau',
            'description' => 'Uraian yang disiapkan HRD.',
        ])->assertRedirect();

        $sourceDepartment = Department::create(['code' => 'MUT-A', 'name' => 'Mutasi A']);
        $targetDepartment = Department::create(['code' => 'MUT-B', 'name' => 'Mutasi B']);
        $foreignPosition = Position::create([
            'department_id' => $sourceDepartment->id,
            'code' => 'MUT-A-POS',
            'name' => 'Jabatan A',
        ]);

        $this->post(route('letters.store'), [
            'type' => LetterType::MUTATION->value,
            'subject_user_id' => $subject->id,
            'title' => 'Mutasi dengan jabatan tidak cocok',
            'to_department_id' => $targetDepartment->id,
            'to_position_id' => $foreignPosition->id,
            'effective_on' => '2026-12-01',
        ])->assertSessionHasErrors('to_position_id');

        $warning = Letter::query()->where('type', LetterType::WARNING->value)->firstOrFail();
        $this->get(route('letters.create', LetterType::MUTATION->value))
            ->assertOk()
            ->assertSee($subject->name);
        $this->get(route('letters.create', LetterType::WARNING->value))
            ->assertOk()
            ->assertSee($subject->name);
        $this->assertSame(LetterStatus::DRAFT, $warning->status);
        $this->assertNull($warning->letter_number);
    }

    public function test_letter_list_is_scoped_for_employee_product_manager_hrd_oaoc_and_admin(): void
    {
        $department = Department::create(['code' => 'A', 'name' => 'Departemen A']);
        $otherDepartment = Department::create(['code' => 'B', 'name' => 'Departemen B']);
        $employeeA = $this->user(UserRole::KARYAWAN, ['department_id' => $department->id]);
        $employeeB = $this->user(UserRole::KARYAWAN, ['department_id' => $otherDepartment->id]);
        $letterA = $this->leaveDraft($employeeA, 'Surat internal A');
        $letterB = $this->leaveDraft($employeeB, 'Surat internal B');
        $letterB->forceFill(['status' => LetterStatus::APPROVED])->save();

        $this->actingAs($employeeA)->get(route('letters.index'))
            ->assertOk()
            ->assertSee('Surat internal A')
            ->assertDontSee('Surat internal B');
        $this->get(route('letters.index', ['q' => 'INTERNAL A']))
            ->assertOk()
            ->assertSee('Surat internal A')
            ->assertDontSee('Surat internal B');
        $this->get(route('letters.show', $letterB))->assertForbidden();

        $productManager = $this->user(UserRole::PRODUCT_MANAGER, ['department_id' => $department->id]);
        $this->actingAs($productManager)->get(route('letters.index'))
            ->assertOk()
            ->assertSee('Surat internal A')
            ->assertDontSee('Surat internal B');

        $hrd = $this->user(UserRole::HRD_MANAGER);
        $this->actingAs($hrd)->get(route('letters.index'))
            ->assertOk()
            ->assertSee('Surat internal A')
            ->assertSee('Surat internal B');

        $oaoc = $this->user(UserRole::OA_OC_MANAGER);
        $this->actingAs($oaoc)->get(route('letters.index'))
            ->assertOk()
            ->assertDontSee('Surat internal A')
            ->assertSee('Surat internal B');

        $admin = $this->user(UserRole::ADMIN);
        $this->actingAs($admin)->get(route('letters.index'))->assertForbidden();
    }

    public function test_pdf_preview_is_a_private_unsigned_pdf_and_is_audited(): void
    {
        $employee = $this->user(UserRole::KARYAWAN);
        $letter = $this->leaveDraft($employee, 'Cuti untuk pratinjau');

        $response = $this->actingAs($employee)->get(route('letters.preview', $letter));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Cache-Control', 'no-store, private');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertSame(0, $letter->signatures()->count());
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'PDF_PREVIEWED',
            'subject_type' => Letter::class,
            'subject_id' => $letter->id,
        ]);

        $otherEmployee = $this->user(UserRole::KARYAWAN);
        $this->actingAs($otherEmployee)->get(route('letters.preview', $letter))->assertForbidden();
    }

    public function test_pdf_service_renders_all_three_letter_templates_as_unsigned_previews(): void
    {
        $employee = $this->user(UserRole::KARYAWAN);
        $leave = $this->leaveDraft($employee, 'Pratinjau cuti');

        $department = Department::create(['code' => 'PDF', 'name' => 'PDF']);
        $position = Position::create([
            'department_id' => $department->id,
            'code' => 'PDF-POS',
            'name' => 'Jabatan PDF',
        ]);
        $mutation = app(LetterService::class)->createDraft($employee, [
            'type' => LetterType::MUTATION->value,
            'title' => 'Pratinjau mutasi',
            'to_department_id' => $department->id,
            'to_position_id' => $position->id,
            'effective_on' => '2026-12-01',
        ]);

        $hrd = $this->user(UserRole::HRD_MANAGER);
        $subject = $this->user(UserRole::KARYAWAN);
        $warning = app(LetterService::class)->createDraft($hrd, [
            'type' => LetterType::WARNING->value,
            'subject_user_id' => $subject->id,
            'title' => 'Pratinjau peringatan',
            'description' => 'Draf internal untuk ditinjau.',
        ]);

        foreach ([$leave, $mutation, $warning] as $letter) {
            $pdf = app(PdfService::class)->preview($letter);
            $this->assertStringStartsWith('%PDF-', $pdf);
            $this->assertGreaterThan(1000, strlen($pdf));
            $this->assertSame(0, $letter->signatures()->count());
        }
    }

    public function test_attachment_is_private_and_download_checks_letter_policy(): void
    {
        Storage::fake('local');
        $employee = $this->user(UserRole::KARYAWAN);
        $letter = $this->leaveDraft($employee, 'Cuti dengan bukti');

        $this->actingAs($employee)->post(route('letters.attachments.store', $letter), [
            'attachment' => UploadedFile::fake()->createWithContent('bukti.pdf', "%PDF-1.4\nBukti\n%%EOF"),
        ])->assertRedirect(route('letters.show', $letter));

        $attachment = LetterAttachment::query()->where('letter_id', $letter->id)->firstOrFail();
        Storage::disk('local')->assertExists($attachment->file_path);
        $this->get(route('letters.attachments.download', $attachment))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');

        $otherEmployee = $this->user(UserRole::KARYAWAN);
        $this->actingAs($otherEmployee)->get(route('letters.attachments.download', $attachment))->assertForbidden();
    }

    private function leaveDraft(User $employee, string $title): Letter
    {
        return app(LetterService::class)->createDraft($employee, [
            'type' => LetterType::LEAVE->value,
            'title' => $title,
            'leave_type' => 'Cuti sesuai kebijakan perusahaan',
            'starts_on' => '2026-10-10',
            'ends_on' => '2026-10-10',
            'total_days' => 1,
        ]);
    }

    private function user(UserRole $role, array $attributes = []): User
    {
        $user = User::factory()->create();
        $user->forceFill($attributes + ['role' => $role, 'is_active' => true])->save();

        return $user->fresh();
    }
}
