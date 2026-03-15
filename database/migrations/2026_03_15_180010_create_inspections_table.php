<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inspection_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inspector_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('draft');
            $table->string('overall_result')->nullable();
            $table->text('observations')->nullable();
            $table->integer('score')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
