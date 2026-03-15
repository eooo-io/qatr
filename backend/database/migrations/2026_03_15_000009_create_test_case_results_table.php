<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_case_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_case_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->text('actual_result')->nullable();
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->foreignId('executed_by')->nullable()->constrained('users');
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->unique(['test_run_id', 'test_case_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_case_results');
    }
};
