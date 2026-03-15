<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_template_id',
        'name',
        'order',
        'description',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(InspectionTemplate::class, 'inspection_template_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(TemplateQuestion::class);
    }
}
