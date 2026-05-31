<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'is_active',
        'default_template_id',
        'default_inspection_interval_months',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'default_inspection_interval_months' => 'integer',
        ];
    }

    public function defaultTemplate(): BelongsTo
    {
        return $this->belongsTo(InspectionTemplate::class, 'default_template_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(InspectionTemplate::class, 'category_id');
    }

    public function equipmentFields(): HasMany
    {
        return $this->hasMany(CategoryEquipmentField::class, 'template_category_id')
            ->orderBy('sort_order');
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'category_id');
    }

    /**
     * Legacy count via the deprecated vehicle_type string. Prefer templates()->count().
     */
    public function templatesCount(): int
    {
        return $this->templates()->count();
    }
}
