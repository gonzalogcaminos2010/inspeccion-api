<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class InspectionTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'vehicle_type',
        'is_active',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(TemplateSection::class);
    }

    public function questions(): HasManyThrough
    {
        return $this->hasManyThrough(TemplateQuestion::class, TemplateSection::class);
    }
}
