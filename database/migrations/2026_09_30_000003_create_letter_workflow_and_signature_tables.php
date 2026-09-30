<?php

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sequence');
            $table->enum('approver_role', array_map(fn (UserRole $role) => $role->value, UserRole::cases()));
            $table->foreignId('approver_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->enum('status', array_map(fn (ApprovalStatus $status) => $status->value, ApprovalStatus::cases()))
                ->default(ApprovalStatus::PENDING->value);
            $table->text('comments')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->unique(['letter_id', 'sequence']);
            $table->index(['approver_role', 'status']);
            $table->index(['approver_user_id', 'status']);
        });

        Schema::create('letter_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 40);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['letter_id', 'created_at']);
            $table->index('event');
        });

        Schema::create('letter_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('disk', 40)->default('local');
            $table->string('file_path');
            $table->string('original_name', 255);
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('file_size');
            $table->char('sha256', 64);
            $table->timestamps();
            $table->index(['letter_id', 'created_at']);
        });

        Schema::create('user_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('version');
            $table->string('file_path')->unique();
            $table->string('mime_type', 80)->default('image/png');
            $table->unsignedBigInteger('file_size');
            $table->char('sha256', 64);
            $table->boolean('is_active')->default(true);
            $table->timestamp('uploaded_at');
            $table->timestamps();
            $table->unique(['user_id', 'version']);
            $table->index(['user_id', 'is_active']);
        });

        Schema::create('letter_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_signature_id')->constrained('user_signatures')->restrictOnDelete();
            $table->foreignId('signer_user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedSmallInteger('signing_order');
            $table->string('signer_name_snapshot', 160);
            $table->string('signer_position_snapshot', 160)->nullable();
            $table->string('signature_path_snapshot');
            $table->char('signature_sha256_snapshot', 64);
            $table->timestamp('signed_at');
            $table->unique(['letter_id', 'signing_order']);
            $table->index('signer_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_signatures');
        Schema::dropIfExists('user_signatures');
        Schema::dropIfExists('letter_attachments');
        Schema::dropIfExists('letter_histories');
        Schema::dropIfExists('letter_approvals');
    }
};
