<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_question_id')->constrained()->cascadeOnDelete();
            $table->text('answer_text')->nullable();
            $table->boolean('answer_boolean')->nullable();
            $table->decimal('answer_number', 10, 2)->nullable();
            $table->json('answer_json')->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['inspection_id', 'template_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_answers');
    }
};
