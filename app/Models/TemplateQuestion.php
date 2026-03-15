<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_section_id',
        'text',
        'type',
        'options',
        'is_required',
        'order',
        'fail_values',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'fail_values' => 'array',
            'is_required' => 'boolean',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(TemplateSection::class, 'template_section_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(InspectionAnswer::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(InspectionPhoto::class);
    }
}
