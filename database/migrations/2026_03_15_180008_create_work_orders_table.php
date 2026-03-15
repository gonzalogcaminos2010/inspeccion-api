<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('inspection_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('scheduled_date')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
