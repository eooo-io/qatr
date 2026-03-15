<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color')->default('#6d5acd');
            $table->timestamps();
        });

        Schema::create('test_case_tag', function (Blueprint $table) {
            $table->foreignId('test_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['test_case_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_case_tag');
        Schema::dropIfExists('tags');
    }
};
