<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('type')
                ->constrained('template_categories')
                ->restrictOnDelete();
            $table->date('next_inspection_due_at')->nullable();
            $table->timestamp('last_inspection_completed_at')->nullable();
            $table->foreignId('last_inspection_id')
                ->nullable()
                ->constrained('inspections')
                ->nullOnDelete();

            $table->index('next_inspection_due_at', 'idx_eq_next_due');
            $table->index('last_inspection_completed_at', 'idx_eq_last_completed');
            $table->index(['client_id', 'plate'], 'idx_eq_client_plate');
            $table->index(['client_id', 'serial_number'], 'idx_eq_client_serial');
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropIndex('idx_eq_next_due');
            $table->dropIndex('idx_eq_last_completed');
            $table->dropIndex('idx_eq_client_plate');
            $table->dropIndex('idx_eq_client_serial');
            $table->dropConstrainedForeignId('last_inspection_id');
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn(['next_inspection_due_at', 'last_inspection_completed_at']);
        });
    }
};
