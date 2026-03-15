<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'inspection_request_id',
        'inspector_id',
        'scheduled_date',
        'status',
        'notes',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function inspectionRequest(): BelongsTo
    {
        return $this->belongsTo(InspectionRequest::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class);
    }
}
