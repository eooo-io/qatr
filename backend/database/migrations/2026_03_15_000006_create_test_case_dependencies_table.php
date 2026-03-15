<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_case_dependencies', function (Blueprint $table) {
            $table->foreignId('test_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('depends_on_id')->constrained('test_cases')->cascadeOnDelete();
            $table->primary(['test_case_id', 'depends_on_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_case_dependencies');
    }
};
