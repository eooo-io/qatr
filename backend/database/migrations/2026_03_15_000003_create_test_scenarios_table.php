<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_scenarios', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('preconditions')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('test_plan_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index(['test_plan_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_scenarios');
    }
};
