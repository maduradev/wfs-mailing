<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\User;
use App\Models\UserSignature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StepTwoAccessProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_logout_inactive_accounts_and_role_restriction(): void
    {
        $employee = $this->makeUser(UserRole::KARYAWAN, ['username' => 'pegawai-uji']);

        $this->post(route('login.store'), [
            'login' => $employee->email,
            'password' => 'password',
        ])->assertRedirect(route('karyawan.dashboard'));

        $this->assertAuthenticatedAs($employee);
        $this->get(route('karyawan.dashboard'))->assertOk();
        $this->get(route('admin.dashboard'))->assertForbidden();

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();

        $this->post(route('login.store'), [
            'login' => $employee->username,
            'password' => 'password',
        ])->assertRedirect(route('karyawan.dashboard'));

        $this->post(route('logout'))->assertRedirect(route('login'));

        $inactive = $this->makeUser(UserRole::KARYAWAN, ['is_active' => false]);
        $this->post(route('login.store'), [
            'login' => $inactive->email,
            'password' => 'password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_login_validation_errors_are_displayed_in_indonesian(): void
    {
        $this->assertSame('id', app()->getLocale());

        $this->post(route('login.store'), [])->assertSessionHasErrors([
            'login' => 'email atau nama pengguna wajib diisi.',
            'password' => 'kata sandi wajib diisi.',
        ]);
    }

    public function test_every_role_can_open_only_its_profile_route(): void
    {
        foreach (UserRole::cases() as $role) {
            $user = $this->makeUser($role);

            $this->actingAs($user)
                ->get(route($role->value.'.profile'))
                ->assertOk()
                ->assertSee('Informasi Pribadi')
                ->assertSee('Tanda Tangan');
        }
    }

    public function test_profile_update_ignores_administrative_fields_and_password_requires_current_value(): void
    {
        $employee = $this->makeUser(UserRole::KARYAWAN);

        $this->actingAs($employee)->put(route('karyawan.profile.update'), [
            'name' => 'Nama Baru',
            'email' => $employee->email,
            'username' => 'pegawai-baru',
            'nik' => 'NIK-001',
            'phone' => '08123456789',
            'role' => UserRole::ADMIN->value,
            'department_id' => 999,
            'position_id' => 999,
            'is_active' => false,
        ])->assertSessionHasNoErrors();

        $employee->refresh();
        $this->assertSame('Nama Baru', $employee->name);
        $this->assertSame(UserRole::KARYAWAN, $employee->role);
        $this->assertTrue($employee->is_active);
        $this->assertNull($employee->department_id);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'PROFILE_UPDATED',
            'subject_type' => User::class,
            'subject_id' => $employee->id,
        ]);

        $this->put(route('karyawan.profile.password'), [
            'current_password' => 'wrong-password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertSessionHasErrors('current_password');

        $this->put(route('karyawan.profile.password'), [
            'current_password' => 'password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect(route('karyawan.profile'));

        $this->assertTrue(Hash::check('new-secure-password', $employee->fresh()->password));
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'PASSWORD_CHANGED',
            'subject_type' => User::class,
            'subject_id' => $employee->id,
        ]);
    }

    public function test_admin_can_create_a_user_but_other_roles_cannot_manage_users(): void
    {
        $admin = $this->makeUser(UserRole::ADMIN);
        $employee = $this->makeUser(UserRole::KARYAWAN);

        $this->actingAs($employee)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($admin)->put(route('admin.users.update', $admin), ['is_active' => false])->assertForbidden();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Manajer HRD',
            'email' => 'hrd@example.test',
            'username' => 'hrd-manager',
            'role' => UserRole::HRD_MANAGER->value,
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
            'is_active' => true,
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'hrd@example.test',
            'role' => UserRole::HRD_MANAGER->value,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'USER_CREATED',
            'subject_type' => User::class,
            'subject_id' => User::query()->where('email', 'hrd@example.test')->value('id'),
        ]);

        $this->get(route('admin.users.index'))->assertOk()->assertSee('Manajer HRD');
        $createdUser = User::query()->where('email', 'hrd@example.test')->firstOrFail();

        $this->put(route('admin.users.update', $createdUser), ['is_active' => false])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'hrd@example.test',
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'ACCOUNT_STATUS_CHANGED',
            'subject_type' => User::class,
            'subject_id' => $createdUser->id,
        ]);
    }

    public function test_admin_can_manage_departments_positions_and_assign_them_to_users(): void
    {
        $admin = $this->makeUser(UserRole::ADMIN);
        $this->actingAs($admin);

        $this->post(route('admin.departments.store'), [
            'code' => 'PROD',
            'name' => 'Produk',
            'is_active' => true,
        ])->assertRedirect(route('admin.departments.index'));

        $department = Department::query()->where('code', 'PROD')->firstOrFail();

        $this->post(route('admin.positions.store'), [
            'department_id' => $department->id,
            'code' => 'PM',
            'name' => 'Manajer Produk',
            'is_active' => true,
        ])->assertRedirect(route('admin.positions.index'));

        $positionId = $department->positions()->value('id');
        $this->assertDatabaseHas('audit_logs', ['event' => 'DEPARTMENT_CREATED']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'POSITION_CREATED']);

        $this->post(route('admin.users.store'), [
            'name' => 'Manajer Produk',
            'email' => 'product@example.test',
            'username' => 'product-manager',
            'role' => UserRole::PRODUCT_MANAGER->value,
            'department_id' => $department->id,
            'position_id' => $positionId,
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
            'is_active' => true,
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'product@example.test',
            'department_id' => $department->id,
            'position_id' => $positionId,
        ]);
    }

    public function test_signature_upload_rejects_invalid_files_and_versions_private_pngs(): void
    {
        Storage::fake('local');
        $employee = $this->makeUser(UserRole::KARYAWAN);
        $this->actingAs($employee);

        $this->post(route('karyawan.profile.signature.store'), [
            'signature' => UploadedFile::fake()->createWithContent('rusak.png', 'bukan gambar PNG'),
        ])->assertSessionHasErrors('signature');

        $this->post(route('karyawan.profile.signature.store'), [
            'signature' => UploadedFile::fake()->image('foto.jpg', 200, 80)->mimeType('image/jpeg'),
        ])->assertSessionHasErrors('signature');

        $this->post(route('karyawan.profile.signature.store'), [
            'signature' => UploadedFile::fake()->image('dimensi-besar.png', 4097, 80),
        ])->assertSessionHasErrors('signature');

        $this->post(route('karyawan.profile.signature.store'), [
            'signature' => UploadedFile::fake()->image('tanda-tangan.png', 240, 80),
        ])->assertRedirect(route('karyawan.profile'));

        $first = UserSignature::query()->where('user_id', $employee->id)->firstOrFail();
        $this->assertSame(1, $first->version);
        $this->assertTrue($first->is_active);
        $this->assertStringStartsWith('signatures/'.$employee->id.'/', $first->file_path);
        Storage::disk('local')->assertExists($first->file_path);
        $otherEmployee = $this->makeUser(UserRole::KARYAWAN);
        $this->assertFalse($employee->can('viewSignature', $otherEmployee));
        $this->get('/storage/'.$first->file_path)->assertNotFound();

        $this->post(route('karyawan.profile.signature.store'), [
            'signature' => UploadedFile::fake()->image('tanda-tangan-baru.png', 260, 90),
        ])->assertRedirect(route('karyawan.profile'));

        $signatures = UserSignature::query()->where('user_id', $employee->id)->orderBy('version')->get();
        $this->assertCount(2, $signatures);
        $this->assertFalse($signatures[0]->is_active);
        $this->assertTrue($signatures[1]->is_active);
        Storage::disk('local')->assertExists($signatures[0]->file_path);
        Storage::disk('local')->assertExists($signatures[1]->file_path);

        $this->get(route('karyawan.profile.signature'))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->assertDatabaseHas('audit_logs', ['event' => 'SIGNATURE_UPLOADED']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'SIGNATURE_REPLACED']);
    }

    public function test_every_role_can_upload_its_own_signature(): void
    {
        Storage::fake('local');

        foreach (UserRole::cases() as $role) {
            $user = $this->makeUser($role);
            $routeName = $role->value.'.profile.signature.store';

            $this->actingAs($user)
                ->post(route($routeName), [
                    'signature' => UploadedFile::fake()->image($role->value.'.png', 160, 60),
                ])
                ->assertRedirect(route($role->value.'.profile'));

            $this->assertDatabaseHas('user_signatures', [
                'user_id' => $user->id,
                'version' => 1,
                'is_active' => true,
            ]);
        }
    }

    private function makeUser(UserRole $role, array $attributes = []): User
    {
        $user = User::factory()->create();
        $user->forceFill($attributes + ['role' => $role, 'is_active' => true])->save();

        return $user->fresh();
    }
}
