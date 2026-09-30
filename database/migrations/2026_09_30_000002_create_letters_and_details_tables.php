<?php

use App\Enums\LetterStatus;
use App\Enums\LetterType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->string('letter_number', 100)->nullable()->unique();
            $table->enum('type', array_map(fn (LetterType $type) => $type->value, LetterType::cases()));
            $table->enum('status', array_map(fn (LetterStatus $status) => $status->value, LetterStatus::cases()))
                ->default(LetterStatus::DRAFT->value);
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('subject_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('title', 200);
            $table->text('reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->string('issued_document_path')->nullable();
            $table->char('issued_document_sha256', 64)->nullable();
            $table->timestamps();
            $table->index(['type', 'status']);
            $table->index(['subject_user_id', 'status']);
            $table->index(['requested_by', 'status']);
        });

        Schema::create('leave_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('leave_type', 100);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->decimal('total_days', 6, 2);
            $table->text('details')->nullable();
            $table->text('return_address')->nullable();
            $table->timestamps();
        });

        Schema::create('mutation_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('from_department_id')->nullable()->constrained('departments')->restrictOnDelete();
            $table->foreignId('to_department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('from_position_id')->nullable()->constrained('positions')->restrictOnDelete();
            $table->foreignId('to_position_id')->constrained('positions')->restrictOnDelete();
            $table->date('effective_on');
            $table->text('details')->nullable();
            $table->timestamps();
            $table->index(['from_department_id', 'to_department_id']);
            $table->index(['from_position_id', 'to_position_id']);
        });

        Schema::create('warning_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('warning_level', 30)->nullable();
            $table->date('offense_on')->nullable();
            $table->text('description');
            $table->text('legal_basis')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warning_letters');
        Schema::dropIfExists('mutation_letters');
        Schema::dropIfExists('leave_letters');
        Schema::dropIfExists('letters');
    }
};
