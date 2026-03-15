<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'template_question_id',
        'answer_text',
        'answer_boolean',
        'answer_number',
        'answer_json',
        'is_flagged',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'answer_boolean' => 'boolean',
            'answer_number' => 'decimal:2',
            'answer_json' => 'array',
            'is_flagged' => 'boolean',
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
}
