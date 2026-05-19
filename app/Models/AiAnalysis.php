<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAnalysis extends Model
{
    use HasFactory;

    protected $table = 'ai_analyses';

    protected $fillable = [
        'photo_id',
        'inspection_id',
        'requested_by_user_id',
        'model',
        'prompt_version',
        'response_json',
        'has_defect',
        'severity',
        'used_by_user',
        'latency_ms',
    ];

    protected function casts(): array
    {
        return [
            'response_json' => 'array',
            'has_defect' => 'boolean',
            'used_by_user' => 'boolean',
        ];
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(InspectionPhoto::class, 'photo_id');
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}
