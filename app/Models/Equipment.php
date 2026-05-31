<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'type',
        'category_id',
        'brand',
        'model',
        'year',
        'plate',
        'serial_number',
        'internal_code',
        'metadata',
        'status',
        'next_inspection_due_at',
        'last_inspection_completed_at',
        'last_inspection_id',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'next_inspection_due_at' => 'date',
            'last_inspection_completed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TemplateCategory::class, 'category_id');
    }

    public function lastInspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'last_inspection_id');
    }

    public function workOrderItems(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }
}
