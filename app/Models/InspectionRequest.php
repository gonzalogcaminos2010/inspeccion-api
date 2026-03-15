<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'client_id',
        'service_type_id',
        'requested_date',
        'scheduled_date',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'requested_date' => 'date',
            'scheduled_date' => 'date',
        ];
    }

    public function getNumberAttribute(): string
    {
        return $this->request_number;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }
}
