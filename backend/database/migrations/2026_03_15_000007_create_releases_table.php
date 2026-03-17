<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('releases', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('release_date')->nullable();
            $table->string('status')->default('planning');
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['project_id', 'version']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('releases');
    }
};
