<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\TemplateCategory;
use Illuminate\Database\Seeder;

/**
 * Backfill equipment.category_id from the legacy `type` string.
 * Idempotent: skips equipment that already has category_id set.
 */
class BackfillEquipmentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $fallback = TemplateCategory::firstOrCreate(
            ['code' => 'sin_clasificar'],
            ['name' => 'Sin Clasificar', 'is_active' => true]
        );

        Equipment::query()
            ->whereNull('category_id')
            ->get()
            ->each(function (Equipment $eq) use ($fallback): void {
                $code = $eq->type;
                $category = $code
                    ? TemplateCategory::where('code', $code)->first()
                    : null;

                $eq->category_id = ($category ?? $fallback)->id;
                $eq->saveQuietly();
            });
    }
}
