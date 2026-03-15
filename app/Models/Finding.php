<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Finding extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'template_question_id',
        'severity',
        'description',
        'recommendation',
        'is_resolved',
        'resolved_at',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'is_resolved' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(TemplateQuestion::class, 'template_question_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(InspectionPhoto::class);
    }
}
