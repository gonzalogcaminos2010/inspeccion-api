<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->after('inspector_id')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('completed_at');
            $table->text('supervisor_notes')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['approved_at', 'supervisor_notes']);
        });
    }
};
