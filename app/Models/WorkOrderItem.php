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
        'status',
        'notes',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
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
