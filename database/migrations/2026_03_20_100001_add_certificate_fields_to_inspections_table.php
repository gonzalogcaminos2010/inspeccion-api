<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->string('certificate_number')->unique()->nullable()->after('supervisor_notes');
            $table->timestamp('certificate_issued_at')->nullable()->after('certificate_number');
            $table->string('qr_token')->unique()->nullable()->after('certificate_issued_at');
        });
    }

    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropColumn(['certificate_number', 'certificate_issued_at', 'qr_token']);
        });
    }
};
