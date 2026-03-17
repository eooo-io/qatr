<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('defects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_case_result_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('severity')->default('medium');
            $table->string('status')->default('open');
            $table->string('external_tracker_url')->nullable();
            $table->foreignId('reported_by')->constrained('users');
            $table->timestamps();

            $table->index('severity');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('defects');
    }
};
