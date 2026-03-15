<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('year', 4)->nullable();
            $table->string('plate')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('internal_code')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
