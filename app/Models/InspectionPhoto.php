<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'template_question_id',
        'finding_id',
        'photo_path',
        'caption',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(TemplateQuestion::class, 'template_question_id');
    }

    public function finding(): BelongsTo
    {
        return $this->belongsTo(Finding::class);
    }
}
