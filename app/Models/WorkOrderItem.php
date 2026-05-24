<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'equipment_id',
        'inspection_template_id',
        'inspector_id',
        'status',
        'notes',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    /**
     * Effective inspector for this item: the item-level assignment if present,
     * otherwise the work order's lead inspector.
     */
    public function getEffectiveInspectorIdAttribute(): ?int
    {
        return $this->inspector_id ?? $this->workOrder?->inspector_id;
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(InspectionTemplate::class, 'inspection_template_id');
    }

    public function inspection(): HasOne
    {
        return $this->hasOne(Inspection::class);
    }
}
