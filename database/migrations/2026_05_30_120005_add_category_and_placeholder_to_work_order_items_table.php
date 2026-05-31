<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_order_items', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('equipment_id')
                ->constrained('template_categories')
                ->restrictOnDelete();
            $table->boolean('is_equipment_placeholder')
                ->default(false)
                ->after('category_id');
        });

        // Relax inspection_template_id to NULL-able (default-template fallback at inspection start)
        // Have to drop FK first to alter nullability, then re-add.
        Schema::table('work_order_items', function (Blueprint $table) {
            $table->dropForeign(['inspection_template_id']);
        });

        Schema::table('work_order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('inspection_template_id')->nullable()->change();
            $table->foreign('inspection_template_id')
                ->references('id')
                ->on('inspection_templates')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('work_order_items', function (Blueprint $table) {
            $table->dropForeign(['inspection_template_id']);
        });

        Schema::table('work_order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('inspection_template_id')->nullable(false)->change();
            $table->foreign('inspection_template_id')
                ->references('id')
                ->on('inspection_templates')
                ->cascadeOnDelete();

            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn('is_equipment_placeholder');
        });
    }
};
