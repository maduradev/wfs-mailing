<?php

namespace Tests\Feature;

use App\Enums\ApprovalStatus;
use App\Enums\LetterStatus;
use App\Enums\LetterType;
use App\Enums\UserRole;
use App\Models\Letter;
use App\Models\LetterApproval;
use App\Models\LetterAttachment;
use App\Models\User;
use App\Models\UserSignature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StepOneFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_domain_enums_are_limited_to_the_specified_values(): void
    {
        $this->assertSame(
            ['LEAVE', 'MUTATION', 'WARNING'],
            array_map(fn (LetterType $type) => $type->value, LetterType::cases()),
        );

        $this->assertSame(
            ['admin', 'product-manager', 'hrd-manager', 'oa-oc-manager', 'karyawan'],
            array_map(fn (UserRole $role) => $role->value, UserRole::cases()),
        );

        $this->assertSame('Perlu Perbaikan', LetterStatus::REVISION->label());
        $this->assertSame('Menunggu', ApprovalStatus::PENDING->label());
    }

    public function test_migrations_create_letter_signature_and_audit_foundations(): void
    {
        foreach ([
            'departments',
            'positions',
            'letters',
            'leave_letters',
            'mutation_letters',
            'warning_letters',
            'letter_approvals',
            'letter_histories',
            'letter_attachments',
            'user_signatures',
            'letter_signatures',
            'audit_logs',
            'notifications',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }

        $this->assertTrue(Schema::hasColumns('letter_signatures', [
            'user_signature_id',
            'signer_name_snapshot',
            'signature_path_snapshot',
            'signature_sha256_snapshot',
        ]));
        $this->assertTrue(Schema::hasColumn('letters', 'issued_snapshot'));
    }

    public function test_server_managed_fields_are_not_mass_assignable(): void
    {
        $this->assertNotContains('role', (new User)->getFillable());
        $this->assertNotContains('department_id', (new User)->getFillable());
        $this->assertNotContains('position_id', (new User)->getFillable());
        $this->assertNotContains('letter_number', (new Letter)->getFillable());
        $this->assertNotContains('status', (new Letter)->getFillable());
        $this->assertNotContains('issued_snapshot', (new Letter)->getFillable());
        $this->assertSame(['*'], (new LetterApproval)->getGuarded());
        $this->assertSame(['*'], (new LetterAttachment)->getGuarded());
        $this->assertSame(['*'], (new UserSignature)->getGuarded());
    }
}
