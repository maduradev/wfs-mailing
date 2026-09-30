<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name', 120)->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->string('code', 40);
            $table->string('name', 120);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['department_id', 'code']);
            $table->unique(['department_id', 'name']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 80)->nullable()->unique();
            $table->string('nik', 40)->nullable()->unique();
            $table->string('phone', 30)->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->restrictOnDelete();
            $table->enum('role', array_map(fn (UserRole $role) => $role->value, UserRole::cases()))
                ->default(UserRole::KARYAWAN->value)
                ->index();
            $table->boolean('is_active')->default(true)->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('position_id');
            $table->dropUnique(['username']);
            $table->dropUnique(['nik']);
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['username', 'nik', 'phone', 'role', 'is_active']);
        });

        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
    }
};
