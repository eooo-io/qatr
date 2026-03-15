<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_cases', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('steps'); // [{action: string, expected: string}]
            $table->text('expected_result')->nullable();
            $table->string('priority')->default('medium'); // critical, high, medium, low
            $table->string('type')->default('functional'); // functional, smoke, integration, edge_case
            $table->string('automation_status')->default('manual'); // manual, automated, pending
            $table->string('automation_framework')->nullable(); // cypress, selenium, pest, nightwatch
            $table->string('automation_script_path')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('test_scenario_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index(['test_scenario_id', 'sort_order']);
            $table->index('priority');
            $table->index('automation_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_cases');
    }
};
