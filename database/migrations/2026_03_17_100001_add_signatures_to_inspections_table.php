<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->string('inspector_signature')->nullable();
            $table->timestamp('inspector_signed_at')->nullable();
            $table->string('supervisor_signature')->nullable();
            $table->timestamp('supervisor_signed_at')->nullable();
            $table->string('client_signature')->nullable();
            $table->timestamp('client_signed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropColumn([
                'inspector_signature',
                'inspector_signed_at',
                'supervisor_signature',
                'supervisor_signed_at',
                'client_signature',
                'client_signed_at',
            ]);
        });
    }
};
