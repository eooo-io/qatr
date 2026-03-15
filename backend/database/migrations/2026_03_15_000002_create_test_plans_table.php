<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('feature'); // smoke, integration, feature, happy_path, edge_case
            $table->string('status')->default('draft'); // draft, active, archived
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('type');
            $table->index('status');
        });

        // Many-to-many: test plans can be used across multiple projects
        Schema::create('project_test_plan', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_plan_id')->constrained()->cascadeOnDelete();
            $table->primary(['project_id', 'test_plan_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_test_plan');
        Schema::dropIfExists('test_plans');
    }
};
