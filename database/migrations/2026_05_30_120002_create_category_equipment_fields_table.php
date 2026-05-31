<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_equipment_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_category_id')
                ->constrained('template_categories')
                ->cascadeOnDelete();
            $table->string('key_name', 64);
            $table->string('label');
            $table->enum('type', ['text', 'number', 'date', 'select', 'boolean']);
            $table->json('options')->nullable();
            $table->string('unit', 32)->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_mutable')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['template_category_id', 'key_name'], 'uq_cat_field_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_equipment_fields');
    }
};
