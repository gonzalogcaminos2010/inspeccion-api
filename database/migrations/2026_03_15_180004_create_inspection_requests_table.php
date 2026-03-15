<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_type_id')->constrained()->cascadeOnDelete();
            $table->date('requested_date');
            $table->date('scheduled_date')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_requests');
    }
};
