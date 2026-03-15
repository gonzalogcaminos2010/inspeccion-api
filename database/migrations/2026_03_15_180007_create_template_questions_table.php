<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_section_id')->constrained()->cascadeOnDelete();
            $table->string('text');
            $table->string('type');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(true);
            $table->integer('order')->default(0);
            $table->json('fail_values')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_questions');
    }
};
