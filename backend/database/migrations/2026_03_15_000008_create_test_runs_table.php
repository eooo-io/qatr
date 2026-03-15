<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('release_id')->constrained()->cascadeOnDelete();
            $table->foreignId('executor_id')->constrained('users');
            $table->string('status')->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('environment')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['test_plan_id', 'release_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_runs');
    }
};
