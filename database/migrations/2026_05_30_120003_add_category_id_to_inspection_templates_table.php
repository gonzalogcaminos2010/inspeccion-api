<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_templates', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('vehicle_type')
                ->constrained('template_categories')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inspection_templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
