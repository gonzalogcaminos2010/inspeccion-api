<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('template_categories', function (Blueprint $table) {
            $table->foreignId('default_template_id')
                ->nullable()
                ->after('name')
                ->constrained('inspection_templates')
                ->nullOnDelete();
            $table->unsignedSmallInteger('default_inspection_interval_months')
                ->nullable()
                ->after('default_template_id');
        });
    }

    public function down(): void
    {
        Schema::table('template_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('default_template_id');
            $table->dropColumn('default_inspection_interval_months');
        });
    }
};
