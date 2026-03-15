<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_item_id',
        'inspection_template_id',
        'equipment_id',
        'inspector_id',
        'status',
        'overall_result',
        'observations',
        'score',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function workOrderItem(): BelongsTo
    {
        return $this->belongsTo(WorkOrderItem::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(InspectionTemplate::class, 'inspection_template_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(InspectionAnswer::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(InspectionPhoto::class);
    }

    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class);
    }
}
