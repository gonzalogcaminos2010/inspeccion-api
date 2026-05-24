<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_order_items', function (Blueprint $table) {
            $table->foreignId('inspector_id')->nullable()->after('inspection_template_id')
                ->constrained('users')->nullOnDelete();
        });

        // Backfill: each existing item inherits the inspector assigned to its work order.
        // Items whose work order has no inspector stay NULL and resolve via fallback at read time.
        DB::statement('
            UPDATE work_order_items
            SET inspector_id = (
                SELECT inspector_id FROM work_orders
                WHERE work_orders.id = work_order_items.work_order_id
            )
            WHERE inspector_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('work_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('inspector_id');
        });
    }
};
