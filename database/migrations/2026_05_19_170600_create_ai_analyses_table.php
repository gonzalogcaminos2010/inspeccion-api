<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photo_id')->constrained('inspection_photos')->cascadeOnDelete();
            $table->foreignId('inspection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('model');
            $table->string('prompt_version')->default('v1');
            $table->json('response_json')->nullable();
            $table->boolean('has_defect')->default(false);
            $table->string('severity')->nullable();
            $table->boolean('used_by_user')->default(false);
            $table->integer('latency_ms')->nullable();
            $table->timestamps();
            $table->index(['inspection_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_analyses');
    }
};
