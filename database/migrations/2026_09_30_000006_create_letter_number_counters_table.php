<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_number_counters', function (Blueprint $table) {
            $table->id();
            $table->string('letter_type', 20);
            $table->unsignedSmallInteger('year');
            $table->unsignedBigInteger('next_number')->default(1);
            $table->timestamps();
            $table->unique(['letter_type', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_number_counters');
    }
};
