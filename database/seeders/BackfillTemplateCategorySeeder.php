<?php

namespace Database\Seeders;

use App\Models\InspectionTemplate;
use App\Models\TemplateCategory;
use Illuminate\Database\Seeder;

/**
 * Backfill inspection_templates.category_id from the legacy vehicle_type string.
 * Idempotent: skips templates that already have category_id set.
 */
class BackfillTemplateCategorySeeder extends Seeder
{
    public function run(): void
    {
        $fallback = TemplateCategory::firstOrCreate(
            ['code' => 'sin_clasificar'],
            ['name' => 'Sin Clasificar', 'is_active' => true]
        );

        InspectionTemplate::query()
            ->whereNull('category_id')
            ->get()
            ->each(function (InspectionTemplate $tpl) use ($fallback): void {
                $code = $tpl->vehicle_type;
                $category = $code
                    ? TemplateCategory::where('code', $code)->first()
                    : null;

                $tpl->category_id = ($category ?? $fallback)->id;
                $tpl->saveQuietly();
            });
    }
}
